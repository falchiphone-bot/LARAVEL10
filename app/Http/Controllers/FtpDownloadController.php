<?php

namespace App\Http\Controllers;

use App\Services\FtpBrowserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FtpDownloadController extends Controller
{
    protected FtpBrowserService $browser;
    protected string $blockedIp = '186.237.225.6'; // IP do servidor que NÃO deve consumir downloads

    public function __construct(FtpBrowserService $browser)
    {
        $this->browser = $browser;
    }

    /**
     * Lista diretórios e arquivos do FTP.
     */
    public function index(Request $request)
    {
        $this->denyIfBlockedIp($request);
        $dirEncoded = (string) $request->query('p', '');
        $dir = '';
        if ($dirEncoded !== '') {
            try { $dir = base64_decode(strtr($dirEncoded, '._-', '+/=')); } catch (\Throwable $e) { $dir = ''; }
        }
        $data = $this->browser->list($dir);
        return view('ftp.browser', [
            'dir' => $data['dir'],
            'parent' => $data['parent'],
            'directories' => $data['directories'],
            'files' => $data['files'],
            'encoded' => fn(string $p) => rtrim(strtr(base64_encode($p), '+/=', '._-'), '='),
        ]);
    }

    /**
     * Download de um arquivo via streaming (sem carregar tudo em memória).
     */
    public function download(Request $request): StreamedResponse
    {
        $this->denyIfBlockedIp($request);
        $pathEncoded = (string) $request->query('f');
        if ($pathEncoded === '') abort(400, 'Parâmetro ausente.');
        try {
            $path = base64_decode(strtr($pathEncoded, '._-', '+/='));
        } catch (\Throwable $e) {
            abort(400, 'Parâmetro inválido.');
        }
        if ($path === false) abort(400, 'Parâmetro inválido.');
        $path = ltrim($path, '/');
        if (str_contains($path, '..')) abort(400, 'Path inválido.');

        $disk = Storage::disk('ftp');
        if (!$disk->exists($path)) abort(404, 'Arquivo não encontrado.');

        // Tenta obter stream
        try {
            $stream = $disk->readStream($path);
        } catch (\Throwable $e) {
            Log::error('FTP readStream falhou para '.$path.' => '.$e->getMessage());
            abort(500, 'Falha ao abrir stream FTP.');
        }
        if (!is_resource($stream)) abort(500, 'Não foi possível abrir o arquivo para leitura.');

        $filename = basename($path);
        Log::info('FTP download iniciado: '.$path.' por IP '.$request->ip());

        return response()->streamDownload(function () use ($stream, $path) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
                @ob_flush(); flush();
            }
            fclose($stream);
            Log::info('FTP download concluído: '.$path);
        }, $filename);
    }

    protected function denyIfBlockedIp(Request $request): void
    {
        if ($request->ip() === $this->blockedIp) {
            Log::warning('Tentativa de uso de FTP Browser a partir de IP bloqueado: '.$request->ip());
            abort(403, 'Este servidor não está autorizado a efetuar downloads via FTP. Utilize outra máquina.');
        }
    }
}
