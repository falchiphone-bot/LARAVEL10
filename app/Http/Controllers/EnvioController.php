<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnvioRequest;
use App\Models\Envio;
use App\Models\EnvioArquivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Jobs\TranscodeEnvioVideo;
use App\Models\EnvioArquivoShare;
use App\Models\User;
use App\Models\EnvioArquivoToken;
use Illuminate\Support\Facades\URL;

class EnvioController extends Controller
{
    /**
     * Detecta se o navegador do request suporta WebP via header Accept e/ou User-Agent (fallback para Safari antigo).
     */
    protected function browserSupportsWebP(Request $request): bool
    {
        $accept = (string) $request->headers->get('Accept', '');
        if (stripos($accept, 'image/webp') !== false) {
            return true;
        }
        $ua = (string) $request->headers->get('User-Agent', '');
        // Safari ganhou suporte nativo a WebP no 14+. Vamos supor que versões antigas não suportam
        if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
            if (preg_match('/Version\/(\d+)/i', $ua, $m)) {
                $major = (int)($m[1] ?? 0);
                return $major >= 14; // 14+ suporta
            }
            return false; // Safari sem versão clara: assumir que não suporta
        }
        // Outros navegadores: se não anunciaram image/webp no Accept, considerar que não suportam
        return false;
    }

    /**
     * Detecta suporte a AVIF via header Accept e heurísticas simples de UA.
     */
    protected function browserSupportsAvif(Request $request): bool
    {
        $accept = (string) $request->headers->get('Accept', '');
        if (stripos($accept, 'image/avif') !== false) {
            return true;
        }
        $ua = (string) $request->headers->get('User-Agent', '');
        // Safari só passou a suportar AVIF por volta do 16+; heurística simples
        if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
            if (preg_match('/Version\/(\d+)/i', $ua, $m)) {
                $major = (int)($m[1] ?? 0);
                return $major >= 16; // assumir 16+
            }
            return false;
        }
        return false;
    }

    /**
     * Gera um caminho relativo no disco "public" para o fallback PNG baseado no path original.
     * Ex.: envios/2025/09/foto.webp -> envios/2025/09/foto.fallback.png
     */
    protected function buildWebpFallbackRelative(string $storedPath): string
    {
        $relative = ltrim($storedPath, '/');
        if (Str::startsWith($relative, 'public/')) { $relative = Str::after($relative, 'public/'); }
        if (Str::startsWith($relative, 'storage/')) { $relative = Str::after($relative, 'storage/'); }
        $dir = pathinfo($relative, PATHINFO_DIRNAME);
        $name = pathinfo($relative, PATHINFO_FILENAME);
        $fallback = ($dir && $dir !== '.') ? ($dir . '/' . $name . '.fallback.png') : ($name . '.fallback.png');
        return $fallback;
    }

    protected function isAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) { return false; }
        // Compatível com spatie/laravel-permission
        try {
            if (method_exists($user, 'hasAnyRole')) {
                return (bool) call_user_func([$user, 'hasAnyRole'], ['Admin', 'admin', 'Super-Admin', 'super-admin', 'Super Admin', 'SuperAdmin']);
            }
            if (method_exists($user, 'hasRole')) {
                // Verifica múltiplas variações de nome de papel
                return (bool) (
                    call_user_func([$user, 'hasRole'], 'Admin') ||
                    call_user_func([$user, 'hasRole'], 'admin') ||
                    call_user_func([$user, 'hasRole'], 'Super-Admin') ||
                    call_user_func([$user, 'hasRole'], 'super-admin') ||
                    call_user_func([$user, 'hasRole'], 'Super Admin') ||
                    call_user_func([$user, 'hasRole'], 'SuperAdmin')
                );
            }
        } catch (\Throwable $e) {
            return false;
        }
        return false;
    }
    /**
     * Resolve um caminho absoluto confiável para um arquivo armazenado,
     * cobrindo casos legados (prefixos 'public/' ou 'storage/' e armazenamento em app/ em vez de app/public).
     */
    protected function resolveAbsolutePath(string $storedPath): ?string
    {
        if (empty($storedPath)) { return null; }
        // Se já for um caminho absoluto (Linux/Unix ou Windows), retorna se existir
        $isAbsoluteUnix = Str::startsWith($storedPath, ['/','\\']);
    // Usa delimitador '#' para evitar conflitos com '/'
    $isAbsoluteWin = (bool) preg_match('#^[A-Za-z]:[\\/]#', $storedPath);
        if ($isAbsoluteUnix || $isAbsoluteWin) {
            return file_exists($storedPath) ? $storedPath : null;
        }

        $relative = ltrim($storedPath, '/');
        if (Str::startsWith($relative, 'public/')) { $relative = Str::after($relative, 'public/'); }
        if (Str::startsWith($relative, 'storage/')) { $relative = Str::after($relative, 'storage/'); }

        $candidates = [
            storage_path('app/public/' . $relative),
            storage_path('app/' . $relative),
            public_path('storage/' . $relative),
            base_path('public/' . $relative),
        ];

        foreach ($candidates as $abs) {
            if (file_exists($abs)) { return $abs; }
        }
        return null;
    }
    public function __construct()
    {
    $this->middleware('auth')->except(['publicView','publicDownload']);
    $this->middleware('permission:ENVIOS - LISTAR')->only('index');
    $this->middleware('permission:ENVIOS - INCLUIR')->only(['create','store','uploadArquivos']);
    $this->middleware('permission:ENVIOS - EDITAR')->only(['edit','update']);
    // Diagnóstico restrito a quem tem ENVIOS - VER
    $this->middleware('permission:ENVIOS - VER')->only(['diagnose']);
    // ZIP restrito a quem pode VER ou LISTAR; download será checado manualmente (owner/admin/compartilhado)
    $this->middleware('permission:ENVIOS - VER|ENVIOS - LISTAR')->only(['zip']);
    $this->middleware('permission:ENVIOS - EXCLUIR')->only(['destroy','destroyArquivo']);
    }

    public function index(Request $request)
    {
    $authId = auth()->id();
    $isAdmin = $this->isAdmin();
        $q = trim((string)$request->query('q',''));
        $createdFrom = $request->query('created_from');
        $createdTo = $request->query('created_to');
        $tipo = $request->query('tipo'); // imagem,pdf,video,audio,doc,xls,ppt,txt,zip,outros
        $escopo = $request->query('escopo'); // apenas para Admin: 'todos' | 'meus'
        if ($isAdmin) {
            $escopo = ($escopo === 'meus') ? 'meus' : 'todos';
        } else {
            $escopo = 'meus';
        }

        $query = Envio::query();
        if (!$isAdmin || $escopo === 'meus') {
            $query->where(function($q) use ($authId){
                $q->where('user_id', $authId)
                  ->orWhereHas('arquivos.shares', function($q2) use ($authId){
                      $q2->where('user_id', $authId);
                  });
            });
        }
        if ($q !== '') { $query->where('nome','like',"%{$q}%"); }
        if (!empty($createdFrom)) { $query->whereDate('created_at', '>=', $createdFrom); }
        if (!empty($createdTo)) { $query->whereDate('created_at', '<=', $createdTo); }
        if (!empty($tipo)) {
            $query->whereHas('arquivos', function($w) use ($tipo, $authId, $isAdmin, $escopo){
                $map = [
                    'imagem' => ['image/'],
                    'pdf' => ['application/pdf'],
                    'video' => ['video/'],
                    'audio' => ['audio/'],
                    'doc' => ['application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml'],
                    'xls' => ['application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml'],
                    'ppt' => ['application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml'],
                    'txt' => ['text/plain'],
                    'zip' => ['application/zip','application/x-zip-compressed','application/x-7z-compressed','application/x-rar-compressed'],
                ];
                if (!$isAdmin || $escopo === 'meus') { $w->where('uploaded_by', $authId); }
                if (isset($map[$tipo])) {
                    $patterns = $map[$tipo];
                    $w->where(function($q2) use ($patterns){
                        foreach ($patterns as $p) {
                            if (str_ends_with($p, '/')) {
                                $q2->orWhere('mime_type', 'like', $p.'%');
                            } else {
                                $q2->orWhere('mime_type', $p);
                            }
                        }
                    });
                } else {
                    $w->whereNull('id'); // nenhum filtro quando tipo inválido
                }
            });
        }
        $envios = $query
            ->withCount(['arquivos'])
            ->withMax(['arquivos as last_transcode_at' => function($q2) use ($isAdmin, $escopo, $authId){
                if (!$isAdmin || $escopo === 'meus') { $q2->where('uploaded_by', $authId); }
            }], 'last_transcode_at')
            ->when((!$isAdmin) || ($isAdmin && $escopo === 'meus'), function($q) use ($authId){
                $q->withCount(['arquivos as arquivos_user_count' => function($q2) use ($authId){
                    $q2->where('uploaded_by', $authId);
                }]);
            })
            ->orderBy('created_at','desc')->paginate(20)->appends([
            'q'=>$q,'created_from'=>$createdFrom,'created_to'=>$createdTo,'tipo'=>$tipo,'escopo'=>$escopo
        ]);
        return view('Envios.index', compact('envios','q','createdFrom','createdTo','tipo','isAdmin','escopo'));
    }

    public function create()
    {
        return view('Envios.create');
    }

    public function store(EnvioRequest $request)
    {
        $envio = Envio::create([
            'nome' => $request->input('nome'),
            'descricao' => $request->input('descricao'),
            'user_id' => auth()->id(),
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('envios/'.date('Y/m'), 'public');
                $envio->arquivos()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);

                // Pré-gerar fallback para Safari: WebP/AVIF -> PNG
                try {
                    $ext = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['webp','avif'])) {
                        $fallbackRel = $this->buildWebpFallbackRelative((string)$path);
                        if (!Storage::disk('public')->exists($fallbackRel)) {
                            $absSource = storage_path('app/public/' . ltrim($path, '/'));
                            if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                                $im = @imagecreatefromwebp($absSource);
                            } elseif ($ext === 'avif' && function_exists('imagecreatefromavif')) {
                                $im = @imagecreatefromavif($absSource);
                            } else { $im = false; }
                            if ($im) {
                                $absFallback = storage_path('app/public/' . $fallbackRel);
                                @mkdir(dirname($absFallback), 0775, true);
                                imagealphablending($im, false);
                                imagesavealpha($im, true);
                                @imagepng($im, $absFallback, 6);
                                @imagedestroy($im);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::info('Falha ao pré-gerar fallback de imagem', ['path'=>$path,'error'=>$e->getMessage()]);
                }
            }
        }

        return redirect()->route('Envios.index')->with('success','Envio criado com sucesso.');
    }

    public function show(Envio $Envio)
    {
        $authId = (int)auth()->id();
        $isAdmin = $this->isAdmin();
        $isOwner = (int)$Envio->user_id === $authId;
        $hasShares = false;
        if (!$isAdmin && !$isOwner) {
            // Verifica se há pelo menos um arquivo compartilhado com este usuário
            $hasShares = $Envio->arquivos()
                ->whereHas('shares', function($q) use ($authId){ $q->where('user_id', $authId); })
                ->exists();
            if (!$hasShares) { abort(404); }
        }

        // Carrega arquivos conforme o contexto
        if ($isAdmin || $isOwner) {
            $Envio->load('arquivos');
        } else {
            // Mostrar apenas arquivos compartilhados com o usuário (e opcionalmente os que ele mesmo enviou)
            $Envio->load(['arquivos' => function($q) use ($authId){
                $q->where(function($w) use ($authId){
                    $w->where('uploaded_by', $authId)
                      ->orWhereHas('shares', function($s) use ($authId){ $s->where('user_id', $authId); });
                });
            }]);
        }

        return view('Envios.show', ['envio' => $Envio]);
    }

    public function edit(Envio $Envio)
    {
    $isAdmin = $this->isAdmin();
    if (!$isAdmin && (int)$Envio->user_id !== (int)auth()->id()) { abort(404); }
    $Envio->load(['arquivos.sharedUsers','arquivos.tokens']);
        return view('Envios.edit', ['envio' => $Envio]);
    }

    public function update(EnvioRequest $request, Envio $Envio)
    {
    $isAdmin = $this->isAdmin();
    if (!$isAdmin && (int)$Envio->user_id !== (int)auth()->id()) { abort(404); }
        $Envio->fill($request->only(['nome','descricao']));
        $Envio->save();

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('envios/'.date('Y/m'), 'public');
                $Envio->arquivos()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                ]);

                // Pré-gerar fallback para Safari: WebP/AVIF -> PNG
                try {
                    $ext = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['webp','avif'])) {
                        $fallbackRel = $this->buildWebpFallbackRelative((string)$path);
                        if (!Storage::disk('public')->exists($fallbackRel)) {
                            $absSource = storage_path('app/public/' . ltrim($path, '/'));
                            if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                                $im = @imagecreatefromwebp($absSource);
                            } elseif ($ext === 'avif' && function_exists('imagecreatefromavif')) {
                                $im = @imagecreatefromavif($absSource);
                            } else { $im = false; }
                            if ($im) {
                                $absFallback = storage_path('app/public/' . $fallbackRel);
                                @mkdir(dirname($absFallback), 0775, true);
                                imagealphablending($im, false);
                                imagesavealpha($im, true);
                                @imagepng($im, $absFallback, 6);
                                @imagedestroy($im);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::info('Falha ao pré-gerar fallback de imagem', ['path'=>$path,'error'=>$e->getMessage()]);
                }
            }
        }

        return redirect()->route('Envios.show', $Envio)->with('success','Envio atualizado.');
    }

    public function destroy(Envio $Envio)
    {
        $isAdmin = $this->isAdmin();
        if (!$isAdmin && $Envio->user_id !== auth()->id()) { abort(404); }
        foreach ($Envio->arquivos as $arq) {
            if ($arq->path && Storage::disk('public')->exists($arq->path)) {
                Storage::disk('public')->delete($arq->path);
            }
            // Remove fallback PNG se existir
            $ext = strtolower(pathinfo((string)$arq->path, PATHINFO_EXTENSION));
            if (in_array($ext, ['webp','avif'])) {
                $fallbackRel = $this->buildWebpFallbackRelative((string)$arq->path);
                if (Storage::disk('public')->exists($fallbackRel)) {
                    Storage::disk('public')->delete($fallbackRel);
                }
            }
        }
        $Envio->delete();
        return redirect()->route('Envios.index')->with('success','Envio excluído.');
    }

    public function destroyArquivo(Envio $Envio, EnvioArquivo $arquivo)
    {
    if ((int)$arquivo->envio_id !== (int)$Envio->id) { abort(404); }
        $isAdmin = $this->isAdmin();
        if (!$isAdmin && $arquivo->uploaded_by !== auth()->id()) { abort(404); }
        if ($arquivo->path && Storage::disk('public')->exists($arquivo->path)) {
            Storage::disk('public')->delete($arquivo->path);
        }
        // Remove fallback PNG se existir
        $ext = strtolower(pathinfo((string)$arquivo->path, PATHINFO_EXTENSION));
        if (in_array($ext, ['webp','avif'])) {
            $fallbackRel = $this->buildWebpFallbackRelative((string)$arquivo->path);
            if (Storage::disk('public')->exists($fallbackRel)) {
                Storage::disk('public')->delete($fallbackRel);
            }
        }
        $arquivo->delete();
        return back()->with('success','Arquivo removido.');
    }

    public function download(Request $request, Envio $Envio, EnvioArquivo $arquivo)
    {
        $authId = auth()->id();
        $isAdmin = $this->isAdmin();
        $isPublic = ((string)$request->headers->get('X-Envios-Public')) === '1';
        Log::info('Envios download chamado', [
            'envio_id'=>$Envio->id,
            'arquivo_id'=>$arquivo->id,
            'arquivo_envio_id'=>$arquivo->envio_id,
            'uploaded_by'=>$arquivo->uploaded_by,
            'auth_id'=>$authId,
            'is_admin'=>$isAdmin,
            'is_public'=>$isPublic,
        ]);
            if ((int)$arquivo->envio_id !== (int)$Envio->id) {
            Log::warning('Envios download 404: arquivo não pertence ao envio', [
                'envio_id'=>$Envio->id,
                'arquivo_id'=>$arquivo->id,
                'arquivo_envio_id'=>$arquivo->envio_id,
            ]);
            abort(404);
        }
        // Permitir se proprietário (uploader), admin ou compartilhado com o usuário
        $isShared = EnvioArquivoShare::where('envio_arquivo_id', $arquivo->id)
            ->where('user_id', $authId)
            ->exists();
            if (!$isPublic && !$isAdmin && (int)$arquivo->uploaded_by !== (int)$authId && !$isShared) {
            Log::warning('Envios download 404: sem permissão (não admin e não é o uploader)', [
                'envio_id'=>$Envio->id,
                'arquivo_id'=>$arquivo->id,
                'uploaded_by'=>$arquivo->uploaded_by,
                'auth_id'=>$authId,
            ]);
            abort(404);
        }
        $stored = (string)$arquivo->path;
        $relative = ltrim($stored, '/');
        if (Str::startsWith($relative, 'public/')) { $relative = Str::after($relative, 'public/'); }
        if (Str::startsWith($relative, 'storage/')) { $relative = Str::after($relative, 'storage/'); }
        $absPublic = storage_path('app/public/' . $relative);
        $absLocal = storage_path('app/' . $relative);
        $absSymlink = public_path('storage/' . $relative);
        $absPublicDir = base_path('public/' . $relative);
        $absolutePath = $this->resolveAbsolutePath($stored);
        if (!$absolutePath) {
            // Fallback via Storage disk 'public'
            if (Storage::disk('public')->exists($relative)) {
                $absPublic = storage_path('app/public/' . $relative);
                Log::info('Envios download via Storage', ['envio_id'=>$Envio->id,'arquivo_id'=>$arquivo->id,'relative'=>$relative]);
                return response()->download($absPublic, $arquivo->original_name);
            }
            Log::warning('Envios download 404: arquivo não resolvido', [
                'envio_id'=>$Envio->id,
                'arquivo_id'=>$arquivo->id,
                'stored'=>$stored,
                'normalized'=>$relative,
                'abs_public'=>$absPublic,'abs_public_exists'=>file_exists($absPublic),
                'abs_local'=>$absLocal,'abs_local_exists'=>file_exists($absLocal),
                'abs_symlink'=>$absSymlink,'abs_symlink_exists'=>file_exists($absSymlink),
                'abs_public_dir'=>$absPublicDir,'abs_public_dir_exists'=>file_exists($absPublicDir),
            ]);
            abort(404);
        }
        return response()->download($absolutePath, $arquivo->original_name);
    }

    public function view(Request $request, Envio $Envio, EnvioArquivo $arquivo)
    {
        $authId = auth()->id();
        $isAdmin = $this->isAdmin();
        Log::info('Envios view chamado', [
            'envio_id'=>$Envio->id,
            'arquivo_id'=>$arquivo->id,
            'arquivo_envio_id'=>$arquivo->envio_id,
            'uploaded_by'=>$arquivo->uploaded_by,
            'auth_id'=>$authId,
            'is_admin'=>$isAdmin,
        ]);
            if ((int)$arquivo->envio_id !== (int)$Envio->id) {
            Log::warning('Envios view 404: arquivo não pertence ao envio', [
                'envio_id'=>$Envio->id,
                'arquivo_id'=>$arquivo->id,
                'arquivo_envio_id'=>$arquivo->envio_id,
            ]);
            abort(404);
        }
        // Permitir se proprietário (uploader), admin ou compartilhado com o usuário
        $isShared = EnvioArquivoShare::where('envio_arquivo_id', $arquivo->id)
            ->where('user_id', $authId)
            ->exists();
            if (!$isAdmin && (int)$arquivo->uploaded_by !== (int)$authId && !$isShared) {
            Log::warning('Envios view 404: sem permissão (não admin e não é o uploader)', [
                'envio_id'=>$Envio->id,
                'arquivo_id'=>$arquivo->id,
                'uploaded_by'=>$arquivo->uploaded_by,
                'auth_id'=>$authId,
            ]);
            abort(404);
        }
        $stored = (string)$arquivo->path;
        $absolutePath = $this->resolveAbsolutePath($stored);
        if (!$absolutePath) {
            $relative = ltrim($stored, '/');
            if (Str::startsWith($relative, 'public/')) { $relative = Str::after($relative, 'public/'); }
            if (Str::startsWith($relative, 'storage/')) { $relative = Str::after($relative, 'storage/'); }
            // Fallback via Storage disk 'public'
            if (Storage::disk('public')->exists($relative)) {
                $absPublic = storage_path('app/public/' . $relative);
                Log::info('Envios view via Storage', ['envio_id'=>$Envio->id,'arquivo_id'=>$arquivo->id,'relative'=>$relative]);
                $mime = $arquivo->mime_type ?: 'application/octet-stream';
                return response()->file($absPublic, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline; filename="'.addslashes($arquivo->original_name).'"'
                ]);
            }
            $absPublic = storage_path('app/public/' . $relative);
            $absLocal = storage_path('app/' . $relative);
            $absSymlink = public_path('storage/' . $relative);
            $absPublicDir = base_path('public/' . $relative);
            Log::warning('Envios view 404: arquivo não resolvido', [
                'envio_id'=>$Envio->id,
                'arquivo_id'=>$arquivo->id,
                'stored'=>$stored,
                'normalized'=>$relative,
                'abs_public'=>$absPublic,'abs_public_exists'=>file_exists($absPublic),
                'abs_local'=>$absLocal,'abs_local_exists'=>file_exists($absLocal),
                'abs_symlink'=>$absSymlink,'abs_symlink_exists'=>file_exists($absSymlink),
                'abs_public_dir'=>$absPublicDir,'abs_public_dir_exists'=>file_exists($absPublicDir),
            ]);
            abort(404);
        }
        // Servir com suporte a Range para vídeos/áudios (permitindo seek no player)
        $mime = $arquivo->mime_type ?: (function($p){
            $detected = @mime_content_type($p);
            return $detected ?: 'application/octet-stream';
        })($absolutePath);

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $isMedia = Str::startsWith($mime, 'video/') || Str::startsWith($mime, 'audio/') || in_array($mime, ['video/quicktime','video/x-matroska']) || in_array($ext, ['mov','mp4','m4v','webm','mkv','avi','mp3','wav','aac','ogg']);

        if ($isMedia) {
            $size = @filesize($absolutePath);
            if ($size === false) { $size = 0; }
            $start = 0;
            $end = max(0, $size - 1);
            $status = 200;
            $headers = [
                'Accept-Ranges' => 'bytes',
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.addslashes($arquivo->original_name).'"'
            ];

            $range = $request->headers->get('Range');
            if ($range && preg_match('/bytes=(\d*)-(\d*)/i', $range, $m)) {
                if ($m[1] !== '') { $start = (int)$m[1]; }
                if ($m[2] !== '') { $end = (int)$m[2]; }
                if ($start > $end || $start >= $size) {
                    return response('', 416, [ 'Content-Range' => 'bytes */'.$size ]);
                }
                $status = 206; // Partial Content
                $headers['Content-Range'] = 'bytes '.$start.'-'.$end.'/'.$size;
            }

            $length = ($size > 0) ? ($end - $start + 1) : 0;
            $headers['Content-Length'] = max(0, (int)$length);

            return response()->stream(function() use ($absolutePath, $start, $length) {
                $chunkSize = 1024 * 512; // 512KB por chunk
                $bytesToRead = $length;
                $fh = @fopen($absolutePath, 'rb');
                if ($fh === false) { return; }
                if ($start > 0) { @fseek($fh, $start); }
                while ($bytesToRead > 0 && !feof($fh)) {
                    $read = ($bytesToRead > $chunkSize) ? $chunkSize : $bytesToRead;
                    $buffer = @fread($fh, $read);
                    if ($buffer === false) { break; }
                    echo $buffer;
                    @ob_flush();
                    flush();
                    $bytesToRead -= strlen($buffer);
                    if (connection_aborted()) { break; }
                }
                @fclose($fh);
            }, $status, $headers);
        }

        // Fallback para imagens WebP em navegadores sem suporte (ex.: Safari < 14)
        $extLower = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $needsWebpFallback = ($mime === 'image/webp' || $extLower === 'webp') && !$this->browserSupportsWebP($request);
        $needsAvifFallback = ($mime === 'image/avif' || $extLower === 'avif') && !$this->browserSupportsAvif($request);
        if ($needsWebpFallback || $needsAvifFallback) {
            try {
                $fallbackRel = $this->buildWebpFallbackRelative((string)$arquivo->path);
                // Se já existe o fallback, servir direto
                if (Storage::disk('public')->exists($fallbackRel)) {
                    $abs = storage_path('app/public/' . $fallbackRel);
                    $lastMod = gmdate('D, d M Y H:i:s', @filemtime($abs) ?: time()) . ' GMT';
                    return response()->file($abs, [
                        'Content-Type' => 'image/png',
                        'Content-Disposition' => 'inline; filename="' . addslashes(pathinfo($arquivo->original_name, PATHINFO_FILENAME)) . '.png"',
                        'Cache-Control' => 'public, max-age=31536000, immutable',
                        'Last-Modified' => $lastMod,
                    ]);
                }

                // Tentar converter usando GD
                $im = false;
                if ($needsWebpFallback && function_exists('imagecreatefromwebp')) {
                    $im = @imagecreatefromwebp($absolutePath);
                } elseif ($needsAvifFallback && function_exists('imagecreatefromavif')) {
                    $im = @imagecreatefromavif($absolutePath);
                }
                if ($im !== false) {
                    // Garante diretório
                    $absFallback = storage_path('app/public/' . $fallbackRel);
                    @mkdir(dirname($absFallback), 0775, true);
                    // Preserva transparência ao salvar PNG
                    imagealphablending($im, false);
                    imagesavealpha($im, true);
                    @imagepng($im, $absFallback, 6);
                    @imagedestroy($im);
                    if (file_exists($absFallback)) {
                        $lastMod = gmdate('D, d M Y H:i:s', @filemtime($absFallback) ?: time()) . ' GMT';
                        return response()->file($absFallback, [
                            'Content-Type' => 'image/png',
                            'Content-Disposition' => 'inline; filename="' . addslashes(pathinfo($arquivo->original_name, PATHINFO_FILENAME)) . '.png"',
                            'Cache-Control' => 'public, max-age=31536000, immutable',
                            'Last-Modified' => $lastMod,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Falha ao gerar fallback PNG para WebP', [
                    'envio_id' => $Envio->id,
                    'arquivo_id' => $arquivo->id,
                    'error' => $e->getMessage(),
                ]);
            }
            // Se não conseguiu gerar, segue com o original
        }

        // Demais tipos (ex.: imagens, PDFs) seguem via file() inline com cache
        $lastMod = gmdate('D, d M Y H:i:s', @filemtime($absolutePath) ?: time()) . ' GMT';
        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($arquivo->original_name).'"',
            'Cache-Control' => Str::startsWith($mime, 'image/') ? 'public, max-age=31536000, immutable' : 'public, max-age=3600',
            'Last-Modified' => $lastMod,
        ]);
    }

    // Endpoint de diagnóstico: retorna informações do caminho do arquivo
    public function diagnose(Envio $Envio, EnvioArquivo $arquivo)
    {
        $this->authorize('ENVIOS - VER');
        if ($arquivo->envio_id !== $Envio->id) { abort(404); }
        $stored = (string)$arquivo->path;
        $relative = ltrim($stored, '/');
        if (Str::startsWith($relative, 'public/')) { $relative = Str::after($relative, 'public/'); }
        if (Str::startsWith($relative, 'storage/')) { $relative = Str::after($relative, 'storage/'); }
        $absPublic = storage_path('app/public/' . $relative);
        $absLocal = storage_path('app/' . $relative);
        $resolved = $this->resolveAbsolutePath($stored);
        return response()->json([
            'envio_id' => $Envio->id,
            'arquivo_id' => $arquivo->id,
            'stored_path' => $stored,
            'normalized_relative' => $relative,
            'abs_public' => $absPublic,
            'abs_public_exists' => file_exists($absPublic),
            'abs_local' => $absLocal,
            'abs_local_exists' => file_exists($absLocal),
            'resolved_absolute' => $resolved,
            'resolved_exists' => $resolved ? file_exists($resolved) : false,
            'public_url_candidate' => asset('storage/' . $relative),
        ]);
    }

    public function zip(Envio $Envio)
    {
        // Gera um ZIP temporário com todos os arquivos do envio e faz o download
        $isAdmin = $this->isAdmin();
        $authId = auth()->id();
        $Envio->load(['arquivos' => function($q) use ($isAdmin, $authId){
            if (!$isAdmin) { $q->where('uploaded_by', $authId); }
        }]);
        if ($Envio->arquivos->isEmpty()) {
            return back()->with('warning','Sem arquivos para compactar.');
        }

    $zipFileName = 'envio-'.$Envio->id.'-'.Str::slug($Envio->nome ?? 'arquivos').'-'.date('Ymd-His').'.zip';
        $tmpPath = storage_path('app/tmp');
        if (!is_dir($tmpPath)) { @mkdir($tmpPath, 0775, true); }
        $zipFullPath = $tmpPath . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new \ZipArchive();
        if ($zip->open($zipFullPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error','Falha ao criar arquivo ZIP.');
        }

        foreach ($Envio->arquivos as $arq) {
            if (!$arq->path) { continue; }
            $absolutePath = $this->resolveAbsolutePath((string)$arq->path);
            if (!$absolutePath) { continue; }
            $localName = $arq->original_name ?: basename($arq->path);
            // Evita nomes duplicados no zip
            $i = 1; $candidate = $localName;
            while ($zip->locateName($candidate) !== false) {
                $candidate = pathinfo($localName, PATHINFO_FILENAME)." ($i).".pathinfo($localName, PATHINFO_EXTENSION);
                $i++;
            }
            $zip->addFile($absolutePath, $candidate);
        }

        $zip->close();

        return response()->download($zipFullPath, $zipFileName)->deleteFileAfterSend(true);
    }

    // Agenda transcodificação para MP4/HLS
    public function transcode(Request $request, Envio $Envio, EnvioArquivo $arquivo)
    {
        $authId = auth()->id();
        $isAdmin = $this->isAdmin();
        if ((int)$arquivo->envio_id !== (int)$Envio->id) { abort(404); }
        if (!$isAdmin && $arquivo->uploaded_by !== $authId) { abort(403); }

        // Checa se já está processando ou concluído
        if ($arquivo->transcode_status === 'processing') {
            return back()->with('info','Este arquivo já está em processamento.');
        }
        if ($arquivo->transcode_status === 'done' && $arquivo->mp4_path && $arquivo->hls_path) {
            return back()->with('info','Este arquivo já foi convertido.');
        }

        $arquivo->update(['transcode_status' => 'pending', 'transcode_error' => null]);
        TranscodeEnvioVideo::dispatch($arquivo->id)->onQueue('default');
        return back()->with('success','Conversão para MP4/HLS agendada. Você pode atualizar a página em alguns minutos.');
    }

    // Compartilhar um arquivo do envio com outro usuário (por e-mail)
    public function share(Request $request, Envio $Envio, EnvioArquivo $arquivo)
    {
        $request->validate(['email' => 'required|email']);
        $authId = (int)auth()->id();
        $isAdmin = $this->isAdmin();
        if ((int)$arquivo->envio_id !== (int)$Envio->id) { abort(404); }
        // Apenas admin, dono do envio ou uploader do arquivo podem compartilhar
        if (!$isAdmin && $Envio->user_id !== $authId && (int)$arquivo->uploaded_by !== $authId) { abort(403); }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return back()->with('error', 'Usuário não encontrado pelo e-mail informado.');
        }
        if ((int)$user->id === $authId) {
            return back()->with('info', 'Você já tem acesso a este arquivo.');
        }
        EnvioArquivoShare::firstOrCreate([
            'envio_arquivo_id' => $arquivo->id,
            'user_id' => $user->id,
        ]);
        return back()->with('success', 'Arquivo compartilhado com '.$user->email.'.');
    }

    // Remover compartilhamento com um usuário
    public function unshare(Request $request, Envio $Envio, EnvioArquivo $arquivo, User $user)
    {
        $authId = (int)auth()->id();
        $isAdmin = $this->isAdmin();
        if ((int)$arquivo->envio_id !== (int)$Envio->id) { abort(404); }
        if (!$isAdmin && $Envio->user_id !== $authId && (int)$arquivo->uploaded_by !== $authId) { abort(403); }
        EnvioArquivoShare::where('envio_arquivo_id', $arquivo->id)
            ->where('user_id', $user->id)
            ->delete();
        return back()->with('success', 'Compartilhamento removido de '.$user->email.'.');
    }

    // Gera link público temporário
    public function createPublicLink(Request $request, Envio $Envio, EnvioArquivo $arquivo)
    {
        // $request->validate([
        //     'hours' => 'nullable|integer|min:1|max:168',
        //     'allow_download' => 'nullable|boolean',
        // ]);



        $authId = (int)auth()->id();
        $isAdmin = $this->isAdmin();
        if ((int)$arquivo->envio_id !== (int)$Envio->id) { abort(404); }
        if (!$isAdmin && $Envio->user_id !== $authId && (int)$arquivo->uploaded_by !== $authId) { abort(403); }

    // Normaliza horas: aceita vazio e valores fora de faixa, aplicando default e clamp [1,168]
    $hoursInput = $request->input('hours');
    $hours = is_numeric($hoursInput) ? (int)$hoursInput : 24;
    if ($hours < 1) { $hours = 24; }
    if ($hours > 168) { $hours = 168; }
        $allow = (bool)$request->boolean('allow_download', true);
        $token = bin2hex(random_bytes(16));

        $expiresAt = now()->addHours($hours);
        $rec = EnvioArquivoToken::create([
            'envio_arquivo_id' => $arquivo->id,
            'token' => $token,
            'expires_at' => $expiresAt,
            'allow_download' => $allow,
            'created_by' => $authId,
        ]);

        $publicViewUrl = route('Envios.public.view', [$rec->token]);
        $publicDownloadUrl = $allow ? route('Envios.public.download', [$rec->token]) : null;

        // Monta payload para alerta pós-redirect com links prontos para copiar
        $links = [ [ 'label' => 'Visualizar', 'url' => $publicViewUrl ] ];
        if ($publicDownloadUrl) { $links[] = [ 'label' => 'Download', 'url' => $publicDownloadUrl ]; }




        return redirect()->route('Envios.edit', $Envio)
            ->with('success', 'Link público gerado.')
            ->with('public_links_created', [
                'envio_id' => $Envio->id,
                'arquivo_id' => $arquivo->id,
                'expires_at' => $expiresAt->format('d/m/Y H:i'),
                'links' => $links,
            ])
            // Compat: também flasheia no formato antigo 'public_link'
            ->with('public_link', [
                'view' => $publicViewUrl,
                'download' => $publicDownloadUrl,
                'expires_at' => $expiresAt->format('d/m/Y H:i'),
            ]);
    }

    // Revoga um link público
    public function revokePublicLink(Request $request, Envio $Envio, EnvioArquivo $arquivo, EnvioArquivoToken $token)
    {
        $authId = (int)auth()->id();
        $isAdmin = $this->isAdmin();
        if ((int)$arquivo->envio_id !== (int)$Envio->id) { abort(404); }
        if (!$isAdmin && $Envio->user_id !== $authId && (int)$arquivo->uploaded_by !== $authId) { abort(403); }
        if ((int)$token->envio_arquivo_id !== (int)$arquivo->id) { abort(404); }
    $token->delete();
    return redirect()->route('Envios.edit', $Envio)->with('success', 'Link público revogado.');
    }

    // Endpoints públicos (sem auth)
    public function publicView(Request $request, string $token)
    {
        $tok = EnvioArquivoToken::where('token', $token)
            ->where(function($q){ $q->whereNull('expires_at')->orWhere('expires_at','>', now()); })
            ->firstOrFail();
        // Reutiliza lógica de view, mas sem checar permissão (já validado por token)
        $arquivo = EnvioArquivo::findOrFail($tok->envio_arquivo_id);
        $envio = Envio::findOrFail($arquivo->envio_id);
        // Força bypass de verificação por permissão usando um flow interno reduzido
        $request->headers->set('X-Envios-Public', '1');
        // Agora reaproveita o código do método view() com uma pequena adaptação: vamos chamar internamente
        return $this->view($request, $envio, $arquivo);
    }

    public function publicDownload(Request $request, string $token)
    {
        $tok = EnvioArquivoToken::where('token', $token)
            ->where(function($q){ $q->whereNull('expires_at')->orWhere('expires_at','>', now()); })
            ->firstOrFail();
        if (!$tok->allow_download) { abort(403); }
        $arquivo = EnvioArquivo::findOrFail($tok->envio_arquivo_id);
        $envio = Envio::findOrFail($arquivo->envio_id);
        $request->headers->set('X-Envios-Public', '1');
    return $this->download($request, $envio, $arquivo);
    }
}
