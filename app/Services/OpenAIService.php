<?php

namespace App\Services;

use App\Exceptions\OpenAI\ApiKeyMissingException;
use App\Exceptions\OpenAI\NetworkException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OpenAIService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $baseUrl = 'https://api.openai.com/v1/';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
        ]);

        // It's best practice to store API keys in config files, which in turn read from .env
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
    }

    /**
     * Get a response from OpenAI's vision model.
     *
     * @param string $prompt The text prompt.
     * @param string $imageUrl The URL of the image to analyze.
     * @param string $model The model to use.
     * @param int $maxTokens The maximum number of tokens to generate.
     * @return array|null The API response or null on failure.
     */
    public function getVisionResponse(string $prompt, string $imageUrl, string $model = 'gpt-4-vision-preview', int $maxTokens = 300): array
    {
        if (!$this->apiKey) {
            throw new ApiKeyMissingException('OpenAI API key is not set.');
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => [['type' => 'text', 'text' => $prompt], ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]]]],
            ],
            'max_tokens' => $maxTokens,
        ];

        try {
            $response = $this->client->post('chat/completions', ['headers' => ['Authorization' => 'Bearer ' . $this->apiKey, 'Content-Type' => 'application/json'], 'json' => $payload]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new NetworkException('OpenAI API request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get a text response from an OpenAI model.
     *
     * @param string $prompt The text prompt.
     * @param string $model The model to use.
     * @param int $maxTokens The maximum number of tokens to generate.
     * @return array|null The API response or null on failure.
     */
    public function getTextResponse(string $prompt, string $model = 'gpt-3.5-turbo', int $maxTokens = 300): array
    {
        if (!$this->apiKey) {
            throw new ApiKeyMissingException('OpenAI API key is not set.');
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $maxTokens,
        ];

        try {
            $response = $this->client->post('chat/completions', ['headers' => ['Authorization' => 'Bearer ' . $this->apiKey, 'Content-Type' => 'application/json'], 'json' => $payload]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new NetworkException('OpenAI API request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
