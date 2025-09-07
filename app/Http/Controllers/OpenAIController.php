<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\OpenAI\NetworkException;
use App\Exceptions\OpenAI\ApiKeyMissingException;
use App\Jobs\TranscribeAudioJob;
use App\Models\OpenAIChat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\OpenAIChatAttachment;
use Illuminate\Support\Facades\Log;
// Storage already imported above

class OpenAIController extends Controller
{
    protected OpenAIService $openAIService;

    /**
     * @param OpenAIService $openAIService
     */


    public function __construct(OpenAIService $openAIService)
    {
        $this->middleware('auth');
    $this->middleware(['permission:OPENAI - CHAT'])->only('chat', 'chats', 'saveChat', 'loadChat', 'updateChat', 'deleteChat', 'newChat', 'uploadAttachment', 'viewAttachment', 'downloadAttachment', 'deleteAttachment');
    $this->middleware(['permission:OPENAI - TRANSCRIBE - ESPANHOL'])->only('transcribe', 'transcribeStatus');
         $this->openAIService = $openAIService;
    }


public function convertOpusToMp3(string $inputPath, string $outputPath): void
{
    // Garante que a pasta de destino existe
    $dir = dirname($outputPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    // Primeiro, tenta via php-ffmpeg se a classe existir
    try {
        if (class_exists('FFMpeg\\FFMpeg')) {
            $ffmpegClass = 'FFMpeg\\FFMpeg';
            $ffmpeg = $ffmpegClass::create([
                'ffmpeg.binaries'  => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
                'timeout'          => 3600, // segundos
                'ffmpeg.threads'   => 2,
            ]);
            $audio = $ffmpeg->open($inputPath);

            $mp3Class = 'FFMpeg\\Format\\Audio\\Mp3';
            $format = new $mp3Class();
            // Otimizações para fala: mono (1 canal), 16 kHz e bitrate menor
            $format->setAudioKiloBitrate(64);
            $format->setAudioChannels(1);
            try {
                $audio->filters()->resample(16000);
            } catch (\Throwable $t) {
                Log::warning('Falha ao aplicar resample(16000), prosseguindo sem reamostragem', ['error' => $t->getMessage()]);
            }

            $audio->save($format, $outputPath);
            return;
        }
    } catch (\Throwable $libEx) {
        Log::warning('php-ffmpeg falhou na conversão; tentando fallback CLI', ['error' => $libEx->getMessage()]);
        // prossegue para o fallback
    }

    // Fallback: usar CLI do ffmpeg diretamente
    $ffmpegBin = env('FFMPEG_PATH', '/usr/bin/ffmpeg');
    $cmd = sprintf(
        '%s -y -i %s -ac 1 -ar 16000 -b:a 64k %s 2>&1',
        escapeshellcmd($ffmpegBin),
        escapeshellarg($inputPath),
        escapeshellarg($outputPath)
    );
    $output = [];
    $this->middleware(['permission:OPENAI - CHAT'])->only('chat', 'chats', 'saveChat', 'loadChat', 'updateChat', 'deleteChat', 'newChat', 'uploadAttachment', 'downloadAttachment', 'deleteAttachment');
    @exec($cmd, $output, $exit);
    if ($exit !== 0 || !file_exists($outputPath) || filesize($outputPath) === 0) {
        $stderr = trim(implode("\n", array_slice($output, -30)));
        Log::error('ffmpeg CLI falhou ao converter opus->mp3', [
            'exitCode' => $exit,
            'stderr_tail' => $stderr,
        ]);
        throw new Exception('Falha ao converter áudio (.opus) para MP3');
    }
}


    /**
     * Test the getTextResponse method from OpenAIService.
     *
     * @param Request $request
     * @return JsonResponse|View|RedirectResponse
     */
    public function chat(Request $request): JsonResponse|View|RedirectResponse
    {
        // Se for um POST, processa a nova mensagem. Se for GET, apenas exibe a view.
        if ($request->isMethod('post')) {
            $request->validate([
                'prompt' => 'required|string|min:2',
            ], [
                'prompt.required' => 'O campo de prompt é obrigatório.',
                'prompt.min' => 'O prompt deve ter pelo menos :min caracteres.',
            ]);
        }

        // Recupera o histórico da sessão ou inicializa um novo com uma instrução de sistema.
        $messages = $request->session()->get('openai_messages', [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ]);

        $error = null;

    // Adiciona a nova mensagem do usuário ao histórico (apenas em requisições POST)
        if ($request->isMethod('post')) {
            $prompt = $request->input('prompt');
            $messages[] = ['role' => 'user', 'content' => $prompt];

            // Prepara mensagens para envio à API (com possível contexto de conversas salvas e anexos)
            $messagesToSend = $messages;

        // Preferências de busca: lembrar na sessão e usar default do config
        $searchPrefDefault = (bool) config('openai.chat.search.enabled_default', false);
        $searchInChats = $request->boolean('search_in_chats', (bool) $request->session()->get('openai_search_in_chats', $searchPrefDefault));
        $request->session()->put('openai_search_in_chats', $searchInChats);

            if ($searchInChats) {
                $cfg = config('openai.chat.search');
                $maxTerms   = (int) ($cfg['max_terms'] ?? 5);
                $minLen     = (int) ($cfg['min_term_length'] ?? 4);
                $maxQuery   = (int) ($cfg['max_conversations_to_query'] ?? 5);
                $maxInject  = (int) ($cfg['max_conversations_to_inject'] ?? 3);
                $tailPerConv= (int) ($cfg['tail_messages_per_conversation'] ?? 6);
                $contextPreamble = (string) ($cfg['context_preamble'] ?? '');

                $terms = collect(preg_split('/\s+/u', (string) $prompt, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn($w) => trim($w))
                    ->filter(fn($w) => mb_strlen($w) >= $minLen)
                    ->unique()
                    ->take($maxTerms)
                    ->values();
                // Normaliza termos: remove pontuação e aplica lower para comparação case-insensitive em drivers não-sqlsrv
                $termsNorm = $terms->map(function ($w) {
                    $w = preg_replace('/[\p{P}]+/u', '', (string) $w) ?? '';
                    return mb_strtolower($w);
                })->filter()->values();

                if ($terms->isNotEmpty()) {
                    $allowAll = (bool) ($cfg['allow_all'] ?? false);
                    $perm = $cfg['allow_all_permission'] ?? null;
                    $userCanAll = $allowAll && (!$perm || $request->user()->can($perm));
            // Lê e persiste o escopo escolhido (quando permitido); caso contrário força 'mine'
            $scope = $userCanAll ? ($request->input('search_scope', (string) $request->session()->get('openai_search_scope', 'mine'))) : 'mine';
            $request->session()->put('openai_search_scope', $scope);

                    $collation = (string) (config('openai.chat.search.collation') ?? 'Latin1_General_CI_AI');
                    $driver = DB::connection()->getDriverName(); // sqlsrv, mysql, pgsql, sqlite

                    // Heurística: extrair possível frase (ex.: endereços)
                    $addressPhrase = null;
                    if (preg_match('/\b(Rua|Avenida|Av\.|Travessa|Tv\.|Rodovia|Estrada|Praça|Praca)\s+([\p{L}\d\s\.-]+?)(?=[\?\.,;\n]|$)/u', (string) $prompt, $m)) {
                        $addressPhrase = trim($m[0]);
                    }
                    $addressPhraseNorm = $addressPhrase ? mb_strtolower(preg_replace('/[\p{P}]+/u', '', $addressPhrase)) : null;

                    $runSearch = function (string $scopeToUse, bool $usePhraseOnly = false) use ($terms, $termsNorm, $maxQuery, $collation, $driver, $addressPhrase, $addressPhraseNorm) {
                        // Inclui o dono da conversa para rotular corretamente o emissor (em vez de "Você")
                        $q = DB::table('open_a_i_chats')
                            ->leftJoin('users', 'users.id', '=', 'open_a_i_chats.user_id')
                            ->select(
                                'open_a_i_chats.title',
                                'open_a_i_chats.messages',
                                'open_a_i_chats.updated_at',
                                'open_a_i_chats.user_id',
                                DB::raw("COALESCE(users.name, 'Usuário') AS owner_name")
                            );
                        if ($scopeToUse !== 'all') {
                            $q->where('user_id', Auth::id());
                        }
                        $q->where(function ($qq) use ($terms, $termsNorm, $collation, $driver, $addressPhrase, $addressPhraseNorm, $usePhraseOnly) {
                            if (!$usePhraseOnly) {
                                foreach ($terms as $idx => $t) {
                                    if ($driver === 'sqlsrv') {
                                        $safe = str_replace(['%', '_'], ['[%]', '[_]'], $t);
                                        $qq->orWhereRaw("messages COLLATE $collation LIKE ?", ['%' . $safe . '%'])
                                           ->orWhereRaw("title COLLATE $collation LIKE ?",    ['%' . $safe . '%']);
                                    } elseif ($driver === 'pgsql') {
                                        // ILIKE no Postgres
                                        $qq->orWhereRaw('messages ILIKE ?', ['%' . $termsNorm[$idx] . '%'])
                                           ->orWhereRaw('title ILIKE ?',    ['%' . $termsNorm[$idx] . '%']);
                                    } else {
                                        // mysql/sqlite: LOWER(col) LIKE lower(term)
                                        $qq->orWhereRaw('LOWER(messages) LIKE ?', ['%' . $termsNorm[$idx] . '%'])
                                           ->orWhereRaw('LOWER(title) LIKE ?',    ['%' . $termsNorm[$idx] . '%']);
                                    }
                                }
                            }
                            if ($addressPhrase) {
                                if ($driver === 'sqlsrv') {
                                    $safePhrase = str_replace(['%', '_'], ['[%]', '[_]'], $addressPhrase);
                                    $qq->orWhereRaw("messages COLLATE $collation LIKE ?", ['%' . $safePhrase . '%'])
                                       ->orWhereRaw("title COLLATE $collation LIKE ?",    ['%' . $safePhrase . '%']);
                                } elseif ($driver === 'pgsql') {
                                    $qq->orWhereRaw('messages ILIKE ?', ['%' . $addressPhraseNorm . '%'])
                                       ->orWhereRaw('title ILIKE ?',    ['%' . $addressPhraseNorm . '%']);
                                } else {
                                    $qq->orWhereRaw('LOWER(messages) LIKE ?', ['%' . $addressPhraseNorm . '%'])
                                       ->orWhereRaw('LOWER(title) LIKE ?',    ['%' . $addressPhraseNorm . '%']);
                                }
                            }
                        });
                        return $q->orderBy('updated_at', 'desc')->limit($maxQuery)->get();
                    };

                    // 1) Busca por termos (escopo atual)
                    $hits = $runSearch($scope);
                    // 2) Se vazio e temos frase de endereço, tenta frase (escopo atual)
                    if ($hits->count() === 0 && $addressPhrase) {
                        $hits = $runSearch($scope, true);
                    }
                    // 3) Se ainda vazio e usuário pode buscar em todas, tenta escopo "all"
                    if ($hits->count() === 0 && $userCanAll && $scope !== 'all') {
                        $hits = $runSearch('all');
                        if ($hits->count() === 0 && $addressPhrase) {
                            $hits = $runSearch('all', true);
                        }
                    }

                    Log::debug('OpenAI chat search debug', [
                        'driver' => $driver,
                        'scope' => $scope,
                        'terms' => $terms->all(),
                        'termsNorm' => $termsNorm->all(),
                        'addressPhrase' => $addressPhrase,
                        'addressPhraseNorm' => $addressPhraseNorm,
                        'hits' => $hits->count(),
                    ]);

                    if ($hits->count() > 0) {
                        $snippets = [];
                        foreach ($hits as $hit) {
                            $arr = json_decode($hit->messages ?? '[]', true);
                            if (!is_array($arr)) { continue; }
                            $filtered = array_values(array_filter($arr, fn($m) => ($m['role'] ?? '') !== 'system'));
                            $tail = array_slice($filtered, -$tailPerConv);
                            $ownerName = (string)($hit->owner_name ?? 'Usuário');
                            $text = collect($tail)->map(function ($m) use ($ownerName) {
                                $who = $m['role'] === 'user' ? $ownerName : 'Assistente';
                                return $who . ': ' . (string) ($m['content'] ?? '');
                            })->implode("\n");
                            if ($text) {
                                $header = '- ' . ($hit->title ?? 'Conversa') . ' — Autor: ' . ((string)($hit->owner_name ?? 'Usuário')) . " (" . (string) $hit->updated_at . ")";
                                $snippets[] = $header . "\n" . $text;
                            }
                            if (count($snippets) >= $maxInject) { break; }
                        }
                        if (!empty($snippets)) {
                            $preamble = $contextPreamble ?: 'Use o contexto abaixo quando ele responder diretamente à pergunta; não invente.';
                            $context = $preamble . "\n\n" . implode("\n\n", $snippets);
                            $contextMsg = ['role' => 'system', 'content' => $context];
                            array_splice($messagesToSend, 1, 0, [$contextMsg]);
                        }
                    }
                }
            }

            // 2) Injetar conteúdo de anexos (PDF/Imagem) do chat ativo (se habilitado)
            $attCfg = (array) config('openai.chat.attachments', []);
            $attEnabled = (bool) ($attCfg['enabled'] ?? true);
            if ($attEnabled) {
                $currentIdForAtt = (int) $request->session()->get('openai_current_chat_id');
                if ($currentIdForAtt > 0) {
                    $maxFiles = (int) ($attCfg['max_files'] ?? 3);
                    $maxCharsPerFile = (int) ($attCfg['max_chars_per_file'] ?? 20000);
                    $maxTotalChars = (int) ($attCfg['max_total_chars'] ?? 40000);
                    $attPreamble = (string) ($attCfg['context_preamble'] ?? '');

                    $attachments = OpenAIChatAttachment::where('chat_id', $currentIdForAtt)
                        ->where('user_id', Auth::id())
                        ->latest('created_at')
                        ->take($maxFiles)
                        ->get();

                    $total = 0;
                    $chunks = [];
                    foreach ($attachments as $att) {
                        try {
                            $text = $this->extractAttachmentText($att, $maxCharsPerFile);
                        } catch (\Throwable $t) {
                            Log::warning('Falha extraindo texto do anexo: '.$t->getMessage(), ['att' => $att->id]);
                            $text = '';
                        }
                        if (!$text) { continue; }
                        if ($total >= $maxTotalChars) { break; }
                        $remain = max(0, $maxTotalChars - $total);
                        $slice = mb_substr($text, 0, $remain);
                        $total += mb_strlen($slice);
                        $chunks[] = "Arquivo: {$att->original_name}\n".$slice;
                    }
                    if (!empty($chunks)) {
                        $prefix = $attPreamble ?: 'Considere o conteúdo dos anexos abaixo:';
                        $attMsg = [
                            'role' => 'system',
                            'content' => $prefix."\n\n".implode("\n\n---\n\n", $chunks),
                        ];
                        // Insere após a 1ª mensagem (system original)
                        array_splice($messagesToSend, 1, 0, [$attMsg]);
                    }
                }
            }

            try {
                $response = $this->openAIService->getChatResponse($messagesToSend);

                // Handle API-level errors
                if (isset($response['error'])) {
                    $error = $response['error']['message'] ?? 'Erro desconhecido da API OpenAI.';
                    Log::error('OpenAI API Error: ' . $error, ['response' => $response]);
                } else {
                    // Adiciona a resposta do assistente ao histórico (mantém sessão sem poluir com contexto)
                    $assistantMessage = $response['choices'][0]['message']['content'] ?? null;
                    if ($assistantMessage) {
                        $messages[] = ['role' => 'assistant', 'content' => $assistantMessage];
                    } else {
                        $error = 'Não foi possível obter uma resposta válida da API.';
                        Log::error($error, ['response' => $response]);
                    }
                }

            } catch (ApiKeyMissingException $e) {
                $error = 'A chave da API OpenAI não foi configurada. Verifique seu arquivo .env.';
                Log::error($error, ['exception' => $e]);
            } catch (NetworkException $e) {
                $error = 'Falha de comunicação com a API da OpenAI. Verifique a conexão e os logs.';
                Log::error($error, ['exception' => $e]);
            } catch (Exception $e) {
                $error = 'Ocorreu um erro inesperado: ' . $e->getMessage();
                Log::error($error, ['exception' => $e]);
            }

            // Salva o histórico atualizado na sessão
            $request->session()->put('openai_messages', $messages);

            // Se a conversa atual já estiver salva, atualiza automaticamente no banco
            $currentId = (int) $request->session()->get('openai_current_chat_id');
            if ($currentId > 0) {
                $affected = DB::table('open_a_i_chats')
                    ->where('id', $currentId)
                    ->where('user_id', Auth::id())
                    ->update([
                        'messages'   => json_encode($messages, JSON_UNESCAPED_UNICODE),
                        'updated_at' => DB::raw('GETDATE()'),
                    ]);
                if ($affected === 0) {
                    $request->session()->forget('openai_current_chat_id');
                }
            }
        }

        if ($request->wantsJson()) {
            return $error
                ? response()->json(['error' => $error], 500)
                : response()->json(['messages' => $messages]);
        }

        if ($error) {
            return back()->with('error', $error)->withInput();
        }

        // Passa o histórico completo para a view
        $searchPrefDefault = (bool) config('openai.chat.search.enabled_default', false);
        $searchInChatsPref = (bool) $request->session()->get('openai_search_in_chats', $searchPrefDefault);
        $searchScopePref = (string) $request->session()->get('openai_search_scope', 'mine');

    // Carrega anexos do chat ativo (se houver)
    $attachments = collect();
        $currentId = (int) $request->session()->get('openai_current_chat_id');
        if ($currentId > 0) {
            $attachments = OpenAIChatAttachment::where('chat_id', $currentId)
                ->where('user_id', Auth::id())
                ->latest('created_at')
                ->get();
        }

        // Tipos para seleção ao salvar/atualizar e chat atual (para pré-seleção do tipo)
        $types = \App\Models\OpenAIChatType::orderBy('name')->get();
        $currentChat = null;
        if ($currentId > 0) {
            $currentChat = OpenAIChat::select('id','type_id','title','code','target_min','target_avg','target_max')->where('id', $currentId)->first();
        }

        return view('openai.chat', [
            'messages' => $messages,
            'searchInChats' => $searchInChatsPref,
            'searchScope' => $searchScopePref,
            'attachments' => $attachments,
            'types' => $types,
            'currentChat' => $currentChat,
        ]);
    }

    /**
     * Extrai texto de um anexo suportando PDF e imagens comuns.
     */
    protected function extractAttachmentText(OpenAIChatAttachment $att, int $maxCharsPerFile = 20000): string
    {
        $disk = $att->disk ?: 'public';
        $path = $att->path;
        if (!$path) { return ''; }
        $mime = (string) ($att->mime_type ?? '');

    // Recupera conteúdo: sempre copia para arquivo temporário via stream (compatível com qualquer driver)
    $diskObj = Storage::disk($disk);
    $stream = $diskObj->readStream($path);
    if (!$stream) { return ''; }
    $tmp = tempnam(sys_get_temp_dir(), 'att_');
    $out = fopen($tmp, 'wb');
    stream_copy_to_stream($stream, $out);
    fclose($out);
    if (is_resource($stream)) { fclose($stream); }
    $absolute = $tmp;

        $text = '';
        // PDF
        if (stripos($mime, 'pdf') !== false || str_ends_with(strtolower($att->original_name), '.pdf')) {
            $text = $this->extractFromPdf($absolute);
        }
        // Imagens: png, jpg, jpeg, webp
        elseif (preg_match('/\.(png|jpe?g|webp)$/i', $att->original_name)) {
            $text = $this->extractFromImage($absolute);
        }

        $text = trim((string) $text);
        if ($text === '') { return ''; }
        return mb_substr($text, 0, $maxCharsPerFile);
    }

    protected function extractFromPdf(string $absolutePath): string
    {
        // Tenta bibliotecas comuns. Prioriza Smalot\PdfParser
        try {
            if (class_exists('Smalot\\PdfParser\\Parser')) {
                $parserClass = 'Smalot\\PdfParser\\Parser';
                $parser = new $parserClass();
                $pdf = $parser->parseFile($absolutePath);
                return (string) $pdf->getText();
            }
        } catch (\Throwable $t) {
            Log::warning('PDF parse via Smalot falhou', ['error' => $t->getMessage()]);
        }
        // Fallback simples: usa pdftotext se disponível
        try {
            $cmd = 'pdftotext -layout '.escapeshellarg($absolutePath).' -';
            $out = @shell_exec($cmd);
            if (is_string($out) && $out !== '') { return $out; }
        } catch (\Throwable $t) {
            // ignore
        }
        return '';
    }

    protected function extractFromImage(string $absolutePath): string
    {
        // Preferência: ocr via tesseract, se instalado
        try {
            $cmd = 'tesseract '.escapeshellarg($absolutePath).' stdout -l por+eng 2>/dev/null';
            $out = @shell_exec($cmd);
            if (is_string($out) && trim($out) !== '') {
                return trim($out);
            }
        } catch (\Throwable $t) {
            // ignore
        }

        // Fallback: sem OCR disponível
        return '';
    }
    public function clearChat(Request $request): RedirectResponse
    {
        $request->session()->forget('openai_messages');
        $request->session()->forget('openai_current_chat_id');

        return redirect()->route('openai.chat');
    }

    /**
     * Upload de anexo para o chat atual (em sessão ou salvo).
     */
    public function uploadAttachment(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
        ], [
            'file.required' => 'Selecione um arquivo.',
            'file.file'     => 'Arquivo inválido.',
            'file.max'      => 'O arquivo não pode ser superior a 50 MB.',
        ]);

        $file = $request->file('file');
        $disk = 'public';
        $path = $file->store('openai/chat_attachments', $disk);

        // Garante que exista um chat salvo; se não houver, cria um rapidamente com o histórico atual
        $messages = $request->session()->get('openai_messages', [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ]);
        $currentId = (int) $request->session()->get('openai_current_chat_id');

        if ($currentId <= 0) {
            // Cria um chat básico com título padrão
            $title = 'Conversa com anexos';
            $currentId = DB::table('open_a_i_chats')->insertGetId([
                'user_id'  => Auth::id(),
                'title'    => $title,
                'messages' => json_encode($messages, JSON_UNESCAPED_UNICODE),
            ]);
            $request->session()->put('openai_current_chat_id', (int) $currentId);
        }

        OpenAIChatAttachment::create([
            'chat_id'       => $currentId,
            'user_id'       => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'disk'          => $disk,
            'mime_type'     => $file->getClientMimeType(),
            'size'          => $file->getSize(),
            'message_index' => null,
        ]);

        // Atualiza updated_at do chat
        DB::table('open_a_i_chats')
            ->where('id', $currentId)
            ->where('user_id', Auth::id())
            ->update(['updated_at' => DB::raw('GETDATE()')]);

        return back()->with('success', 'Arquivo anexado à conversa.');
    }

