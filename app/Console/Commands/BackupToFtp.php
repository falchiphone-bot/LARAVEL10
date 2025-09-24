<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupToFtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:ftp {--limit= : Limita o número de arquivos a processar} {--dry-run : Não envia arquivos, apenas simula} {--delay-ms=100 : Delay em milissegundos entre arquivos} {--raw-ftp : Use conexão FTP nativa do PHP (um login, múltiplos uploads)} {--debug-file= : Depurar um arquivo específico com logs FTP brutos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup do storage local para disk ftp com feedback no console';

    public function handle()
    {
        // helper para registrar logs em JSONL
        $jsonLog = function (array $data) {
            try {
                $record = array_merge([
                    'ts' => now()->toIso8601String(),
                    'type' => 'backup:ftp',
                ], $data);
                @file_put_contents(
                    storage_path('logs/backup_ftp.jsonl'),
                    json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL,
                    FILE_APPEND | LOCK_EX
                );
            } catch (\Throwable $e) {
                // não interromper o fluxo por falha no log JSON
            }
        };

        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Iniciando backup para FTP' . ($dryRun ? ' (dry-run)' : ''));

        try {
            $files = Storage::disk('local')->allFiles();
            $total = count($files);
            $this->info("Total encontrados no storage local: $total");
            $jsonLog(['event' => 'start', 'total' => $total, 'dry_run' => $dryRun, 'raw_ftp' => (bool) $this->option('raw-ftp')]);

            if ($limit) {
                $files = array_slice($files, 0, $limit);
                $this->info("Limitado a: $limit arquivos para teste");
            }

            $copied = 0;
            $skipped = 0;

            $delayMs = (int) $this->option('delay-ms');
            $useRawFtp = (bool) $this->option('raw-ftp');

            if ($useRawFtp) {
                $this->info('Usando conexão FTP nativa (raw-ftp)');
                if (!function_exists('ftp_connect')) {
                    $this->error('Funções FTP nativas não estão disponíveis no PHP deste container. Instale a extensão ftp.');
                    return 1;
                }

                // Preferir config em tempo de execução (respeita config:cache)
                $cfg = config('filesystems.disks.ftp', []);
                $host = $cfg['host'] ?? null;
                $user = $cfg['username'] ?? null;
                $pass = $cfg['password'] ?? null;
                $port = isset($cfg['port']) ? (int) $cfg['port'] : 21;
                $passive = isset($cfg['passive']) ? (bool) $cfg['passive'] : true;
                $ssl = isset($cfg['ssl']) ? (bool) $cfg['ssl'] : false;
                $timeout = isset($cfg['timeout']) ? (int) $cfg['timeout'] : 30;

                if (empty($host)) {
                    $this->error('FTP host não configurado. Defina FTP_HOST no .env (e rode php artisan config:cache) ou configure filesystems.disks.ftp.host.');
                    $this->line('Dicas: variáveis esperadas: FTP_HOST, FTP_USERNAME, FTP_PASSWORD, FTP_PORT, FTP_ROOT, FTP_PASSIVE, FTP_SSL, FTP_TIMEOUT');
                    return 1;
                }

                // Em dry-run não precisamos abrir conexão; apenas simular
                if ($dryRun) {
                    $this->comment("[dry-run] Conectaria em {$host}:{$port} (passive=" . ($passive?'on':'off') . ", ssl=" . ($ssl?'on':'off') . ")");
                    foreach ($files as $file) {
                        $this->comment("[dry-run] Enviaria (raw): /" . ltrim($file, '/'));
                        $copied++;
                        if ($delayMs > 0) usleep($delayMs * 1000);
                    }
                    $this->info("Finalizado (dry-run raw). Copiados simulados: $copied | Ignorados: $skipped | Total analisado: " . count($files));
                    return 0;
                }

                $this->line("Conectando em {$host}:{$port} (passive=" . ($passive?'on':'off') . ", ssl=" . ($ssl?'on':'off') . ") com timeout {$timeout}s");
                $conn = @ftp_connect($host, $port, $timeout);
                if (!$conn) {
                    $err = error_get_last();
                    $this->error('Não foi possível conectar ao servidor FTP: ' . $host . ':' . $port);
                    if ($err && isset($err['message'])) {
                        $this->error('Detalhe: ' . $err['message']);
                    }
                    return 1;
                }
                $login = @ftp_login($conn, $user ?? '', $pass ?? '');
                if (!$login) {
                    $maskUser = $user ? (substr($user, 0, 2) . '***') : '(vazio)';
                    $this->error('Falha no login FTP com usuário ' . $maskUser);
                    ftp_close($conn);
                    return 1;
                }
                ftp_pasv($conn, $passive);

                $debugFile = $this->option('debug-file');
                if ($debugFile) {
                    // normalize
                    $files = array_values(array_filter($files, function ($f) use ($debugFile) {
                        return $f === ltrim($debugFile, '/');
                    }));
                    if (empty($files)) {
                        $this->error("Arquivo para depuração não encontrado no storage local: $debugFile");
                        ftp_close($conn);
                        return 1;
                    }
                }

                foreach ($files as $file) {
                    $this->line("Processando: $file");
                    try {
                        $content = Storage::disk('local')->get($file);
                        // criar diretórios recursivamente no FTP se necessário, mas só uma vez por diretório
                        static $ftpDirsCache = [];
                        $dirname = dirname($file);
                        if ($dirname !== '.' && $dirname !== '') {
                            $parts = explode('/', $dirname);
                            $path = '';
                            foreach ($parts as $part) {
                                $path = ltrim($path . '/' . $part, '/');
                                $ftpPath = '/' . ltrim($path, '/');
                                if (!isset($ftpDirsCache[$ftpPath])) {
                                    $chdirOk = @ftp_chdir($conn, $ftpPath);
                                    if (!$chdirOk) {
                                        $mkdirOk = @ftp_mkdir($conn, $ftpPath);
                                        Log::info('[FTP] mkdir ' . $ftpPath . ' => ' . ($mkdirOk ? 'OK' : 'FALHOU'));
                                        $chdirOk2 = @ftp_chdir($conn, $ftpPath);
                                        Log::info('[FTP] chdir após mkdir ' . $ftpPath . ' => ' . ($chdirOk2 ? 'OK' : 'FALHOU'));
                                    } else {
                                        Log::info('[FTP] chdir ' . $ftpPath . ' => OK');
                                    }
                                    // Marcar como já processado (exista ou não)
                                    $ftpDirsCache[$ftpPath] = true;
                                }
                            }
                        }

                        if ($dryRun) {
                            $this->comment("[dry-run] Enviaria: $file");
                            $copied++;
                            $jsonLog(['event' => 'dry-run', 'file' => $file]);
                            if ($delayMs > 0) usleep($delayMs * 1000);
                            continue;
                        }

                        // gravar arquivo temporariamente
                        $tmp = tempnam(sys_get_temp_dir(), 'bkftp');
                        file_put_contents($tmp, $content);

                        // Padroniza caminho remoto absoluto
                        $remoteFile = '/' . ltrim($file, '/');


                        // Verifica apenas se o arquivo remoto já existe (ftp_size != -1)
                        $remoteSize = @ftp_size($conn, $remoteFile);
                        if ($remoteSize !== -1) {
                            $this->comment("Ignorado (já existe): $remoteFile");
                            Log::info('Ignorado (já existe): ' . $remoteFile);
                            $skipped++;
                            $jsonLog(['event' => 'skipped', 'file' => $file, 'remote' => $remoteFile, 'bytes' => @strlen($content), 'reason' => 'already_exists', 'remote_size' => $remoteSize]);
                            @unlink($tmp);
                            if ($delayMs > 0) usleep($delayMs * 1000);
                            continue;
                        }

                        // enviar via ftp_put
                        if ($debugFile) {
                            // emit some raw ftp diagnostics
                            $pwd = @ftp_pwd($conn);
                            $syst = @ftp_systype($conn);
                            $nlist = @ftp_nlist($conn, dirname($remoteFile) === '.' ? '/' : dirname($remoteFile));
                            $this->info('[debug] ftp_pwd: ' . ($pwd === false ? 'false' : $pwd));
                            $this->info('[debug] ftp_systype: ' . ($syst === false ? 'false' : $syst));
                            $this->info('[debug] ftp_nlist: ' . json_encode($nlist));
                            $this->info('[debug] ftp_size before put: ' . @ftp_size($conn, $remoteFile));
                        }

                        $sent = @ftp_put($conn, $remoteFile, $tmp, FTP_BINARY);
                        if (!$sent) {
                            $err = error_get_last();
                            $errMsg = $err ? (isset($err['message']) ? $err['message'] : json_encode($err)) : 'erro desconhecido';
                            $ftpRaw = function_exists('ftp_raw') ? @ftp_raw($conn, 'NOOP') : null;
                            $ftpRawMsg = $ftpRaw ? implode(' | ', $ftpRaw) : 'N/A';
                            $this->error('[debug] ftp_put falhou para ' . $remoteFile . '. error_get_last: ' . $errMsg . ' | ftp_raw: ' . $ftpRawMsg);
                            Log::error('Falha raw ftp: ' . $remoteFile . ' | Motivo: ' . $errMsg . ' | ftp_raw: ' . $ftpRawMsg);
                            $jsonLog(['event' => 'error', 'file' => $file, 'remote' => $remoteFile, 'message' => $errMsg, 'context' => 'ftp_put']);
                        }
                        @unlink($tmp);
                        if ($sent) {
                            $this->info("Enviado (raw): $remoteFile");
                            $copied++;
                            $jsonLog(['event' => 'sent', 'file' => $file, 'remote' => $remoteFile, 'bytes' => @strlen($content)]);
                        }
                        if ($delayMs > 0) usleep($delayMs * 1000);
                    } catch (\Exception $e) {
                        $this->error("Erro ao processar $file: " . $e->getMessage());
                        Log::error('BackupToFtp error (raw): ' . $e->getMessage());
                        $jsonLog(['event' => 'error', 'file' => $file, 'message' => $e->getMessage(), 'context' => 'process_file']);
                    }
                }

                ftp_close($conn);
                $totalAnalisado = count($files);
                $this->info("Finalizado (raw). Copiados: $copied | Ignorados: $skipped | Total analisado: $totalAnalisado");
                Log::info("BackupToFtpJob: Finalizado (raw). Copiados: $copied | Ignorados: $skipped | Total analisado: $totalAnalisado");
                $jsonLog(['event' => 'end', 'copied' => $copied, 'skipped' => $skipped, 'total' => $totalAnalisado, 'mode' => 'raw']);
                return 0;
            }
            foreach ($files as $file) {
                $this->line("Processando: $file");
                try {
                    $content = Storage::disk('local')->get($file);
                    if (Storage::disk('ftp')->exists($file)) {
                        $ftpContent = Storage::disk('ftp')->get($file);
                        if (md5($content) === md5($ftpContent)) {
                            $this->comment("Ignorado (idêntico): $file");
                            $skipped++;
                            // small delay to avoid hammering the FTP server
                            if ($delayMs > 0) usleep($delayMs * 1000);
                            continue;
                        }
                    }

                    if ($dryRun) {
                        $this->comment("[dry-run] Enviaria: $file");
                        $copied++;
                        if ($delayMs > 0) usleep($delayMs * 1000);
                        continue;
                    }

                    // retry logic for transient FTP auth errors
                    $tries = 0;
                    $maxTries = 3;
                    $sent = false;
                    while (!$sent && $tries < $maxTries) {
                        try {
                            $ok = Storage::disk('ftp')->put($file, $content);
                            if ($ok) {
                                $this->info("Enviado: $file");
                                $copied++;
                                $sent = true;
                                break;
                            } else {
                                $this->error("Falha ao enviar: $file");
                                Log::error('Falha ao enviar via comando backup:ftp - ' . $file);
                            }
                        } catch (\Exception $e) {
                            $tries++;
                            $this->error("Tentativa $tries falhou para $file: " . $e->getMessage());
                            Log::error('BackupToFtp error (try '.$tries.'): ' . $e->getMessage());
                            // se for erro de autenticação, aguarda um pouco antes de tentar novamente
                            if (stripos($e->getMessage(), 'Unable to login') !== false || stripos($e->getMessage(), 'authenticate') !== false) {
                                sleep(1);
                            } else {
                                // pequenos backoffs genéricos
                                usleep(200 * 1000);
                            }
                        }
                    }
                    if (!$sent) {
                        $this->error("Não foi possível enviar $file após $maxTries tentativas.");
                    }
                    if ($delayMs > 0) usleep($delayMs * 1000);
                } catch (\Exception $e) {
                    $this->error("Erro ao processar $file: " . $e->getMessage());
                    Log::error('BackupToFtp error: ' . $e->getMessage());
                }
            }

            $this->info("Finalizado. Copiados: $copied | Ignorados: $skipped");
            $jsonLog(['event' => 'end', 'copied' => $copied, 'skipped' => $skipped, 'total' => $copied + $skipped]);
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro no backup: ' . $e->getMessage());
            Log::error('BackupToFtp fatal: ' . $e->getMessage());
            $jsonLog(['event' => 'fatal', 'message' => $e->getMessage()]);
            return 1;
        }
    }
}
