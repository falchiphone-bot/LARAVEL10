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

public function convertOpusToMp3($inputPath, $outputPath)
{
    $ffmpeg = FFMpeg::create();
    $audio = $ffmpeg->open($inputPath);

    $format = new Mp3();
    $format->setAudioKiloBitrate(192);

    $audio->save($format, $outputPath);
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
                'audio_file' => 'required|file|mimes:opus,mp3,mp4,mpeg,mpga,m4a,wav,webm',
            ], [
                'audio_file.required' => 'Por favor, envie um arquivo de áudio.',
                'audio_file.mimes' => 'O formato do arquivo de áudio não é suportado. Use: opus, mp3, mp4, mpeg, mpga, m4a, wav, webm.',
            ]);

            $file = $request->file('audio_file');

            //salvar audio com extensao antes de enviar
            $extension = $file->getClientOriginalExtension();
            $path = $file->storeAs('audios', uniqid() . '.' . $extension);
            //pegar patch completo
            $fullPath = Storage::path($path);


            $error = null;
            $transcribedText = '';
            $translatedText = '';


            try {
                // 1. Transcribe Spanish audio to Spanish text
                $transcriptionResponse = $this->openAIService->getTranscription($fullPath, 'es');

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

            return view('openai.transcribe', [
                'transcribedText' => $transcribedText,
                'translatedText' => $translatedText,
            ]);
        }

        // For GET requests, just show the view
        return view('openai.transcribe');
    }
}
