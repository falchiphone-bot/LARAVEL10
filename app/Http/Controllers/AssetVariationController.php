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
        $q = AssetVariation::query()->orderBy('year','desc')->orderBy('month','desc');
        if($year){ $q->where('year',$year); }
        if($code !== ''){ $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
        $variations = $q->paginate(30)->appends(array_filter([
            'year'=>$request->input('year'),
            'code'=>$code?:null,
        ]));
        $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');
        return view('openai.variations.index', compact('variations','years','year','code'));
    }
}
