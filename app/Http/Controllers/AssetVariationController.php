<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetVariation;
use App\Models\OpenAIChat;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetVariationsExport;
use Illuminate\Support\Facades\DB;

class AssetVariationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:OPENAI - CHAT'])->only('index','store','exportCsv','exportXlsx','batchFlags');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $year = (int)($request->input('year') ?: date('Y'));
        $monthInput = $request->input('month');
        $month = is_numeric($monthInput) ? (int)$monthInput : 0; // 1..12
        $code = trim($request->input('code',''));
        $polarity = $request->input('polarity'); // positive | negative
        $change = $request->input('change'); // melhoria|piora|igual|null
        $grouped = (bool)$request->boolean('grouped');
        $sparkWindow = (int)($request->input('spark_window') ?: 6);
        if($sparkWindow < 3) $sparkWindow = 3; if($sparkWindow > 24) $sparkWindow = 24;
    $sort = $request->input('sort', 'year_desc'); // adiciona diff_asc|diff_desc|prev_asc|prev_desc

        // Se modo agrupado: construímos dataset agregado e retornamos sem paginação convencional.
        if ($grouped) {
            $base = AssetVariation::query();
            if($code !== ''){ $base->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
            if ($polarity === 'positive') { $base->where('variation', '>', 0); }
            elseif ($polarity === 'negative') { $base->where('variation', '<', 0); }
            // Limitamos número de chats para evitar carga alta; pode ser ajustado futuramente.
            $chatIds = $base->select('chat_id')
                ->whereNotNull('chat_id')
                ->distinct()
                ->limit(300)
                ->pluck('chat_id');
            $seriesRows = AssetVariation::with('chat')
                ->whereIn('chat_id', $chatIds)
                ->orderBy('year','desc')->orderBy('month','desc')
                ->get();
            // Agrupar por chat_id
            $groupedData = [];
            foreach($seriesRows as $row){
                $cid = $row->chat_id;
                if(!isset($groupedData[$cid])){
                    $groupedData[$cid] = [
                        'chat_id' => $cid,
                        'asset_code' => $row->asset_code,
                        'chat_title' => optional($row->chat)->title,
                        'rows' => [],
                    ];
                }
                if (count($groupedData[$cid]['rows']) < $sparkWindow) {
                    $groupedData[$cid]['rows'][] = $row; // armazenar em ordem desc por enquanto
                }
            }
            // Processar cada grupo: ordenar cronologicamente asc para sparkline.
            foreach($groupedData as &$g){
                usort($g['rows'], function($a,$b){
                    if($a->year === $b->year) return $a->month <=> $b->month;
                    return $a->year < $b->year ? -1 : 1;
                });
                $latest = end($g['rows']);
                $prevYear = $latest->month == 1 ? ($latest->year - 1) : $latest->year;
                $prevMonth = $latest->month == 1 ? 12 : ($latest->month - 1);
                $prev = null;
                foreach($g['rows'] as $rPrev){
                    if($rPrev->year == $prevYear && $rPrev->month == $prevMonth){
                        $prev = $rPrev->variation; break;
                    }
                }
                $g['latest'] = $latest;
                $g['prev_variation'] = $prev;
                $g['diff'] = (!is_null($prev)) ? ($latest->variation - $prev) : null;
                // Filtragem por change no modo agrupado (baseada em diff)
                $ok = true;
                if($change === 'melhoria'){ $ok = !is_null($g['diff']) && $g['diff'] > 0; }
                elseif($change === 'piora'){ $ok = !is_null($g['diff']) && $g['diff'] < 0; }
                elseif($change === 'igual'){ $ok = !is_null($g['diff']) && abs($g['diff']) < 1e-12; }
                if(!$ok){ $g['__discard'] = true; }
            }
            unset($g);
            $groupedData = array_filter($groupedData, fn($g)=>empty($g['__discard']));
            // Ordenação básica em agrupado: por código asc; se sort diff_asc/diff_desc aplicar.
            if($sort === 'diff_asc'){
                usort($groupedData, fn($a,$b)=> ($a['diff'] <=> $b['diff']));
            } elseif($sort === 'diff_desc'){
                usort($groupedData, fn($a,$b)=> ($b['diff'] <=> $a['diff']));
            } elseif($sort === 'prev_asc') {
                usort($groupedData, function($a,$b){
                    $av = $a['prev_variation']; $bv = $b['prev_variation'];
                    if($av === null && $bv === null) return 0;
                    if($av === null) return 1; // nulls last
                    if($bv === null) return -1;
                    return $av <=> $bv;
                });
            } elseif($sort === 'prev_desc') {
                usort($groupedData, function($a,$b){
                    $av = $a['prev_variation']; $bv = $b['prev_variation'];
                    if($av === null && $bv === null) return 0;
                    if($av === null) return 1;
                    if($bv === null) return -1;
                    return $bv <=> $av;
                });
            } else {
                usort($groupedData, function($a,$b){
                    return strcmp(strtoupper($a['asset_code']??''), strtoupper($b['asset_code']??''));
                });
            }
            $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');
            // Variáveis compatíveis com view (variations vazio)
            $variations = collect();
            $prevVariationMap = [];
            return view('openai.variations.index', [
                'variations'=>$variations,
                'years'=>$years,
                'year'=>$year,
                'month'=>$month,
                'code'=>$code,
                'sort'=>$sort,
                'polarity'=>$polarity,
                'prevVariationMap'=>$prevVariationMap,
                'change'=>$change,
                'grouped'=>$grouped,
                'sparkWindow'=>$sparkWindow,
                'groupedData'=>$groupedData,
            ]);
        }

        // Modo não agrupado: usar subquery para prev_variation permitindo filtros de mudança e ordenação por diff.
        $q = AssetVariation::with('chat');
        if($year){ $q->where('year',$year); }
        if($month >= 1 && $month <= 12){ $q->where('month', $month); }
        if($code !== ''){ $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
        if ($polarity === 'positive') { $q->where('variation', '>', 0); }
        elseif ($polarity === 'negative') { $q->where('variation', '<', 0); }

        // Subquery compatível com diferentes drivers (MySQL/Postgres usam LIMIT, SQL Server usa TOP)
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            $subSql = "select top 1 av2.variation from asset_variations av2\n                where av2.chat_id = asset_variations.chat_id\n                  and ( (asset_variations.month > 1 AND av2.year = asset_variations.year AND av2.month = asset_variations.month - 1)\n                        OR (asset_variations.month = 1 AND av2.year = asset_variations.year - 1 AND av2.month = 12) )";
        } else {
            $subSql = "select av2.variation from asset_variations av2\n                where av2.chat_id = asset_variations.chat_id\n                  and ( (asset_variations.month > 1 AND av2.year = asset_variations.year AND av2.month = asset_variations.month - 1)\n                        OR (asset_variations.month = 1 AND av2.year = asset_variations.year - 1 AND av2.month = 12) )\n                limit 1";
        }
    $q->select('asset_variations.*');
    // selectSub exige string, closure ou builder; passamos a string diretamente
    $q->selectSub($subSql, 'prev_variation');

        // Filtro por mudança (melhoria/piora/igual)
        if($change === 'melhoria'){
            $q->whereRaw('(variation > (' . $subSql . '))');
        } elseif($change === 'piora'){
            $q->whereRaw('(variation < (' . $subSql . '))');
        } elseif($change === 'igual'){
            $q->whereRaw('((' . $subSql . ') IS NOT NULL AND ABS(variation - (' . $subSql . ')) < 1e-12)');
        }

        // Ordenação
        switch($sort){
            case 'variation_asc':
                $q->orderBy('variation','asc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'variation_desc':
                $q->orderBy('variation','desc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'diff_asc':
                $q->orderByRaw('(variation - COALESCE((' . $subSql . '), 0)) asc');
                break;
            case 'diff_desc':
                $q->orderByRaw('(variation - COALESCE((' . $subSql . '), 0)) desc');
                break;
            case 'prev_asc':
                // Ordena por prev_variation (nulls last)
                $q->orderByRaw('CASE WHEN (' . $subSql . ') IS NULL THEN 1 ELSE 0 END asc');
                $q->orderByRaw('(' . $subSql . ') asc');
                break;
            case 'prev_desc':
                $q->orderByRaw('CASE WHEN (' . $subSql . ') IS NULL THEN 1 ELSE 0 END asc');
                $q->orderByRaw('(' . $subSql . ') desc');
                break;
            case 'code_asc':
                $q->orderBy('asset_code','asc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'code_desc':
                $q->orderBy('asset_code','desc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'created_asc':
                $q->orderBy('created_at','asc');
                break;
            case 'created_desc':
                $q->orderBy('created_at','desc');
                break;
            case 'updated_asc':
                $q->orderBy('updated_at','asc');
                break;
            case 'updated_desc':
                $q->orderBy('updated_at','desc');
                break;
            case 'year_asc':
                $q->orderBy('year','asc')->orderBy('month','asc');
                break;
            case 'year_desc':
                $q->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'month_asc':
                $q->orderBy('month','asc')->orderBy('year','desc');
                break;
            case 'month_desc':
                $q->orderBy('month','desc')->orderBy('year','desc');
                break;
            case 'year_desc':
            default:
                $q->orderBy('year','desc')->orderBy('month','desc');
        }

        $variations = $q->paginate(30)->appends(array_filter([
            'year'=>$request->input('year'),
            'month'=> ($month >= 1 && $month <= 12) ? $month : null,
            'code'=>$code?:null,
            'polarity' => in_array($polarity, ['positive','negative']) ? $polarity : null,
            'sort'=>$sort !== 'year_desc' ? $sort : null,
            'change'=> in_array($change, ['melhoria','piora','igual']) ? $change : null,
        ]));

        // Map prevVariationMap a partir da coluna prev_variation já selecionada
        $prevVariationMap = [];
        foreach($variations as $row){
            $prevVariationMap[$row->id] = $row->prev_variation !== null ? (float)$row->prev_variation : null;
        }
        $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');
        return view('openai.variations.index', [
            'variations'=>$variations,
            'years'=>$years,
            'year'=>$year,
            'month'=>$month,
            'code'=>$code,
            'sort'=>$sort,
            'polarity'=>$polarity,
            'prevVariationMap'=>$prevVariationMap,
            'change'=>$change,
            'grouped'=>false,
            'sparkWindow'=>$sparkWindow,
            'groupedData'=>[],
        ]);
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $filters = [
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'code' => $request->input('code'),
            'polarity' => $request->input('polarity'),
        ];
        $sort = $request->input('sort', 'year_desc');
        $export = new AssetVariationsExport($filters, $sort);
        return Excel::download($export, 'openai-asset-variations.csv', \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportXlsx(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $filters = [
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'code' => $request->input('code'),
            'polarity' => $request->input('polarity'),
        ];
        $sort = $request->input('sort', 'year_desc');
        $export = new AssetVariationsExport($filters, $sort);
        return Excel::download($export, 'openai-asset-variations.xlsx');
    }

    /**
     * Aplica em lote as flags COMPRAR/NÃO COMPRAR por código com base no sinal da variação (%).
     * Considera os filtros atuais (ano, mês, código, polaridade). Para cada código, usa a linha
     * mais recente dentro do conjunto filtrado (maior ano, depois mês) como referência.
     */
    public function batchFlags(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $year = (int) ($request->input('year') ?: 0);
        $monthInput = $request->input('month');
        $month = is_numeric($monthInput) ? (int)$monthInput : 0;
        $code = trim((string)$request->input('code', ''));
        $polarity = (string)$request->input('polarity', ''); // positive|negative|''

        $q = AssetVariation::query();
        if ($year) { $q->where('year', $year); }
        if ($month >= 1 && $month <= 12) { $q->where('month', $month); }
        if ($code !== '') { $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
        if ($polarity === 'positive') { $q->where('variation', '>', 0); }
        elseif ($polarity === 'negative') { $q->where('variation', '<', 0); }

        // Buscar apenas colunas necessárias
        $rows = $q->get(['asset_code','year','month','variation']);
        if ($rows->isEmpty()) {
            return back()->with('info', 'Nada a aplicar: nenhum item encontrado com os filtros atuais.');
        }

        // Escolher por código a linha mais recente (maior ano, depois mês)
        $byCode = [];
        foreach ($rows as $r) {
            $codeKey = strtoupper(trim((string)$r->asset_code));
            if ($codeKey === '') { continue; }
            $key = sprintf('%04d%02d', (int)$r->year, (int)$r->month);
            if (!isset($byCode[$codeKey]) || $key > $byCode[$codeKey]['key']) {
                $byCode[$codeKey] = ['key' => $key, 'variation' => (float)$r->variation];
            }
        }

        $userId = (int) auth()->id();
        $buy = 0; $noBuy = 0; $skipped = 0;
        foreach ($byCode as $codeKey => $info) {
            $v = (float) $info['variation'];
            if ($v > 0) {
                \App\Models\UserAssetFlag::updateOrCreate(
                    ['user_id' => $userId, 'code' => $codeKey],
                    ['no_buy' => 0]
                );
                $buy++;
            } elseif ($v < 0) {
                \App\Models\UserAssetFlag::updateOrCreate(
                    ['user_id' => $userId, 'code' => $codeKey],
                    ['no_buy' => 1]
                );
                $noBuy++;
            } else {
                $skipped++;
            }
        }

        $msg = sprintf('Flags aplicadas: %d COMPRAR, %d NÃO COMPRAR. Ignorados: %d.', $buy, $noBuy, $skipped);
        return back()->with('success', $msg);
    }
}
