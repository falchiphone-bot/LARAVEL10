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

class OpenAIController extends Controller
{
    protected OpenAIService $openAIService;

    /**
     * @param OpenAIService $openAIService
     */
    public function __construct(OpenAIService $openAIService)
    {
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

            try {
                // Assumindo que o serviço agora tem um método que aceita o histórico completo
                $response = $this->openAIService->getChatResponse($messages);

                // Handle API-level errors
                if (isset($response['error'])) {
                    $error = $response['error']['message'] ?? 'Erro desconhecido da API OpenAI.';
                    Log::error('OpenAI API Error: ' . $error, ['response' => $response]);
                } else {
                    // Adiciona a resposta do assistente ao histórico
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
        return view('openai.chat', [ // Sugiro renomear a view para 'chat.blade.php'
            'messages' => $messages,
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
}
