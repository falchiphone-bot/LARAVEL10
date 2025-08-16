<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenAI\ApiKeyMissingException;
use App\Exceptions\OpenAI\NetworkException;
use App\Services\OpenAIService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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

    /**
     * Test the getTextResponse method from OpenAIService.
     *
     * @param  Request  $request
     * @return JsonResponse|View|RedirectResponse
     */
    public function testTextResponse(Request $request): JsonResponse|View|RedirectResponse
    {
        $prompt = $request->input('prompt');
        $response = null;
        $error = null;

        if ($prompt) {
            $request->validate([
                'prompt' => 'required|string|min:5',
            ], [
                'prompt.required' => 'O campo de prompt é obrigatório.',
                'prompt.min' => 'O prompt deve ter pelo menos :min caracteres.',
            ]);

            try {
                $response = $this->openAIService->getTextResponse($prompt);

            } catch (ApiKeyMissingException $e) {
                $error = 'A chave da API OpenAI não foi configurada. Verifique seu arquivo .env.';
                Log::error($error, ['exception' => $e]);
                $response = null;
            } catch (NetworkException $e) {
                $error = 'Falha de comunicação com a API da OpenAI. Verifique a conexão e os logs.';
                Log::error($error, ['exception' => $e]);
                $response = null;
            } catch (Exception $e) {
                // Catch any other unexpected exceptions
                $error = 'Ocorreu um erro inesperado: ' . $e->getMessage();
                Log::error($error, ['exception' => $e]);
                $response = null;
            }

            // Handle API-level errors if the request was successful but OpenAI returned an error
            if (isset($response['error'])) {
                $error = $response['error']['message'] ?? 'Erro desconhecido da API OpenAI.';
                Log::error('OpenAI API Error: ' . $error, ['response' => $response]);
                $response = null; // Clear response so it's not sent to the view
            }
        }

        if ($request->wantsJson()) {
            if ($error) {
                return response()->json(['error' => $error], 500);
            }
            return response()->json($response);
        }

        if ($error) {
            return back()->with('error', $error)->withInput();
        }

        return view('openai.test', [
            'prompt' => $prompt,
            'response' => $response,
        ]);
    }

}
