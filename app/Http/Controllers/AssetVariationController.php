<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetVariation;
use App\Models\OpenAIChat;
use Illuminate\Support\Facades\Auth;

class AssetVariationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:OPENAI - CHAT'])->only('index','store');
    }

    public function index(Request $request)
    {
       
      
        $this->authorize('viewAny', OpenAIChat::class);
        $year = (int)($request->input('year') ?: date('Y'));
        $code = trim($request->input('code',''));
    $sort = $request->input('sort', 'year_desc'); // variation_asc|variation_desc|code_asc|code_desc|created_asc|created_desc|updated_asc|updated_desc|year_asc|year_desc|month_asc|month_desc
        $q = AssetVariation::query();
        // Filtros
        if($year){ $q->where('year',$year); }
        if($code !== ''){ $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
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
            'code'=>$code?:null,
            'sort'=>$sort !== 'year_desc' ? $sort : null,
        ]));
        $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');
        return view('openai.variations.index', compact('variations','years','year','code','sort'));
    }
}
