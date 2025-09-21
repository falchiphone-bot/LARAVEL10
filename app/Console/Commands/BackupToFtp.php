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
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Iniciando backup para FTP' . ($dryRun ? ' (dry-run)' : ''));

        try {
            $files = Storage::disk('local')->allFiles();
            $total = count($files);
            $this->info("Total encontrados no storage local: $total");

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

                $host = env('FTP_HOST');
                $user = env('FTP_USERNAME');
                $pass = env('FTP_PASSWORD');
                $port = (int) env('FTP_PORT', 21);
                $passive = filter_var(env('FTP_PASSIVE', true), FILTER_VALIDATE_BOOLEAN);

                $conn = ftp_connect($host, $port, (int) env('FTP_TIMEOUT', 30));
                if (!$conn) {
                    $this->error('Não foi possível conectar ao servidor FTP: ' . $host . ':' . $port);
                    return 1;
                }
                $login = @ftp_login($conn, $user, $pass);
                if (!$login) {
                    $this->error('Falha no login FTP com usuário ' . $user);
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
                                if (!isset($ftpDirsCache[$path])) {
                                    if (!@ftp_chdir($conn, $path)) {
                                        @ftp_mkdir($conn, $path);
                                    }
                                    // Marcar como já processado (exista ou não)
                                    $ftpDirsCache[$path] = true;
                                }
                            }
                        }

                        if ($dryRun) {
                            $this->comment("[dry-run] Enviaria: $file");
                            $copied++;
                            if ($delayMs > 0) usleep($delayMs * 1000);
                            continue;
                        }

                        // gravar arquivo temporariamente
                        $tmp = tempnam(sys_get_temp_dir(), 'bkftp');
                        file_put_contents($tmp, $content);

                        // verificar se arquivo remoto existe e é idêntico (size -> md5)
                        $remoteSize = @ftp_size($conn, $file);
                        $localSize = filesize($tmp);
                        $identical = false;
                        $canCompare = false;
                        if ($remoteSize !== -1 && $remoteSize == $localSize) {
                            // tentar baixar remotamente para comparar MD5
                            $tmpRemote = tempnam(sys_get_temp_dir(), 'bkftp_r');
                            $ftpGetOk = @ftp_get($conn, $tmpRemote, $file, FTP_BINARY);
                            if ($debugFile) {
                                $this->info("[debug] ftp_size($file) = $remoteSize");
                                $this->info('[debug] ftp_get returned: ' . ($ftpGetOk ? 'true' : 'false'));
                                $this->info('[debug] last_error: ' . json_encode(error_get_last()));
                            }
                            if ($ftpGetOk) {
                                $canCompare = true;
                                if (md5_file($tmpRemote) === md5_file($tmp)) {
                                    $identical = true;
                                }
                            } else {
                                $this->warn("Não foi possível baixar arquivo remoto para comparar: $file");
                                Log::warning('Não foi possível baixar arquivo remoto para comparar: ' . $file);
                            }
                            @unlink($tmpRemote);
                        }

                        if ($identical) {
                            $this->comment("Ignorado (idêntico): $file");
                            Log::info('Ignorado (idêntico): ' . $file);
                            $skipped++;
                            @unlink($tmp);
                            if ($delayMs > 0) usleep($delayMs * 1000);
                            continue;
                        }

                        // Se não foi possível comparar, logar
                        if ($remoteSize !== -1 && !$canCompare) {
                            $this->warn("Não foi possível comparar conteúdo de $file, tentando enviar mesmo assim.");
                            Log::warning('Não foi possível comparar conteúdo de ' . $file . ', tentando enviar mesmo assim.');
                        }

                        // enviar via ftp_put
                        if ($debugFile) {
                            // emit some raw ftp diagnostics
                            $pwd = @ftp_pwd($conn);
                            $syst = @ftp_systype($conn);
                            $nlist = @ftp_nlist($conn, dirname($file) === '.' ? '/' : dirname($file));
                            $this->info('[debug] ftp_pwd: ' . ($pwd === false ? 'false' : $pwd));
                            $this->info('[debug] ftp_systype: ' . ($syst === false ? 'false' : $syst));
                            $this->info('[debug] ftp_nlist: ' . json_encode($nlist));
                            $this->info('[debug] ftp_size before put: ' . @ftp_size($conn, $file));
                        }

                        $sent = @ftp_put($conn, $file, $tmp, FTP_BINARY);
                        if (!$sent) {
                            $err = error_get_last();
                            $errMsg = $err ? (isset($err['message']) ? $err['message'] : json_encode($err)) : 'erro desconhecido';
                            $ftpRaw = function_exists('ftp_raw') ? @ftp_raw($conn, 'NOOP') : null;
                            $ftpRawMsg = $ftpRaw ? implode(' | ', $ftpRaw) : 'N/A';
                            $this->error('[debug] ftp_put falhou para ' . $file . '. error_get_last: ' . $errMsg . ' | ftp_raw: ' . $ftpRawMsg);
                            Log::error('Falha raw ftp: ' . $file . ' | Motivo: ' . $errMsg . ' | ftp_raw: ' . $ftpRawMsg);
                        }
                        @unlink($tmp);
                        if ($sent) {
                            $this->info("Enviado (raw): $file");
                            $copied++;
                        }
                        if ($delayMs > 0) usleep($delayMs * 1000);
                    } catch (\Exception $e) {
                        $this->error("Erro ao processar $file: " . $e->getMessage());
                        Log::error('BackupToFtp error (raw): ' . $e->getMessage());
                    }
                }

                ftp_close($conn);
                $totalAnalisado = count($files);
                $this->info("Finalizado (raw). Copiados: $copied | Ignorados: $skipped | Total analisado: $totalAnalisado");
                Log::info("BackupToFtpJob: Finalizado (raw). Copiados: $copied | Ignorados: $skipped | Total analisado: $totalAnalisado");
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
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro no backup: ' . $e->getMessage());
            Log::error('BackupToFtp fatal: ' . $e->getMessage());
            return 1;
        }
    }
}
