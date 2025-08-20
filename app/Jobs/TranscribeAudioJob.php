<?php

namespace App\Jobs;

use App\Services\OpenAIService;
use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranscribeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $inputPath;
    protected string $jobId;
    protected string $language;

    public int $timeout = 3600; // 1h

    public function __construct(string $inputPath, string $jobId, string $language = 'es')
    {
        $this->inputPath = $inputPath;
        $this->jobId = $jobId;
        $this->language = $language;
    }

    public function handle(OpenAIService $openAIService): void
    {
        $resultDir = storage_path('app/audio/results');
        if (!is_dir($resultDir)) {
            @mkdir($resultDir, 0775, true);
        }
        $resultPath = $resultDir . '/' . $this->jobId . '.json';

        $transcribedText = '';
        $translatedText = '';
        $error = null;

        $workingPath = $this->inputPath;
        try {
            // Tenta converter para MP3 mono 16kHz 64kbps; se falhar, segue com o arquivo original
            try {
                $mp3Path = $this->convertToOptimizedMp3($this->inputPath);
                $workingPath = $mp3Path;
            } catch (\Throwable $convEx) {
                Log::warning('Falha na conversão para MP3; prosseguindo com o arquivo original', [
                    'jobId' => $this->jobId,
                    'error' => $convEx->getMessage(),
                ]);
                $workingPath = $this->inputPath;
            }

            // Transcrição
            $transcription = $openAIService->getTranscription($workingPath, $this->language);
            if (isset($transcription['error'])) {
                throw new Exception($transcription['error']['message'] ?? 'Erro desconhecido ao transcrever o áudio.');
            }
            $transcribedText = $transcription['text'] ?? '';
            if (!$transcribedText) {
                throw new Exception('Texto transcrito vazio. O áudio pode estar vazio ou ilegível.');
            }

            // Tradução (es -> pt-br)
            $messages = [
                ['role' => 'system', 'content' => 'Você é um assistente de tradução.'],
                ['role' => 'user', 'content' => "Traduza do espanhol para o português (Brasil):\n\n" . $transcribedText],
            ];
            $translation = $openAIService->getChatResponse($messages);
            if (isset($translation['error'])) {
                throw new Exception($translation['error']['message'] ?? 'Erro desconhecido ao traduzir o texto.');
            }
            $translatedText = $translation['choices'][0]['message']['content'] ?? '';

            $this->writeResult($resultPath, [
                'status' => 'done',
                'transcribedText' => $transcribedText,
                'translatedText' => $translatedText,
            ]);
        } catch (\Throwable $t) {
            $error = $t->getMessage();
            Log::error('TranscribeAudioJob error: ' . $error, [
                'jobId' => $this->jobId,
                'exception' => $t,
            ]);
            $this->writeResult($resultPath, [
                'status' => 'error',
                'error' => $error,
            ]);
        } finally {
            // Limpeza de arquivos temporários
            if (file_exists($workingPath) && $workingPath !== $this->inputPath) {
                @unlink($workingPath);
            }
            if (file_exists($this->inputPath)) {
                @unlink($this->inputPath);
            }
        }
    }

    protected function convertToOptimizedMp3(string $inputPath): string
    {
        // Se já for MP3, evita reprocessar para reduzir risco de falhas e custo
        if (strtolower(pathinfo($inputPath, PATHINFO_EXTENSION)) === 'mp3') {
            return $inputPath;
        }

        $dir = dirname($inputPath);
        $base = pathinfo($inputPath, PATHINFO_FILENAME);
        // Sempre converte para um novo arquivo para evitar "in-place edit" do ffmpeg
        $mp3Path = $dir . '/' . $base . '_conv.mp3';
        // Garante que não colida com o arquivo de entrada nem com arquivo existente
        if (realpath($mp3Path) === realpath($inputPath) || file_exists($mp3Path)) {
            $mp3Path = $dir . '/' . $base . '_conv_' . uniqid() . '.mp3';
        }

        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
                'timeout'          => 3600,
                'ffmpeg.threads'   => 2,
            ]);
            $audio = $ffmpeg->open($inputPath);

            $format = new Mp3();
            $format->setAudioKiloBitrate(64);
            $format->setAudioChannels(1);
            try {
                $audio->filters()->resample(16000);
            } catch (\Throwable $t) {
                Log::warning('Resample(16000) indisponível; prosseguindo', ['error' => $t->getMessage()]);
            }
            $audio->save($format, $mp3Path);
        } catch (\Throwable $e) {
            // Fallback: tentar linha de comando diretamente para obter STDERR e mensagem mais útil
            Log::warning('php-ffmpeg falhou; tentando fallback com ffmpeg CLI', [
                'error' => $e->getMessage(),
                'input' => $inputPath,
                'output' => $mp3Path,
            ]);

            $ffmpegBin = env('FFMPEG_PATH', '/usr/bin/ffmpeg');
            $cmd = sprintf(
                '%s -y -i %s -ac 1 -ar 16000 -b:a 64k %s 2>&1',
                escapeshellcmd($ffmpegBin),
                escapeshellarg($inputPath),
                escapeshellarg($mp3Path)
            );
            $output = [];
            $exitCode = 0;
            @exec($cmd, $output, $exitCode);
            if ($exitCode !== 0 || !file_exists($mp3Path) || filesize($mp3Path) === 0) {
                $stderr = trim(implode("\n", array_slice($output, -30)));
                Log::error('ffmpeg CLI também falhou', [
                    'exitCode' => $exitCode,
                    'stderr_tail' => $stderr,
                ]);
                throw new Exception('Encoding failed: ' . ($stderr ?: 'ffmpeg error'));
            }
        }

        return $mp3Path;
    }

    protected function writeResult(string $path, array $payload): void
    {
        @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
