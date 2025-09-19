<?php

namespace App\Console\Commands;

use App\Jobs\TranscodeEnvioVideo;
use App\Models\EnvioArquivo;
use Illuminate\Console\Command;

class TranscodeEnviosBatch extends Command
{
    protected $signature = 'envios:transcode {--status=pending : Status para filtrar (pending,failed,all)} {--limit=50 : Limite de itens para agendar}';
    protected $description = 'Agenda transcodificação de vídeos (MP4/HLS) para arquivos de Envios por lote';

    public function handle(): int
    {
        $status = $this->option('status');
        $limit = (int)$this->option('limit');

        $query = EnvioArquivo::query()
            ->where(function($q){
                $q->where('mime_type','like','video/%')->orWhereNull('mime_type');
            })
            ->whereNull('hls_path')
            ->whereNull('mp4_path');

        if ($status === 'pending') {
            $query->where(function($q){ $q->whereNull('transcode_status')->orWhere('transcode_status','pending'); });
        } elseif ($status === 'failed') {
            $query->where('transcode_status','failed');
        }

        $total = $query->count();
        $this->info("Encontrados {$total} itens elegíveis. Agendando até {$limit}...");

        $ids = $query->limit($limit)->pluck('id')->all();
        foreach ($ids as $id) {
            TranscodeEnvioVideo::dispatch($id)->onQueue('default');
        }
        $this->info('Jobs agendados: '.count($ids));
        return self::SUCCESS;
    }
}
