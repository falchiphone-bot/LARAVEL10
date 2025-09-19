<?php

namespace App\Http\Controllers;

use App\Http\Requests\SafColaboradorRequest;
use App\Models\SafColaborador;
use App\Models\Representantes;
use App\Models\FuncaoProfissional;
use App\Models\SafTipoPrestador;
use App\Models\SafFaixaSalarial;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SafColaboradoresExport;
use Dompdf\Dompdf;
use Dompdf\Options;

class SafColaboradorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:SAF_COLABORADORES - LISTAR'])->only('index');
        $this->middleware(['permission:SAF_COLABORADORES - INCLUIR'])->only(['create','store']);
        $this->middleware(['permission:SAF_COLABORADORES - EDITAR'])->only(['edit','update']);
        $this->middleware(['permission:SAF_COLABORADORES - VER'])->only(['show']);
        $this->middleware(['permission:SAF_COLABORADORES - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:SAF_COLABORADORES - EXPORTAR'])->only(['export','exportXlsx','exportPdf']);
    }

    public function index(Request $request)
    {
        $allowedSorts = ['nome','cidade','uf','pais','representante','funcao','tipo','faixa'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $defaultPerPage = (int) ($request->session()->get('saf_colaboradores.per_page', 20));
        if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }
        $request->session()->put('saf_colaboradores.per_page', $perPage);

    $q = trim((string) $request->query('q', ''));
    $representanteId = $request->query('representante_id');
        $funcaoId = $request->query('funcao_profissional_id');
        $tipoId = $request->query('saf_tipo_prestador_id');
        $faixaId = $request->query('saf_faixa_salarial_id');
    $cpfParam = preg_replace('/\D/', '', (string)$request->query('cpf', ''));
        $cpfExact = filter_var($request->query('cpf_exact'), FILTER_VALIDATE_BOOLEAN);

        $query = SafColaborador::query()
            ->with(['representante','funcaoProfissional','tipoPrestador','faixaSalarial']);

        if ($q !== '') {
            $query->where(function($w) use ($q) {
                                $w->where('nome', 'like', "%{$q}%")
                                    ->orWhere('documento', 'like', "%{$q}%")
                                    ->orWhere('cpf', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
    if (!empty($representanteId)) { $query->where('representante_id', $representanteId); }
        if (!empty($funcaoId)) { $query->where('funcao_profissional_id', $funcaoId); }
        if (!empty($tipoId)) { $query->where('saf_tipo_prestador_id', $tipoId); }
        if (!empty($faixaId)) { $query->where('saf_faixa_salarial_id', $faixaId); }
    if (!empty($cpfParam)) {
            if ($cpfExact) {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') = ?", [$cpfParam]);
            } else {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') LIKE ?", ["%{$cpfParam}%"]);
            }
        }

        // Ordenação por campos relacionados
        if ($sort === 'representante') {
            $query->leftJoin('representantes as r', 'r.id', '=', 'saf_colaboradores.representante_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('r.nome', $dir);
        } elseif ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_colaboradores.funcao_profissional_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fp.nome', $dir);
        } elseif ($sort === 'tipo') {
            $query->leftJoin('saf_tipos_prestadores as tp', 'tp.id', '=', 'saf_colaboradores.saf_tipo_prestador_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('tp.nome', $dir);
        } elseif ($sort === 'faixa') {
            $query->leftJoin('saf_faixas_salariais as fs', 'fs.id', '=', 'saf_colaboradores.saf_faixa_salarial_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fs.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $model = $query->paginate($perPage);

        $representantes = Representantes::orderBy('nome')->pluck('nome','id');
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        $tipos = SafTipoPrestador::orderBy('nome')->pluck('nome','id');
        $faixas = SafFaixaSalarial::orderBy('nome')->pluck('nome','id');

        return view('SafColaboradores.index', compact(
            'model','sort','dir','q','representanteId','funcaoId','tipoId','faixaId','representantes','funcoes','tipos','faixas'
        ));
    }

    public function export(Request $request)
    {
        $allowedSorts = ['nome','cidade','uf','pais','representante','funcao','tipo','faixa'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $q = trim((string) $request->query('q', ''));
    $representanteId = $request->query('representante_id');
        $funcaoId = $request->query('funcao_profissional_id');
        $tipoId = $request->query('saf_tipo_prestador_id');
        $faixaId = $request->query('saf_faixa_salarial_id');
    $cpfParam = preg_replace('/\D/', '', (string)$request->query('cpf', ''));
        $cpfExact = filter_var($request->query('cpf_exact'), FILTER_VALIDATE_BOOLEAN);

        $query = SafColaborador::query()->with(['representante','funcaoProfissional','tipoPrestador','faixaSalarial']);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                                $w->where('nome', 'like', "%{$q}%")
                                    ->orWhere('documento', 'like', "%{$q}%")
                                    ->orWhere('cpf', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
    if (!empty($representanteId)) { $query->where('representante_id', $representanteId); }
        if (!empty($funcaoId)) { $query->where('funcao_profissional_id', $funcaoId); }
        if (!empty($tipoId)) { $query->where('saf_tipo_prestador_id', $tipoId); }
        if (!empty($faixaId)) { $query->where('saf_faixa_salarial_id', $faixaId); }
    if (!empty($cpfParam)) {
            if ($cpfExact) {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') = ?", [$cpfParam]);
            } else {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') LIKE ?", ["%{$cpfParam}%"]);
            }
        }

        if ($sort === 'representante') {
            $query->leftJoin('representantes as r', 'r.id', '=', 'saf_colaboradores.representante_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('r.nome', $dir);
        } elseif ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_colaboradores.funcao_profissional_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fp.nome', $dir);
        } elseif ($sort === 'tipo') {
            $query->leftJoin('saf_tipos_prestadores as tp', 'tp.id', '=', 'saf_colaboradores.saf_tipo_prestador_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('tp.nome', $dir);
        } elseif ($sort === 'faixa') {
            $query->leftJoin('saf_faixas_salariais as fs', 'fs.id', '=', 'saf_colaboradores.saf_faixa_salarial_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fs.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $data = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="saf-colaboradores.csv"',
        ];
        $columns = ['Nome','Representante','Função Profissional','Tipo de Colaborador','Faixa Salarial','Documento','CPF','Email','Telefone','Cidade','UF','País','Ativo'];
        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->nome,
                    optional($row->representante)->nome,
                    optional($row->funcaoProfissional)->nome,
                    optional($row->tipoPrestador)->nome,
                    optional($row->faixaSalarial)->nome,
                    $row->documento,
                    $row->cpf,
                    $row->email,
                    $row->telefone,
                    $row->cidade,
                    $row->uf,
                    $row->pais,
                    $row->ativo ? 'SIM' : 'NÃO',
                ], ';');
            }
            fclose($out);
        }, 'saf-colaboradores.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
    $filters = $request->only(['q','cpf','cpf_exact','representante_id','funcao_profissional_id','saf_tipo_prestador_id','saf_faixa_salarial_id','sort','dir']);
        return Excel::download(new SafColaboradoresExport($filters), 'saf-colaboradores.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $allowedSorts = ['nome','cidade','uf','pais','representante','funcao','tipo','faixa'];
        $sort = $request->query('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $q = trim((string) $request->query('q', ''));
    $representanteId = $request->query('representante_id');
        $funcaoId = $request->query('funcao_profissional_id');
        $tipoId = $request->query('saf_tipo_prestador_id');
        $faixaId = $request->query('saf_faixa_salarial_id');
    $cpfParam = preg_replace('/\D/', '', (string)$request->query('cpf', ''));
        $cpfExact = filter_var($request->query('cpf_exact'), FILTER_VALIDATE_BOOLEAN);

        $query = SafColaborador::query()->with(['representante','funcaoProfissional','tipoPrestador','faixaSalarial']);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                                $w->where('nome', 'like', "%{$q}%")
                                    ->orWhere('documento', 'like', "%{$q}%")
                                    ->orWhere('cpf', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
    if (!empty($representanteId)) { $query->where('representante_id', $representanteId); }
        if (!empty($funcaoId)) { $query->where('funcao_profissional_id', $funcaoId); }
        if (!empty($tipoId)) { $query->where('saf_tipo_prestador_id', $tipoId); }
        if (!empty($faixaId)) { $query->where('saf_faixa_salarial_id', $faixaId); }
    if (!empty($cpfParam)) {
            if ($cpfExact) {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') = ?", [$cpfParam]);
            } else {
                $query->whereRaw("REGEXP_REPLACE(IFNULL(cpf,''), '[^0-9]', '') LIKE ?", ["%{$cpfParam}%"]);
            }
        }

        if ($sort === 'representante') {
            $query->leftJoin('representantes as r', 'r.id', '=', 'saf_colaboradores.representante_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('r.nome', $dir);
        } elseif ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_colaboradores.funcao_profissional_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fp.nome', $dir);
        } elseif ($sort === 'tipo') {
            $query->leftJoin('saf_tipos_prestadores as tp', 'tp.id', '=', 'saf_colaboradores.saf_tipo_prestador_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('tp.nome', $dir);
        } elseif ($sort === 'faixa') {
            $query->leftJoin('saf_faixas_salariais as fs', 'fs.id', '=', 'saf_colaboradores.saf_faixa_salarial_id')
                  ->select('saf_colaboradores.*')
                  ->orderBy('fs.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $registros = $query->get();

        $html = view('SafColaboradores.export-pdf', [
            'registros' => $registros,
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
            'headerTitle' => $request->query('header_title') ?? 'SAF - Colaboradores',
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
    // Orientação horizontal para acomodar mais colunas
    $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        $fileName = 'saf-colaboradores-'.date('Ymd-His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function create()
    {
        $representantes = Representantes::orderBy('nome')->pluck('nome','id');
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        $tipos = SafTipoPrestador::orderBy('nome')->pluck('nome','id');
        $faixas = SafFaixaSalarial::orderBy('nome')->pluck('nome','id');
        return view('SafColaboradores.create', compact('representantes','funcoes','tipos','faixas'));
    }

    public function store(SafColaboradorRequest $request)
    {
        $dados = $request->only([
            'nome','representante_id','funcao_profissional_id','saf_tipo_prestador_id','saf_faixa_salarial_id',
            'documento','cpf','email','telefone','cidade','uf','pais','ativo','observacoes'
        ]);
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        SafColaborador::create($dados);
        session(['success' => 'Colaborador incluído com sucesso!']);
        return redirect()->route('SafColaboradores.index');
    }

    public function show(string $id)
    {
        $cadastro = SafColaborador::with(['representante','funcaoProfissional','tipoPrestador','faixaSalarial'])->findOrFail($id);
        return view('SafColaboradores.show', compact('cadastro'));
    }

    public function edit(string $id)
    {
        $model = SafColaborador::findOrFail($id);
        $representantes = Representantes::orderBy('nome')->pluck('nome','id');
        $funcoes = FuncaoProfissional::orderBy('nome')->pluck('nome','id');
        $tipos = SafTipoPrestador::orderBy('nome')->pluck('nome','id');
        $faixas = SafFaixaSalarial::orderBy('nome')->pluck('nome','id');
        return view('SafColaboradores.edit', compact('model','representantes','funcoes','tipos','faixas'));
    }

    public function update(SafColaboradorRequest $request, string $id)
    {
        $cadastro = SafColaborador::findOrFail($id);
        $dados = $request->only([
            'nome','representante_id','funcao_profissional_id','saf_tipo_prestador_id','saf_faixa_salarial_id',
            'documento','cpf','email','telefone','cidade','uf','pais','ativo','observacoes'
        ]);
        $dados['nome'] = strtoupper($dados['nome']);
        if (!empty($dados['uf'])) { $dados['uf'] = strtoupper($dados['uf']); }
        if (!empty($dados['cidade'])) { $dados['cidade'] = strtoupper($dados['cidade']); }
        if (!empty($dados['pais'])) { $dados['pais'] = strtoupper($dados['pais']); }

        $cadastro->fill($dados);
        $cadastro->save();
        session(['success' => 'Colaborador atualizado com sucesso!']);
        return redirect()->route('SafColaboradores.index');
    }

    public function destroy(string $id)
    {
        $cadastro = SafColaborador::findOrFail($id);
        $nome = $cadastro->nome;
        $cadastro->delete();
        session(['success' => "Colaborador {$nome} excluído com sucesso!"]);
        return redirect()->route('SafColaboradores.index');
    }
}
