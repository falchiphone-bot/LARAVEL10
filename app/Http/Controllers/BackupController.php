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
        try {
            $localFiles = Storage::disk('local')->allFiles();
            $copied = [];
            foreach ($localFiles as $file) {
                $content = Storage::disk('local')->get($file);
                $destPath = $file;
                Storage::disk('external')->put($destPath, $content);
                $copied[] = $destPath;
            }
            return response()->json([
                'status' => 'ok',
                'copiados' => $copied,
                'total' => count($copied),
            ]);
        } catch (\Exception $e) {
            Log::error('Backup error: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
            ], 500);
        }
    }
}
