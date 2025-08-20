<?php
require "/var/www/vendor/autoload.php";
use Illuminate\Foundation\Application;
use App\Jobs\TranscribeAudioJob;
use App\Services\OpenAIService;
$app = new Application("/var/www");
$app->instance("path.storage", "/var/www/storage");
$input = "/var/www/tmp/test.opus";
$jobId = "job_test_" . str_replace([" ", "."], "_", microtime(true));
$job = new TranscribeAudioJob($input, $jobId, "es");
$stub = new class extends OpenAIService {
    public function __construct() {}
    public function getTranscription(string $audioFilePath, string $language = "es", string $model = "whisper-1"): array { return ["text" => "hola mundo de prueba"]; }
    public function getChatResponse(array $messages, string $model = "gpt-3.5-turbo", int $maxTokens = 1024): array { return ["choices" => [["message" => ["content" => "olá mundo de teste"]]]]; }
};
$job->handle($stub);
echo $jobId, "\n";
