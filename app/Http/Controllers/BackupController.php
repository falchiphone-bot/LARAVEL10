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
}
