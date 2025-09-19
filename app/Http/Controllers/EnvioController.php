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

class EnvioController extends Controller
{
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
        $this->middleware('auth');
        $this->middleware('permission:ENVIOS - LISTAR')->only('index');
        $this->middleware('permission:ENVIOS - INCLUIR')->only(['create','store','uploadArquivos']);
        $this->middleware('permission:ENVIOS - EDITAR')->only(['edit','update']);
        // Ver detalhes e diagnóstico requer ENVIOS - VER
        $this->middleware('permission:ENVIOS - VER')->only(['show','diagnose']);
        // Download e ZIP aceitam VER ou LISTAR (qualquer um)
        $this->middleware('permission:ENVIOS - VER|ENVIOS - LISTAR')->only(['download','zip']);
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
        if (!$isAdmin || $escopo === 'meus') { $query->where('user_id', $authId); }
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
            }
        }

        return redirect()->route('Envios.index')->with('success','Envio criado com sucesso.');
    }

    public function show(Envio $Envio)
    {
        $isAdmin = $this->isAdmin();
    if (!$isAdmin && (int)$Envio->user_id !== (int)auth()->id()) { abort(404); }
        $Envio->load(['arquivos' => function($q) use ($isAdmin){
            if (!$isAdmin) { $q->where('uploaded_by', auth()->id()); }
        }]);
        return view('Envios.show', ['envio' => $Envio]);
    }

    public function edit(Envio $Envio)
    {
    $isAdmin = $this->isAdmin();
    if (!$isAdmin && (int)$Envio->user_id !== (int)auth()->id()) { abort(404); }
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
        $arquivo->delete();
        return back()->with('success','Arquivo removido.');
    }

    public function download(Envio $Envio, EnvioArquivo $arquivo)
    {
        $authId = auth()->id();
        $isAdmin = $this->isAdmin();
        Log::info('Envios download chamado', [
            'envio_id'=>$Envio->id,
            'arquivo_id'=>$arquivo->id,
            'arquivo_envio_id'=>$arquivo->envio_id,
            'uploaded_by'=>$arquivo->uploaded_by,
            'auth_id'=>$authId,
            'is_admin'=>$isAdmin,
        ]);
        if ((int)$arquivo->envio_id !== (int)$Envio->id) {
            Log::warning('Envios download 404: arquivo não pertence ao envio', [
                'envio_id'=>$Envio->id,
                'arquivo_id'=>$arquivo->id,
                'arquivo_envio_id'=>$arquivo->envio_id,
            ]);
            abort(404);
        }
        if (!$isAdmin && $arquivo->uploaded_by !== $authId) {
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
        if (!$isAdmin && $arquivo->uploaded_by !== $authId) {
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

        // Demais tipos (ex.: imagens, PDFs) seguem via file() inline
        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($arquivo->original_name).'"'
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
}
