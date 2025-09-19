<?php

namespace App\Jobs;

use App\Models\EnvioArquivo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TranscodeEnvioVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 60 min
    public int $tries = 1;

    public function __construct(public int $envioArquivoId) {}

    public function handle(): void
    {
        $arq = EnvioArquivo::find($this->envioArquivoId);
        if (!$arq) { return; }

        // Marca como processing
    $arq->update(['transcode_status' => 'processing', 'transcode_error' => null, 'last_transcode_at' => now()]);

        $stored = (string)$arq->path;
        // Resolve caminho absoluto da origem
        $root = base_path();
        $relative = ltrim($stored, '/');
        if (Str::startsWith($relative, 'public/')) { $relative = Str::after($relative, 'public/'); }
        if (Str::startsWith($relative, 'storage/')) { $relative = Str::after($relative, 'storage/'); }
        $inputAbs = storage_path('app/public/'.$relative);
        if (!file_exists($inputAbs)) {
            $inputAbs = storage_path('app/'.$relative);
        }
        if (!file_exists($inputAbs)) {
            throw new \RuntimeException('Arquivo de entrada não encontrado: '.$stored);
        }

        // Saídas (em public/ para servir via /storage)
        $baseDir = 'envios_transcoded/'.date('Y/m');
        $filename = pathinfo($arq->original_name ?: basename($stored), PATHINFO_FILENAME);
        $safeName = Str::slug($filename);

        $mp4Rel = $baseDir.'/'.$safeName.'.mp4';
    $hlsDirRel = $baseDir.'/'.$safeName.'_hls';
    $hlsIndexRel = $hlsDirRel.'/master.m3u8';

        $mp4Abs = storage_path('app/public/'.$mp4Rel);
        $hlsDirAbs = storage_path('app/public/'.$hlsDirRel);
        $hlsIndexAbs = storage_path('app/public/'.$hlsIndexRel);

        @mkdir(dirname($mp4Abs), 0775, true);
        @mkdir($hlsDirAbs, 0775, true);

        // Comandos ffmpeg para MP4 (H.264+AAC) e HLS (720p baseline para compatibilidade)
        // MP4
        $cmdMp4 = sprintf(
            'ffmpeg -y -i %s -c:v libx264 -preset veryfast -crf 23 -movflags +faststart -pix_fmt yuv420p -c:a aac -b:a 128k %s 2>&1',
            escapeshellarg($inputAbs),
            escapeshellarg($mp4Abs)
        );
        // HLS multi-bitrate (240p/360p/480p/720p) com master playlist
        $hls240 = $hlsDirAbs.'/240p.m3u8';
        $hls360 = $hlsDirAbs.'/360p.m3u8';
        $hls480 = $hlsDirAbs.'/480p.m3u8';
        $hls720 = $hlsDirAbs.'/720p.m3u8';

        $cmdHls = '('
            .sprintf('ffmpeg -y -i %s -vf scale=-2:240 -c:v libx264 -preset veryfast -crf 25 -pix_fmt yuv420p -c:a aac -b:a 96k -hls_time 6 -hls_playlist_type vod -hls_segment_filename %s/seg240p_%%03d.ts %s',
                escapeshellarg($inputAbs), escapeshellarg($hlsDirAbs), escapeshellarg($hls240))
            .') && ('
            .sprintf('ffmpeg -y -i %s -vf scale=-2:360 -c:v libx264 -preset veryfast -crf 24 -pix_fmt yuv420p -c:a aac -b:a 96k -hls_time 6 -hls_playlist_type vod -hls_segment_filename %s/seg360p_%%03d.ts %s',
                escapeshellarg($inputAbs), escapeshellarg($hlsDirAbs), escapeshellarg($hls360))
            .') && ('
            .sprintf('ffmpeg -y -i %s -vf scale=-2:480 -c:v libx264 -preset veryfast -crf 23 -pix_fmt yuv420p -c:a aac -b:a 128k -hls_time 6 -hls_playlist_type vod -hls_segment_filename %s/seg480p_%%03d.ts %s',
                escapeshellarg($inputAbs), escapeshellarg($hlsDirAbs), escapeshellarg($hls480))
            .') && ('
            .sprintf('ffmpeg -y -i %s -vf scale=-2:720 -c:v libx264 -preset veryfast -crf 22 -pix_fmt yuv420p -c:a aac -b:a 128k -hls_time 6 -hls_playlist_type vod -hls_segment_filename %s/seg720p_%%03d.ts %s',
                escapeshellarg($inputAbs), escapeshellarg($hlsDirAbs), escapeshellarg($hls720))
            .') && ('
            .sprintf('bash -lc %s', escapeshellarg(
                'cat > '.escapeshellarg($hlsIndexAbs).
                ' <<EOF
#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=500000,RESOLUTION=426x240
240p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=800000,RESOLUTION=640x360
360p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=1400000,RESOLUTION=854x480
480p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=2800000,RESOLUTION=1280x720
720p.m3u8
EOF'
            ))
            .') 2>&1';

        // Executa
        $out1 = $this->run($cmdMp4);
        $out2 = $this->run($cmdHls);

        if (!file_exists($mp4Abs) || !file_exists($hlsIndexAbs)) {
            $arq->update([
                'transcode_status' => 'failed',
                'transcode_error' => substr(($out1."\n\n".$out2), 0, 2000),
                'last_transcode_at' => now(),
            ]);
            return;
        }

        // Atualiza paths
        $arq->update([
            'mp4_path' => 'public/'.$mp4Rel,
            'hls_path' => 'public/'.$hlsIndexRel,
            'transcode_status' => 'done',
            'transcode_error' => null,
            'mime_type' => $arq->mime_type ?: 'video/mp4',
            'last_transcode_at' => now(),
        ]);
    }

    private function run(string $cmd): string
    {
        Log::info('[TranscodeEnvioVideo] Executando', ['cmd' => $cmd]);
        $out = [];
        $code = 0;
        exec($cmd, $out, $code);
        $txt = implode("\n", $out);
        Log::info('[TranscodeEnvioVideo] Saída', ['code' => $code]);
        return $txt;
    }
}
