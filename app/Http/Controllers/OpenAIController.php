<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\OpenAI\NetworkException;
use App\Exceptions\OpenAI\ApiKeyMissingException;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use App\Jobs\TranscribeAudioJob;
use App\Models\OpenAIChat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OpenAIController extends Controller
{
    protected OpenAIService $openAIService;

    /**
     * @param OpenAIService $openAIService
     */


    public function __construct(OpenAIService $openAIService)
    {
        $this->middleware('auth');
        $this->middleware(['permission:OPENAI - CHAT'])->only('chat', 'chats', 'saveChat', 'loadChat', 'updateChat', 'deleteChat', 'newChat');
        $this->middleware(['permission:OPENAI - TRANSCRIBE - ESPANHOL'])->only('transcribe');
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
        if (class_exists(\FFMpeg\FFMpeg::class)) {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
                'timeout'          => 3600, // segundos
                'ffmpeg.threads'   => 2,
            ]);
            $audio = $ffmpeg->open($inputPath);

            $format = new Mp3();
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
    $exit = 0;
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

            // Prepara mensagens para envio à API (com possível contexto de conversas salvas)
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
                        $q = DB::table('open_a_i_chats')->select('title', 'messages', 'updated_at');
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
                            $text = collect($tail)->map(function ($m) {
                                $who = $m['role'] === 'user' ? 'Você' : 'Assistente';
                                return $who . ': ' . (string) ($m['content'] ?? '');
                            })->implode("\n");
                            if ($text) {
                                $snippets[] = '- ' . ($hit->title ?? 'Conversa') . " (" . (string) $hit->updated_at . ")\n" . $text;
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
        return view('openai.chat', [ // Sugiro renomear a view para 'chat.blade.php'
            'messages' => $messages,
            'searchInChats' => $searchInChatsPref,
            'searchScope' => $searchScopePref,
        ]);
    }

    /**
     * Clear the chat history from the session.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function clearChat(Request $request): RedirectResponse
    {
        $request->session()->forget('openai_messages');
        $request->session()->forget('openai_current_chat_id');

        return redirect()->route('openai.chat');
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
    public function chats(): View
    {
        $chats = OpenAIChat::where('user_id', Auth::id())
            ->latest('updated_at')
            ->paginate(12);

        return view('openai.chats', compact('chats'));
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
        ]);

        $rawTitle = $request->input('title');
        $titleNormalized = null;
        if ($rawTitle !== null && trim($rawTitle) !== '') {
            $titleNormalized = Str::limit(trim(preg_replace('/\s+/', ' ', (string) $rawTitle)), 100, '…');
        }

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

        $newId = DB::table('open_a_i_chats')->insertGetId([
            'user_id'  => Auth::id(),
            'title'    => $title,
            'messages' => json_encode($messages, JSON_UNESCAPED_UNICODE),
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

        $validated = $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $title = Str::limit(trim(preg_replace('/\s+/', ' ', (string) $validated['title'])), 100, '…');

        DB::table('open_a_i_chats')
            ->where('id', $chat->id)
            ->update([
                'title' => $title,
                'updated_at' => DB::raw('GETDATE()'),
            ]);

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
