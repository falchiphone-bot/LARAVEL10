<?php

namespace App\Http\Controllers;

use App\Http\Requests\SafFaixaSalarialRequest;
use App\Models\SafFaixaSalarial;
use App\Models\FuncaoProfissional;
use App\Models\SafTipoPrestador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SafFaixasSalariaisExport;
use App\Imports\SafFaixasSalariaisImport;
use Dompdf\Dompdf;
use Dompdf\Options;

class SafFaixaSalarialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:SAF_FAIXASSALARIAIS - LISTAR'])->only(['index']);
        $this->middleware(['permission:SAF_FAIXASSALARIAIS - EXPORTAR'])->only(['export','exportPdf']);
        $this->middleware(['permission:SAF_FAIXASSALARIAIS - INCLUIR'])->only(['create','store','import','duplicate','importTemplate']);
        $this->middleware(['permission:SAF_FAIXASSALARIAIS - EDITAR'])->only(['edit','update']);
        $this->middleware(['permission:SAF_FAIXASSALARIAIS - VER'])->only(['show']);
        $this->middleware(['permission:SAF_FAIXASSALARIAIS - EXCLUIR'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $allowedSorts = ['nome','valor_minimo','valor_maximo','vigencia_inicio','vigencia_fim','funcao','senioridade'];
        $sort = $request->query('sort', 'vigencia_inicio');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'vigencia_inicio'; }
        $dir = strtolower($request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $defaultPerPage = (int) ($request->session()->get('saf_faixas_salariais.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        $perPage = max(5, min(100, $perPage));
        $request->session()->put('saf_faixas_salariais.per_page', $perPage);

        $q = trim((string) $request->query('q', ''));
        $funcaoId = $request->query('funcao_profissional_id');
        $tipoPrestadorId = $request->query('saf_tipo_prestador_id');
        $senioridade = $request->query('senioridade');
        $tipoContrato = $request->query('tipo_contrato');
        $moeda = $request->query('moeda');
        $vigentes = $request->boolean('somente_vigentes', false);
    $dataCorte = $request->query('data_corte'); // opcional: Y-m-d

        $query = SafFaixaSalarial::query()->with(['funcaoProfissional','tipoPrestador']);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('observacoes', 'like', "%{$q}%");
            });
        }
        if (!empty($funcaoId)) { $query->where('funcao_profissional_id', $funcaoId); }
        if (!empty($tipoPrestadorId)) { $query->where('saf_tipo_prestador_id', $tipoPrestadorId); }
        if (!empty($senioridade)) { $query->where('senioridade', $senioridade); }
        if (!empty($tipoContrato)) { $query->where('tipo_contrato', $tipoContrato); }
        if (!empty($moeda)) { $query->where('moeda', strtoupper($moeda)); }
        if ($vigentes) {
            $data = $dataCorte ? date('Y-m-d 00:00:00', strtotime($dataCorte)) : now();
            $query->where('vigencia_inicio','<=',$data)
                  ->where(function($w) use ($data) { $w->whereNull('vigencia_fim')->orWhere('vigencia_fim','>=',$data); });
        }

        if ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_faixas_salariais.funcao_profissional_id')
                  ->select('saf_faixas_salariais.*')
                  ->orderBy('fp.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $model = $query->paginate($perPage);
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        $tipos = SafTipoPrestador::orderBy('nome')->pluck('nome','id');
        return view('SafFaixasSalariais.index', compact('model','sort','dir','q','funcoes','tipos','funcaoId','tipoPrestadorId','senioridade','tipoContrato','moeda','vigentes','perPage'));
    }

    public function create()
    {
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        $tipos = SafTipoPrestador::orderBy('nome')->pluck('nome','id');
        $senioridades = ['JUNIOR','PLENO','SENIOR'];
        $tiposContrato = ['CLT','PJ','ESTAGIO'];
        $periodicidades = ['MENSAL','HORA','DIA'];
        return view('SafFaixasSalariais.create', compact('funcoes','tipos','senioridades','tiposContrato','periodicidades'));
    }

    public function store(SafFaixaSalarialRequest $request)
    {
        $dados = $request->validated();
        $dados['moeda'] = strtoupper($dados['moeda']);
        $dados['senioridade'] = empty($dados['senioridade']) ? null : strtoupper($dados['senioridade']);
        $dados['tipo_contrato'] = strtoupper($dados['tipo_contrato']);
        $dados['periodicidade'] = strtoupper($dados['periodicidade']);
        SafFaixaSalarial::create($dados);
        session(['success' => 'Faixa salarial incluída com sucesso!']);
        return redirect()->route('SafFaixasSalariais.index');
    }

    public function show(string $id)
    {
        $cadastro = SafFaixaSalarial::with(['funcaoProfissional','tipoPrestador'])->findOrFail($id);
        return view('SafFaixasSalariais.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = SafFaixaSalarial::findOrFail($id);
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        $tipos = SafTipoPrestador::orderBy('nome')->pluck('nome','id');
        $senioridades = ['JUNIOR','PLENO','SENIOR'];
        $tiposContrato = ['CLT','PJ','ESTAGIO'];
        $periodicidades = ['MENSAL','HORA','DIA'];
        return view('SafFaixasSalariais.edit', compact('model','funcoes','tipos','senioridades','tiposContrato','periodicidades'));
    }

    public function update(SafFaixaSalarialRequest $request, string $id)
    {
        $cadastro = SafFaixaSalarial::findOrFail($id);
        $dados = $request->validated();
        $dados['moeda'] = strtoupper($dados['moeda']);
        $dados['senioridade'] = empty($dados['senioridade']) ? null : strtoupper($dados['senioridade']);
        $dados['tipo_contrato'] = strtoupper($dados['tipo_contrato']);
        $dados['periodicidade'] = strtoupper($dados['periodicidade']);
        $cadastro->fill($dados)->save();
        session(['success' => 'Faixa salarial atualizada com sucesso!']);
        return redirect()->route('SafFaixasSalariais.index');
    }

    public function destroy(string $id)
    {
        $cadastro = SafFaixaSalarial::findOrFail($id);
        $nome = $cadastro->nome ?? ('#'.$cadastro->id);
        $cadastro->delete();
        session(['success' => "Faixa salarial {$nome} excluída com sucesso!"]);
        return redirect()->route('SafFaixasSalariais.index');
    }

    // Exporta respeitando filtros atuais (CSV ou XLSX)
    public function export(Request $request)
    {
        $fmt = strtolower($request->query('fmt','xlsx'));
        if (!in_array($fmt, ['csv','xlsx'], true)) { $fmt = 'xlsx'; }
        $file = 'faixas-salariais-'.date('Ymd-His').'.'.$fmt;
        $export = new SafFaixasSalariaisExport($request->all());
        return Excel::download($export, $file);
    }

    // Exportação PDF (Dompdf) respeitando filtros e ordenação atuais
    public function exportPdf(Request $request)
    {
        $allowedSorts = ['nome','valor_minimo','valor_maximo','vigencia_inicio','vigencia_fim','funcao','senioridade'];
        $sort = $request->query('sort', 'vigencia_inicio');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'vigencia_inicio'; }
        $dir = strtolower($request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $q = trim((string) $request->query('q', ''));
        $funcaoId = $request->query('funcao_profissional_id');
        $tipoPrestadorId = $request->query('saf_tipo_prestador_id');
        $senioridade = $request->query('senioridade');
        $tipoContrato = $request->query('tipo_contrato');
        $moeda = $request->query('moeda');
        $vigentes = $request->boolean('somente_vigentes', false);
        $dataCorte = $request->query('data_corte');

        $query = SafFaixaSalarial::query()->with(['funcaoProfissional','tipoPrestador']);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('observacoes', 'like', "%{$q}%");
            });
        }
        if (!empty($funcaoId)) { $query->where('funcao_profissional_id', $funcaoId); }
        if (!empty($tipoPrestadorId)) { $query->where('saf_tipo_prestador_id', $tipoPrestadorId); }
        if (!empty($senioridade)) { $query->where('senioridade', $senioridade); }
        if (!empty($tipoContrato)) { $query->where('tipo_contrato', $tipoContrato); }
        if (!empty($moeda)) { $query->where('moeda', strtoupper($moeda)); }
        if ($vigentes) {
            $data = $dataCorte ? date('Y-m-d 00:00:00', strtotime($dataCorte)) : now();
            $query->where('vigencia_inicio','<=',$data)
                  ->where(function($w) use ($data) { $w->whereNull('vigencia_fim')->orWhere('vigencia_fim','>=',$data); });
        }

        if ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_faixas_salariais.funcao_profissional_id')
                  ->select('saf_faixas_salariais.*')
                  ->orderBy('fp.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $registros = $query->get();

        $html = view('SafFaixasSalariais.export-pdf', [
            'registros' => $registros,
            // Customizações opcionais vindas da query
            'headerTitle' => $request->query('header_title'),
            'headerSubtitle' => $request->query('header_subtitle'),
            'footerLeft' => $request->query('footer_left'),
            'footerRight' => $request->query('footer_right'),
            'logoUrl' => $request->query('logo_url'),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $fileName = 'saf-faixas-salariais-'.date('Ymd-His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    // Import de CSV/XLSX (com heading row)
    public function import(Request $request)
    {
        $request->validate([
            'arquivo' => ['required','file','mimes:csv,txt,xlsx'],
        ]);
        $import = new SafFaixasSalariaisImport();
        Excel::import($import, $request->file('arquivo'));

        // Coleta falhas para relatório
    $failuresRaw = method_exists($import, 'failures') ? $import->failures() : [];
    $errorsRaw = method_exists($import, 'errors') ? $import->errors() : [];

    $failures = collect($failuresRaw);
    $errors = collect($errorsRaw);

    $inserted = method_exists($import, 'insertedCount') ? (int)$import->insertedCount() : 0;
    $totalFailures = $failures->count();
    $totalErrors = $errors->count();
    $distinctFailureRows = $failures->pluck('row')->filter()->unique()->count();
    $attempted = $inserted + $distinctFailureRows + $totalErrors;

    $msg = "Importação concluída. Linhas processadas: {$attempted} | Inseridos: {$inserted}";
        if ($totalFailures || $totalErrors) {
            // Gera CSV de relatório em storage temporário público
            $rows = [ ['linha','coluna','mensagem','dados'] ];
            $failures->each(function($f) use (&$rows) {
                foreach ((array)$f->errors() as $err) {
                    $attr = $f->attribute();
                    $dadosJson = '';
                    if (method_exists($f, 'values')) {
                        $dadosJson = json_encode($f->values(), JSON_UNESCAPED_UNICODE);
                    }
                    $rows[] = [ $f->row(), is_array($attr)? implode('|',$attr) : (string)$attr, (string)$err, $dadosJson ];
                }
            });
            $errors->each(function($e) use (&$rows) {
                $rows[] = [ null, null, (string)$e, null ];
            });

            $csv = '';
            foreach ($rows as $r) {
                $csv .= implode(';', array_map(function($v){
                    $v = (string)$v;
                    $v = str_replace(["\r","\n",";"], [' ',' ',''], $v);
                    return $v;
                }, $r))."\n";
            }
            $file = 'import_saf_faixas_salariais_erros_'.date('Ymd_His').'.csv';
            Storage::disk('public')->put($file, $csv);
            session(['warning' => $msg.' | Falhas: '.$totalFailures.' | Erros: '.$totalErrors.' | Baixe o relatório: '.asset('storage/'.$file)]);
        } else {
            session(['success' => $msg]);
        }

        return redirect()->route('SafFaixasSalariais.index');
    }

    // Download de modelo para importação
    public function importTemplate(Request $request)
    {
        $fmt = strtolower($request->query('fmt','xlsx'));
        if (!in_array($fmt, ['csv','xlsx'], true)) { $fmt = 'xlsx'; }
        $headings = [[
            'nome','funcao_profissional_id','saf_tipo_prestador_id','senioridade','tipo_contrato','periodicidade','valor_minimo','valor_maximo','moeda','vigencia_inicio','vigencia_fim','ativo','observacoes'
        ],[
            'Analista de Sistemas',1,1,'PLENO','CLT','MENSAL',3500,7000,'BRL','2025-01-01',null,1,'Exemplo de observação'
        ]];
        $export = new class($headings) implements \Maatwebsite\Excel\Concerns\FromArray {
            public function __construct(private array $rows){}
            public function array(): array { return $this->rows; }
        };
        return Excel::download($export, 'modelo-importacao-faixas-salariais.'.$fmt);
    }

    // Duplicar registro e ir para create com dados pré-preenchidos
    public function duplicate(string $id)
    {
        $orig = SafFaixaSalarial::findOrFail($id);
        $data = $orig->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['vigencia_inicio'] = optional($orig->vigencia_inicio)->format('Y-m-d');
        $data['vigencia_fim'] = $orig->vigencia_fim ? optional($orig->vigencia_fim)->format('Y-m-d') : null;
        return redirect()->route('SafFaixasSalariais.create')->withInput($data);
    }
}
