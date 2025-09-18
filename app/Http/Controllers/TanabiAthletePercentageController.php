<?php

namespace App\Http\Controllers;

use App\Models\TanabiAthletePercentage;
use App\Models\FormandoBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\TanabiAthleteOtherClubPercentage;

class TanabiAthletePercentageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:TANABI ATLETAS PERCENTUAIS - LISTAR')->only(['index','exportCsv']);
        $this->middleware('permission:TANABI ATLETAS PERCENTUAIS - CRIAR')->only(['store']);
        $this->middleware('permission:TANABI ATLETAS PERCENTUAIS - EDITAR')->only(['update']);
        $this->middleware('permission:TANABI ATLETAS PERCENTUAIS - EXCLUIR')->only(['destroyOtherClubPercentage']);
        $this->middleware('permission:TANABI ATLETAS PERCENTUAIS - ADICIONAR OUTRO CLUBE')->only(['storeOtherClubPercentage']);
        $this->middleware('permission:TANABI ATLETAS PERCENTUAIS - EXPORTAR')->only(['exportCsv']);
    }

    public function index()
    {
    $items = TanabiAthletePercentage::with(['otherClub','otherClubPercentages.otherClub','athlete'])->orderBy('athlete_name')->get();
        $clubs = \App\Models\SafClube::orderBy('nome')->get(['id','nome']);
        $athletes = FormandoBase::orderBy('nome')->get(['id','nome']);
        // Calcular total row-wise e também soma TANABI / soma outro clube
        $totals = [
            'tanabi_sum' => $items->sum('tanabi_percentage'),
            'other_sum' => $items->sum('other_club_percentage'),
        ];
        return view('tanabi.athletes.percentages.index', [
            'items' => $items,
            'totals' => $totals,
            'clubs' => $clubs,
            'athletes' => $athletes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'formando_base_id' => 'nullable|exists:formandobase,id',
            'athlete_name' => 'nullable|string|max:255',
            'tanabi_percentage' => 'required|numeric|min:0|max:100',
            'other_club_percentage' => 'required|numeric|min:0|max:100',
            'other_club_id' => 'nullable|exists:saf_clubes,id',
            'other_club_name' => 'nullable|string|max:255',
        ]);
        if (!empty($data['formando_base_id'])) {
            $fb = FormandoBase::find($data['formando_base_id']);
            if ($fb) { $data['athlete_name'] = $fb->nome; }
        }
        if (empty($data['athlete_name'])) {
            return back()->withErrors(['athlete_name'=>'Informe o nome do atleta ou selecione um cadastro.'])->withInput();
        }
        if (!empty($data['other_club_id'])) { $data['other_club_name'] = null; }
        $this->enforceSum100IfNeeded($data['tanabi_percentage'], $data['other_club_percentage']);
        TanabiAthletePercentage::create($data);
        return redirect()->route('tanabi.athletes.percentages.index')->with('success','Percentual cadastrado.');
    }

    public function update(Request $request, TanabiAthletePercentage $percentage)
    {
        $data = $request->validate([
            'formando_base_id' => 'nullable|exists:formandobase,id',
            'athlete_name' => 'nullable|string|max:255',
            'tanabi_percentage' => 'required|numeric|min:0|max:100',
            'other_club_percentage' => 'required|numeric|min:0|max:100',
            'other_club_id' => 'nullable|exists:saf_clubes,id',
            'other_club_name' => 'nullable|string|max:255',
        ]);
        if (!empty($data['formando_base_id'])) {
            $fb = FormandoBase::find($data['formando_base_id']);
            if ($fb) { $data['athlete_name'] = $fb->nome; }
        }
        if (empty($data['athlete_name'])) {
            return back()->withErrors(['athlete_name'=>'Informe o nome do atleta ou selecione um cadastro.'])->withInput();
        }
        if (!empty($data['other_club_id'])) { $data['other_club_name'] = null; }
        $this->enforceSum100IfNeeded($data['tanabi_percentage'], $data['other_club_percentage']);
        $percentage->update($data);
        return redirect()->route('tanabi.athletes.percentages.index')->with('success','Percentual atualizado.');
    }

    public function exportCsv(): StreamedResponse
    {
    $items = TanabiAthletePercentage::with('otherClub')->orderBy('athlete_name')->get();
        $callback = function() use ($items){
            $out = fopen('php://output','w');
            fputcsv($out, ['Athlete','TanabiPercentage','OtherClubPercentage','OtherClubName','Total'], ';');
            foreach ($items as $it) {
                $total = (float)$it->tanabi_percentage + (float)$it->other_club_percentage;
                fputcsv($out,[
                    $it->athlete_name,
                    number_format($it->tanabi_percentage,4,'.',''),
                    number_format($it->other_club_percentage,4,'.',''),
                    $it->other_club_name,
                    number_format($total,4,'.',''),
                ], ';');
            }
            fclose($out);
        };
        $filename = 'tanabi_athlete_percentages_'.date('Ymd_His').'.csv';
        return response()->streamDownload($callback, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    protected function enforceSum100IfNeeded($tanabi, $other): void
    {
        if (!Config::get('tanabi.percentages_enforce_100')) { return; }
        $tolerance = (float) Config::get('tanabi.percentages_tolerance', 0.0001);
        $sum = (float)$tanabi + (float)$other;
        if (abs($sum - 100) > $tolerance) {
            abort(422, 'A soma dos percentuais deve ser 100%. Soma atual: '.$sum);
        }
    }

    public function storeOtherClubPercentage(Request $request, TanabiAthletePercentage $percentage)
    {
        $data = $request->validate([
            'other_club_id' => 'nullable|exists:saf_clubes,id',
            'other_club_name' => 'nullable|string|max:255',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);
        if (!empty($data['other_club_id'])) { $data['other_club_name'] = null; }
        $data['tanabi_athlete_percentage_id'] = $percentage->id;
        TanabiAthleteOtherClubPercentage::create($data);
        return back()->with('success','Percentual adicional incluído.');
    }

    public function destroyOtherClubPercentage($id)
    {
        $item = TanabiAthleteOtherClubPercentage::findOrFail($id);
        $item->delete();
        return back()->with('success','Percentual removido.');
    }
}
