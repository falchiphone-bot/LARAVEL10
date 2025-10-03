<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetVariation;
use App\Models\OpenAIChat;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetVariationsExport;

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
        $polarity = $request->input('polarity'); // positive | negative | null
    $sort = $request->input('sort', 'year_desc'); // variation_asc|variation_desc|code_asc|code_desc|created_asc|created_desc|updated_asc|updated_desc|year_asc|year_desc|month_asc|month_desc
        $q = AssetVariation::query();
        // Filtros
        if($year){ $q->where('year',$year); }
        if($month >= 1 && $month <= 12){ $q->where('month', $month); }
        if($code !== ''){ $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
        if ($polarity === 'positive') {
            $q->where('variation', '>', 0);
        } elseif ($polarity === 'negative') {
            $q->where('variation', '<', 0);
        }
        // Ordenação
        switch($sort){
            case 'variation_asc':
                $q->orderBy('variation','asc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'variation_desc':
                $q->orderBy('variation','desc')->orderBy('year','desc')->orderBy('month','desc');
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
        ]));
        $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');
        return view('openai.variations.index', compact('variations','years','year','month','code','sort','polarity'));
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
