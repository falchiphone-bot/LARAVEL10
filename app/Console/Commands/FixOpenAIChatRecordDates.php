<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OpenAIChatRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FixOpenAIChatRecordDates extends Command
{
    protected $signature = 'openai:fix-record-dates
        {--dry-run : Apenas mostra o que seria alterado}
        {--limit=0 : Limitar quantidade de registros processados}
        {--force-all : Aplica sem pedir confirmação em registros elegíveis}
        {--after= : Converter somente registros com occurred_at >= data (YYYY-MM-DD)}
        {--before= : Converter somente registros com occurred_at <= data (YYYY-MM-DD)}';
    protected $description = 'Tenta corrigir datas invertidas (dia/mês trocados) em openai_chat_records.';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $limit = (int)$this->option('limit');
        $forceAll = $this->option('force-all');
        $after = $this->option('after');
        $before = $this->option('before');

        $query = OpenAIChatRecord::query()->orderBy('id');
        $afterDt = null; $beforeDt = null;
        if ($after) {
            try { $afterDt = Carbon::createFromFormat('Y-m-d', $after)->startOfDay(); } catch (\Exception $e) { $this->error('Formato inválido para --after (use YYYY-MM-DD)'); return Command::FAILURE; }
        }
        if ($before) {
            try { $beforeDt = Carbon::createFromFormat('Y-m-d', $before)->endOfDay(); } catch (\Exception $e) { $this->error('Formato inválido para --before (use YYYY-MM-DD)'); return Command::FAILURE; }
        }
        if ($afterDt && $beforeDt) {
            $query->whereBetween('occurred_at', [$afterDt, $beforeDt]);
        } elseif ($afterDt) {
            $query->where('occurred_at', '>=', $afterDt);
        } elseif ($beforeDt) {
            $query->where('occurred_at', '<=', $beforeDt);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }
        $total = $query->count();
        if ($total === 0) {
            $this->warn('Nenhum registro encontrado para os filtros informados.');
            return Command::SUCCESS;
        }
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        $changed = 0; $checked = 0;

    $query->chunkById(500, function($chunk) use (&$changed, &$checked, $dry, $bar, $forceAll) {
            foreach ($chunk as $rec) {
                $checked++;
                $dt = $rec->occurred_at; // Carbon
                if (!$dt) { $bar->advance(); continue; }

                // Heurística: se mês > 12 não existe; precisamos detectar se o que está salvo parece invertido.
                // Como a data já está em datetime válido no banco, usamos um critério heurístico: se dia <= 12 e mês <= 12 não sabemos.
                // Estratégia: Armazenamos antes da correção uma flag manual? Não existe. Então adotamos regra opt-in: se a data está no futuro muito distante ou passado incoerente.
                // Simples: Se o dia (d) <= 12 e mês (m) > 12 nunca ocorre; inversão típica ocorre quando d<=12.
                // Sem track original é arriscado; Perguntaremos confirmação interativa para cada candidata.
                $day = (int)$dt->format('d');
                $month = (int)$dt->format('m');

                // Caso típico de inversão original: usuário queria DD/MM mas foi salvo como MM/DD. Só detectável quando day <= 12 e month > 12 no desejado, mas isso não acontece.
                // Melhor: pedir intervalo alvo do usuário (ex: converter todos registros onde month <=12 e day <=12). Para simplificar, marcar todos onde day <= 12.
                if ($day <= 12 && $month <= 12 && $day !== $month) { // candidatos ambíguos (evita dd==mm)
                    $proposed = Carbon::createFromFormat('d/m/Y H:i:s', sprintf('%02d/%02d/%s %s', $month, $day, $dt->format('Y'), $dt->format('H:i:s')));
                    if ((int)$proposed->format('m') > 12) { // segurança extra
                        $bar->advance();
                        continue;
                    }
                    $this->line("\nRegistro #{$rec->id}: atual={$dt->format('d/m/Y H:i:s')} -> proposto={$proposed->format('d/m/Y H:i:s')}");
                    if ($dry) {
                        // somente exibe
                    } else {
                        $apply = $forceAll ? true : $this->confirm('Aplicar conversão?', true);
                        if ($apply) {
                            $rec->occurred_at = $proposed;
                            $rec->save();
                            $changed++;
                        }
                    }
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Registros verificados: $checked");
        $this->info("Registros alterados: $changed");
    if ($dry) $this->warn('Execução em modo dry-run (nada alterado).');
    if ($forceAll) $this->info('Conversão automática aplicada (--force-all).');
        return Command::SUCCESS;
    }
}