    /**
     * Download de um anexo vinculado ao chat do usuário.
     */
    public function downloadAttachment(OpenAIChatAttachment $attachment)
    {
        if ((int) $attachment->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $disk = Storage::disk($attachment->disk ?: 'public');
        $stream = $disk->readStream($attachment->path);
        if (!$stream) {
            abort(404);
        }
        $filename = $attachment->original_name ?: 'arquivo';
        $mime = $attachment->mime_type ?: 'application/octet-stream';
        return response()->streamDownload(function () use ($stream) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
            }
            if (is_resource($stream)) { fclose($stream); }
        }, $filename, ['Content-Type' => $mime]);
    }

    /**
     * Visualização inline de um anexo (abre no navegador quando suportado).
     */
    public function viewAttachment(OpenAIChatAttachment $attachment)
    {
        if ((int) $attachment->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $disk = Storage::disk($attachment->disk ?: 'public');
        $stream = $disk->readStream($attachment->path);
        if (!$stream) {
            abort(404);
        }
        $filename = $attachment->original_name ?: 'arquivo';
        $mime = $attachment->mime_type ?: 'application/octet-stream';
        return response()->stream(function () use ($stream) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
            }
            if (is_resource($stream)) { fclose($stream); }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    /**
     * Remoção de um anexo vinculado ao chat do usuário.
     */
    public function deleteAttachment(OpenAIChatAttachment $attachment): RedirectResponse
    {
        if ((int) $attachment->user_id !== (int) Auth::id()) {
            abort(403);
        }
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();
        return back()->with('success', 'Anexo removido.');
    }

    /**
     * Handles audio transcription and translation.
     *
     * @param Request $request
     * @return JsonResponse|View|RedirectResponse
     */
    public function transcribe(Request $request): JsonResponse|View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                // inclui opus (e ogg, comum para arquivos .opus) na validação
                'audio_file' => 'required|file|mimes:mp3,mp4,mpeg,mpga,m4a,wav,webm,opus,ogg',
                'async' => 'nullable|boolean',
            ], [
                'audio_file.required' => 'Por favor, envie um arquivo de áudio.',
                'audio_file.mimes' => 'O formato do arquivo de áudio não é suportado. Use: opus, mp3, mp4, mpeg, mpga, m4a, wav, webm.',
            ]);

            $file = $request->file('audio_file');
            $originalExtension = strtolower($file->getClientOriginalExtension() ?: '');

            // Garante diretório de áudio
            $audioDir = storage_path('app/audio');
            if (!is_dir($audioDir)) {
                @mkdir($audioDir, 0775, true);
            }

            // Salva o arquivo enviado
            $originalFilename = uniqid('audio_') . ($originalExtension ? ('.' . $originalExtension) : '');
            $originalPath = $audioDir . '/' . $originalFilename;
            $file->move($audioDir, $originalFilename);

            // Caminho que será usado para a transcrição (pode mudar após conversão)
            $audioPath = $originalPath;

            // Se for .opus (ou .ogg que contenha opus), converte para .mp3
            if (in_array($originalExtension, ['opus', 'ogg'])) {
                try {
                    $mp3Filename = pathinfo($originalFilename, PATHINFO_FILENAME) . '.mp3';
                    $mp3Path = $audioDir . '/' . $mp3Filename;
                    $this->convertOpusToMp3($originalPath, $mp3Path);
                    // Usa o mp3 convertido
                    $audioPath = $mp3Path;
                    // Remove o original .opus/.ogg para economizar espaço
                    if (file_exists($originalPath)) {
                        @unlink($originalPath);
                    }
                } catch (Exception $e) {
                    // Se a conversão falhar, remove o arquivo original e retorna erro
                    if (file_exists($originalPath)) {
                        @unlink($originalPath);
                    }
                    $error = 'Falha ao converter o arquivo de áudio (.opus) para MP3: ' . $e->getMessage();
                    Log::error($error, ['exception' => $e]);

                    if ($request->wantsJson()) {
                        return response()->json(['error' => $error], 500);
                    }
                    return back()->with('error', $error)->withInput();
                }
            }

            // Modo assíncrono: despacha job e retorna ID
            if ($request->boolean('async')) {
                $jobId = uniqid('job_', true);
                // Movemos o arquivo para storage (já movido acima) e despachamos o job apontando para o caminho
                TranscribeAudioJob::dispatch($audioPath, $jobId, 'es')->onQueue('default');

                if ($request->wantsJson()) {
                    return response()->json(['job_id' => $jobId]);
                }

                return redirect()->route('openai.transcribe.status', ['jobId' => $jobId]);
            }

            $error = null;
            $transcribedText = '';
            $translatedText = '';

            try {
                // 1. Transcribe Spanish audio to Spanish text
                $transcriptionResponse = $this->openAIService->getTranscription($audioPath, 'es');

                // Apaga o arquivo (convertido ou original) após o envio para a IA
                if (file_exists($audioPath)) {
                    @unlink($audioPath);
                }

                if (isset($transcriptionResponse['error'])) {
                    $error = $transcriptionResponse['error']['message'] ?? 'Erro desconhecido ao transcrever o áudio.';
                    Log::error('OpenAI Transcription API Error: ' . $error, ['response' => $transcriptionResponse]);
                } else {
                    $transcribedText = $transcriptionResponse['text'] ?? '';

                    if ($transcribedText) {
                        // 2. Translate the Spanish text to Portuguese
                        $messages = [
                            ['role' => 'system', 'content' => 'Você é um assistente de tradução.'],
                            ['role' => 'user', 'content' => "Traduza o seguinte texto do espanhol para o português:\n\n" . $transcribedText],
                        ];
                        $translationResponse = $this->openAIService->getChatResponse($messages);

                        if (isset($translationResponse['error'])) {
                            $error = $translationResponse['error']['message'] ?? 'Erro desconhecido ao traduzir o texto.';
                            Log::error('OpenAI Chat API Error for translation: ' . $error, ['response' => $translationResponse]);
                        } else {
                            $translatedText = $translationResponse['choices'][0]['message']['content'] ?? '';
                        }
                    } else {
                        $error = 'Não foi possível extrair o texto do áudio. O áudio pode estar vazio ou em um formato não reconhecido.';
                        Log::error($error, ['response' => $transcriptionResponse]);
                    }
                }

            } catch (ApiKeyMissingException $e) {
                $error = 'A chave da API OpenAI não foi configurada. Verifique seu arquivo .env.';
                Log::error($error, ['exception' => $e]);
            } catch (NetworkException $e) {
                $error = 'Falha de comunicação com a API da OpenAI. Verifique a conexão e os logs.';
                Log::error($error, ['exception' => $e]);
            } catch (\InvalidArgumentException $e) {
                $error = 'Erro: ' . $e->getMessage();
                Log::error($error, ['exception' => $e]);
            } catch (\Exception $e) {
                $error = 'Ocorreu um erro inesperado: ' . $e->getMessage();
                Log::error($error, ['exception' => $e]);
            }

            if ($request->wantsJson()) {
                return $error
                    ? response()->json(['error' => $error], 500)
                    : response()->json(['transcribed_text' => $transcribedText, 'translated_text' => $translatedText]);
            }

            if ($error) {
                return back()->with('error', $error)->withInput();
            }

            return redirect()->route('openai.transcribe')
                ->with('transcribedText', $transcribedText)
                ->with('translatedText', $translatedText);
        }

        // For GET requests, just show the view. Data will be available from the session flash.
        return view('openai.transcribe');
    }

    /**
     * Check async transcription job status.
     */
    public function transcribeStatus(string $jobId): JsonResponse|View
    {
        $resultPath = storage_path('app/audio/results/' . $jobId . '.json');
        $data = null;
        if (file_exists($resultPath)) {
            $json = file_get_contents($resultPath) ?: '';
            $data = json_decode($json, true) ?: null;
        }

        if (request()->wantsJson()) {
            return response()->json($data ?: ['status' => 'pending']);
        }

        return view('openai.transcribe-status', [
            'jobId' => $jobId,
            'data' => $data,
        ]);
    }

    /**
     * Lista conversas salvas do usuário autenticado.
     */
    public function chats(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $typeId = (int) $request->input('type_id', 0);
    $sort = (string) $request->input('sort', 'updated'); // updated|title|type|code
    $dir  = strtolower((string) $request->input('dir', $sort === 'updated' ? 'desc' : 'asc'));
    if (!in_array($sort, ['updated','title','type','code'], true)) { $sort = 'updated'; }
    if (!in_array($dir, ['asc','desc'], true)) { $dir = $sort === 'updated' ? 'desc' : 'asc'; }
    $perPage = (int) $request->input('per_page', 12);
    $maxPerPage = 500; // limite de segurança
    if ($perPage < 1) { $perPage = 1; }
    if ($perPage > $maxPerPage) { $perPage = $maxPerPage; }
        // Modo de visualização: usa o fornecido ou último da sessão
        $view = $request->input('view');
        if (!in_array($view, ['cards','table'], true)) {
            $view = session('openai_chats_last_view', 'cards');
        }
    $query = OpenAIChat::where('user_id', Auth::id());
    // Total geral antes de filtros (para exibir proporção)
    $totalAll = (clone $query)->count();

        if ($q !== '') {
            $driver = DB::connection()->getDriverName(); // sqlsrv, mysql, pgsql, sqlite
            if ($driver === 'sqlsrv') {
                $collation = (string) (config('openai.chat.search.collation') ?? 'Latin1_General_CI_AI');
                $safe = str_replace(['%', '_'], ['[%]', '[_]'], $q);
                $query->whereRaw("title COLLATE $collation LIKE ?", ['%' . $safe . '%']);
            } elseif ($driver === 'pgsql') {
                $query->whereRaw('title ILIKE ?', ['%' . $q . '%']);
            } else {
                $query->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($q) . '%']);
            }
        }

        if ($typeId > 0) {
            $query->where('type_id', $typeId);
        }

        // Ordenação
        if ($sort === 'title') {
            $query->orderBy('title', $dir);
        } elseif ($sort === 'code') {
            if (Schema::hasColumn('open_a_i_chats','code')) {
                $query->orderBy('code', $dir);
            } else {
                // Coluna ainda não existe: fallback
                $sort = 'updated';
                $query->orderBy('updated_at', $dir);
            }
        } elseif ($sort === 'type') {
            // Ordena pelo nome do tipo (left join)
            $query->leftJoin('openai_chat_types as t', 't.id', '=', 'open_a_i_chats.type_id')
                  ->select('open_a_i_chats.*')
                  ->orderByRaw('CASE WHEN t.name IS NULL THEN 1 ELSE 0 END, t.name '.$dir); // nulos ao final
        } else { // updated
            $query->orderBy('updated_at', $dir);
        }

        $chats = $query->with('type')->paginate($perPage)->appends([
            'q' => $q,
            'type_id' => $typeId,
            'sort' => $sort,
            'dir' => $dir,
            'per_page' => $perPage,
        ]);

        $types = \App\Models\OpenAIChatType::orderBy('name')->get();

        // Persistir últimos parâmetros para uso em outros lugares (ex: view chat)
        session()->put('openai_chats_last_params', [
            'q' => $q ?: null,
            'type_id' => $typeId ?: null,
            'sort' => $sort,
            'dir' => $dir,
            'per_page' => $perPage,
            'view' => $view,
        ]);
        session()->put('openai_chats_last_view', $view);

    return view('openai.chats', compact('chats', 'q', 'typeId', 'types', 'sort', 'dir', 'perPage', 'totalAll', 'maxPerPage'));
    }

    /**
     * Salva a conversa atual (da sessão) com um título.
     */
    public function saveChat(Request $request): RedirectResponse
    {
        $messages = $request->session()->get('openai_messages', []);
        if (count($messages) <= 1) {
            return back()->with('error', 'Nada para salvar. Envie ao menos uma mensagem e obtenha uma resposta.');
        }

        $request->validate([
            'title' => 'nullable|string|max:100',
            'mode'  => 'nullable|in:update,new',
            'type_id' => 'nullable|integer|exists:openai_chat_types,id',
            'code' => 'nullable|string|min:1|max:5',
        ]);

        $rawTitle = $request->input('title');
        $titleNormalized = null;
        if ($rawTitle !== null && trim($rawTitle) !== '') {
            $titleNormalized = Str::limit(trim(preg_replace('/\s+/', ' ', (string) $rawTitle)), 100, '…');
        }

        $typeIdInput = $request->input('type_id');
        $typeId = ($typeIdInput === null || $typeIdInput === '' ) ? null : (int) $typeIdInput;

        $currentId = (int) $request->session()->get('openai_current_chat_id');
        $mode = $request->input('mode'); // 'update' ou 'new'

        // Atualizar conversa existente quando houver ID e modo != new
        if ($currentId > 0 && $mode !== 'new') {
            $data = [
                'messages'   => json_encode($messages, JSON_UNESCAPED_UNICODE),
                'updated_at' => DB::raw('GETDATE()'),
            ];
            if ($titleNormalized) {
                $data['title'] = $titleNormalized;
            }
            // Permite atualizar/limpar o tipo
            $data['type_id'] = $typeId;
            $codeInput = $request->input('code');
            if ($codeInput !== null && trim($codeInput) !== '' && Schema::hasColumn('open_a_i_chats','code')) {
                $data['code'] = Str::upper(substr(trim($codeInput),0,5));
            }

            $affected = DB::table('open_a_i_chats')
                ->where('id', $currentId)
                ->where('user_id', Auth::id())
                ->update($data);

            if ($affected > 0) {
                return redirect()->route('openai.chats')->with('success', 'Conversa atualizada com sucesso.');
            } else {
                // Se a referência não existir mais, esquecer e seguir para salvar como nova
                $request->session()->forget('openai_current_chat_id');
            }
        }

        // Salvar como nova
        $lastUser = collect($messages)->reverse()->firstWhere('role', 'user');
        $title = $titleNormalized ?? Str::limit(trim(preg_replace('/\s+/', ' ', (string)($lastUser['content'] ?? 'Conversa'))), 100, '…');

        $codeInput = $request->input('code');
        $codeVal = null;
    if ($codeInput !== null && trim($codeInput) !== '' && Schema::hasColumn('open_a_i_chats','code')) {
            $codeVal = Str::upper(substr(trim($codeInput),0,5));
        }

        $newId = DB::table('open_a_i_chats')->insertGetId([
            'user_id'  => Auth::id(),
            'title'    => $title,
            'messages' => json_encode($messages, JSON_UNESCAPED_UNICODE),
            'type_id'  => $typeId,
            'code'     => $codeVal,
            // created_at e updated_at usam defaults da tabela (GETDATE())
        ]);

        $request->session()->put('openai_current_chat_id', (int) $newId);

        return redirect()->route('openai.chats')->with('success', 'Conversa salva com sucesso.');
    }

    /**
     * Renomeia uma conversa salva do usuário.
     */
    public function updateChat(OpenAIChat $chat, Request $request): RedirectResponse
    {
        if ((int) $chat->user_id !== (int) Auth::id()) {
            abort(403);
        }

        // Normalização simples: aceita "5.001,50" / "5001,50" / "5001" / "5.001" / "5001.50"
        foreach(['target_min','target_avg','target_max'] as $col){
            $raw = $request->input($col);
            if($raw===null || $raw==='') continue;
            Log::debug('updateChat raw target', ['field'=>$col, 'raw'=>$raw]);
            $norm = str_replace([' ', "\u{00A0}"], '', (string)$raw);
            if(strpos($norm, ',') !== false){
                // formato BR
                $norm = preg_replace('/\.(?=\d{3}(?:\D|$))/', '', $norm); // remove pontos de milhar
                $norm = str_replace(',', '.', $norm); // vírgula -> ponto decimal
            } else {
                // Sem vírgula: pode ter pontos de milhar ou decimal
                $parts = explode('.', $norm);
                if(count($parts)>2){
                    $first = array_shift($parts);
                    $rest = implode('', $parts); // interpreta pontos intermediários como milhares
                    $norm = $first.'.'.$rest; // primeiro ponto vira decimal
                } elseif(count($parts)===2) {
                    // Se segunda parte tem 3 dígitos, era milhar => remove ponto
                    if(strlen($parts[1])===3){
                        $norm = implode('', $parts); // inteiro
                    }
                }
            }
            if(preg_match('/^\d+(\.\d+)?$/', $norm)){
                // garante duas casas no valor final usado na validação/format
                if(strpos($norm,'.')===false){
                    $norm = $norm.'.00';
                } else {
                    [$i,$d] = explode('.',$norm,2); $d = substr($d.'00',0,2); $norm = $i.'.'.$d;
                }
                Log::debug('updateChat normalized target', ['field'=>$col, 'normalized'=>$norm]);
                $request->merge([$col => $norm]);
            } else {
                Log::debug('updateChat invalid target', ['field'=>$col, 'after_attempt'=>$norm]);
                $request->merge([$col => null]);
            }
        }

    // Opcional: logging simples (pode comentar depois)
    // \Log::debug('updateChat payload', $request->only(['target_min','target_avg','target_max']));

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'type_id' => 'nullable|integer|exists:openai_chat_types,id',
            'code' => 'nullable|string|min:1|max:5',
            'target_min' => 'nullable|numeric|min:0|max:100000000',
            'target_avg' => 'nullable|numeric|min:0|max:100000000',
            'target_max' => 'nullable|numeric|min:0|max:100000000',
        ]);

        $title = Str::limit(trim(preg_replace('/\s+/', ' ', (string) $validated['title'])), 100, '…');
        $typeIdInput = $request->input('type_id');
        $typeId = ($typeIdInput === null || $typeIdInput === '' ) ? null : (int) $typeIdInput;

        $codeInput = $request->input('code');
    $codeVal = ($codeInput !== null && trim($codeInput) !== '' && Schema::hasColumn('open_a_i_chats','code')) ? Str::upper(substr(trim($codeInput),0,5)) : null;

        $updateData = [
            'title' => $title,
            'type_id' => $typeId,
            'code' => $codeVal,
            'updated_at' => DB::raw('GETDATE()'),
        ];
        // Só adiciona se as colunas existirem (segurança em ambientes desatualizados)
        foreach (['target_min','target_avg','target_max'] as $col) {
            if (Schema::hasColumn('open_a_i_chats', $col)) {
                $val = $validated[$col] ?? null;
                $updateData[$col] = ($val === null || $val === '') ? null : number_format((float)$val, 2, '.', '');
            }
        }

        DB::table('open_a_i_chats')
            ->where('id', $chat->id)
            ->update($updateData);

        return back()->with('success', 'Conversa renomeada com sucesso.');
    }

    /**
     * Exclui uma conversa salva do usuário.
     */
    public function deleteChat(OpenAIChat $chat, Request $request): RedirectResponse
    {
        if ((int) $chat->user_id !== (int) Auth::id()) {
            abort(403);
        }

        DB::table('open_a_i_chats')->where('id', $chat->id)->delete();

        // Se a conversa excluída estiver ativa na sessão, remover ponteiros
        if ((int) $request->session()->get('openai_current_chat_id') === (int) $chat->id) {
            $request->session()->forget('openai_current_chat_id');
        }

        return back()->with('success', 'Conversa excluída.');
    }

    /**
     * Carrega uma conversa salva para a sessão atual.
     */
    public function loadChat(OpenAIChat $chat, Request $request): RedirectResponse
    {
        // Comparação com cast para evitar problemas de tipo
        if ((int) $chat->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $request->session()->put('openai_messages', $chat->messages ?? []);
        $request->session()->put('openai_current_chat_id', (int) $chat->id);

        return redirect()->route('openai.chat')->with('success', 'Conversa carregada.');
    }

    /**
     * Inicia um novo chat limpando histórico e ponteiro da conversa ativa.
     */
    public function newChat(Request $request): RedirectResponse
    {
        $request->session()->forget('openai_messages');
        $request->session()->forget('openai_current_chat_id');
        return redirect()->route('openai.chat');
    }
}
