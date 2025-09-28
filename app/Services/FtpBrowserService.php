<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Serviço para listar diretórios e arquivos no disco FTP configurado.
 * Abstrai chamadas para facilitar futura troca de driver (ex: SFTP).
 */
class FtpBrowserService
{
    protected string $disk;

    public function __construct(string $disk = 'ftp')
    {
        $this->disk = $disk;
    }

    /**
     * Lista conteúdo (dirs + files) de um caminho no FTP.
     * @param string $dir Caminho relativo (sem prefixo /). Vazio ou '.' => raiz.
     * @return array{dir:string, parent:?string, directories: array<int,array{basename:string,path:string}>, files: array<int,array{basename:string,path:string,size:int|null, size_human:string|null}>}
     */
    public function list(string $dir = ''): array
    {
        $dir = trim($dir);
        if ($dir === '.' || $dir === '/') $dir = '';

        // segurança básica contra path traversal
        if (str_contains($dir, '..')) {
            $dir = '';
        }

        $disk = Storage::disk($this->disk);

        // Diretórios
        $directories = collect($disk->directories($dir))->map(function ($path) use ($dir) {
            return [
                'basename' => basename($path),
                'path' => ltrim($path, '/'),
            ];
        })->sortBy('basename', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

        // Arquivos
        $files = collect($disk->files($dir))->map(function ($path) use ($disk) {
            $size = null;
            try { $size = $disk->size($path); } catch (\Throwable $e) { /* alguns servidores podem falhar */ }
            return [
                'basename' => basename($path),
                'path' => ltrim($path, '/'),
                'size' => $size,
                'size_human' => $size !== null ? $this->humanBytes($size) : null,
            ];
        })->sortBy('basename', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

        $parent = null;
        if ($dir !== '') {
            $parent = dirname($dir);
            if ($parent === '.' || $parent === DIRECTORY_SEPARATOR) $parent = '';
        }

        return [
            'dir' => $dir,
            'parent' => $parent,
            'directories' => $directories,
            'files' => $files,
        ];
    }

    protected function humanBytes(int $bytes): string
    {
        $u = ['B','KB','MB','GB','TB'];
        $i = 0;
        $value = $bytes;
        while ($value >= 1024 && $i < count($u)-1) {
            $value /= 1024; $i++;
        }
        return number_format($value, ($i===0?0:2), ',', '.') . ' ' . $u[$i];
    }
}
