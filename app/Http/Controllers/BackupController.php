<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Copia todos os arquivos do storage local para o HD externo (disk external).
     * @return \Illuminate\Http\JsonResponse
     */
    public function backupAll()
    {
        Log::info('BackupController@backupAll INICIADO');
        try {
            $localFiles = Storage::disk('local')->allFiles();
            Log::info('Arquivos encontrados no storage local: ' . count($localFiles));
            $copied = [];
            $skipped = [];
            foreach ($localFiles as $file) {
                try {
                    $content = Storage::disk('local')->get($file);
                    $destPath = $file;
                    $shouldCopy = true;
                    if (Storage::disk('external')->exists($destPath)) {
                        $externalContent = Storage::disk('external')->get($destPath);
                        if (md5($content) === md5($externalContent)) {
                            $shouldCopy = false;
                            $skipped[] = $destPath;
                            Log::info('Arquivo já existe e é idêntico, ignorando: ' . $destPath);
                        }
                    }
                    if ($shouldCopy) {
                        Log::info('Copiando arquivo: ' . $file);
                        Storage::disk('external')->put($destPath, $content);
                        $copied[] = $destPath;
                        Log::info('Arquivo copiado com sucesso: ' . $destPath);
                    }
                } catch (\Exception $fe) {
                    Log::error('Falha ao copiar arquivo: ' . $file . ' - ' . $fe->getMessage() . ' | Linha: ' . $fe->getLine() . ' | Arquivo: ' . $fe->getFile());
                }
            }
            Log::info('Backup FINALIZADO. Total copiados: ' . count($copied) . ' | Ignorados (sem alteração): ' . count($skipped));
            return response()->json([
                'status' => 'ok',
                'copiados' => $copied,
                'ignorados' => $skipped,
                'total' => count($copied),
                'total_ignorados' => count($skipped),
            ]);
        } catch (\Exception $e) {
            Log::error('Backup error: ' . $e->getMessage() . ' | Linha: ' . $e->getLine() . ' | Arquivo: ' . $e->getFile());
            return response()->json([
                'status' => 'erro',
                'mensagem' => $e->getMessage() . ' (Linha: ' . $e->getLine() . ')',
            ], 500);
        }
    }

    /**
     * Copia todos os arquivos do storage local para o servidor FTP (disk ftp).
     * @return \Illuminate\Http\JsonResponse
     */
    public function backupAllToFtp()
    {
        Log::info('BackupController@backupAllToFtp INICIADO (dispatch background)');
        try {
            // montar comando para rodar em background. Usamos PHP_BINARY para garantir o PHP correto.
            $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
            $artisan = base_path('artisan');
            // usar nohup/ampersand para garantir detach
            $cmd = "cd " . base_path() . " && nohup " . escapeshellcmd($php) . " " . escapeshellarg($artisan) . " backup:ftp --raw-ftp --delay-ms=200 > /dev/null 2>&1 & echo $!";
            $output = [];
            @exec($cmd, $output);
            $pid = $output[0] ?? null;
            Log::info('Backup enfileirado em background. PID: ' . ($pid ?: 'n/a'));

            return response()->json([
                'status' => 'ok',
                'mensagem' => 'Backup enfileirado e iniciado em background' . ($pid ? ' (PID: ' . $pid . ')' : ''),
                'total' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Backup FTP dispatch error: ' . $e->getMessage() . ' | Linha: ' . $e->getLine() . ' | Arquivo: ' . $e->getFile());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Falha ao iniciar backup em background: ' . $e->getMessage(),
            ], 500);
        }
    }
}
