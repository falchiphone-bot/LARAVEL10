<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeriadoCreateRequest;
use App\Http\Requests\FeriadosCreateRequest;
use App\Http\Requests\RepresentantesCreateRequest;
use App\Http\Requests\RedeSocialRepresentantesCreateRequest;
use App\Models\Feriado;
use App\Models\Representantes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use App\Models\RedeSocial;
use App\Models\RedeSocialUsuarios;
use App\Models\TipoRepresentante;
use App\Models\Empresa;

use Google\Service\ServiceControl\Auth as ServiceControlAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Facades\Gate;

require_once app_path('helpers.php');
use App\Exports\RepresentantesExport;
use Maatwebsite\Excel\Facades\Excel;

class RepresentantesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:REPRESENTANTES - CADASTRO DO REPRESENTANTE'])->only('representantecadastro');
        $this->middleware(['permission:REPRESENTANTES - LISTAR'])->only('index');
        $this->middleware(['permission:REPRESENTANTES - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:REPRESENTANTES - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:REPRESENTANTES - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:REPRESENTANTES - EXCLUIR'])->only('destroy');
    }

    public function index(Request $request)
    {
        // Sessão: limpar filtros
        if ($request->boolean('clear')) {
            Session::forget('representantes.index.filters');
            return redirect()->route('Representantes.index');
        }

        // Carregar filtros salvos se nenhum filtro informado
    $saved = Session::get('representantes.index.filters', []);
    $incomingFilters = $request->only(['nome','email','agente_fifa','oficial_cbf','sem_registro','empresa_id','per_page','sort','dir']);
        $hasIncoming = collect($incomingFilters)->filter(function($v){ return $v !== null && $v !== ''; })->isNotEmpty();
        if (!$hasIncoming && !empty($saved)) {
            // Aplicar filtros salvos e redirecionar para manter URL limpa
            return redirect()->route('Representantes.index', $saved);
        }

        // Se lembrar filtros estiver marcado, salvar na sessão
        if ($request->boolean('remember')) {
            Session::put('representantes.index.filters', $incomingFilters);
        }

        // Empresas vinculadas ao usuário
        $empresaIds = DB::table('Contabilidade.EmpresasUsuarios')
            ->where('UsuarioID', Auth::id())
            ->pluck('EmpresaID');

        // Lista de empresas para filtro na view
        $empresas = DB::table('Contabilidade.Empresas as e')
            ->join('Contabilidade.EmpresasUsuarios as eu', 'e.ID', '=', 'eu.EmpresaID')
            ->where('eu.UsuarioID', Auth::id())
            ->orderBy('e.Descricao')
            ->get(['e.ID', 'e.Descricao']);

        // Filtro por empresas acessíveis (evita problemas de alias/schema no join)
        $query = Representantes::query()
            ->with('MostraEmpresa')
            ->whereIn('EmpresaID', $empresaIds);

        // Filtro por empresa específica (se informada e acessível)
        if ($request->filled('empresa_id')) {
            $empresaIdReq = (int)$request->input('empresa_id');
            if ($empresaIds->contains($empresaIdReq)) {
                $query->where('EmpresaID', $empresaIdReq);
            }
        }

        // Busca por nome e e-mail
        if ($request->filled('nome')) {
            $nome = trim($request->input('nome'));
            $query->where('Representantes.nome', 'like', "%{$nome}%");
        }
        if ($request->filled('email')) {
            $email = trim($request->input('email'));
            $query->where('Representantes.email', 'like', "%{$email}%");
        }

        // Filtros por flags booleanas
        $mapBool = function ($v) {
            if (is_null($v) || $v === '') return null;
            $v = strtolower((string)$v);
            if (in_array($v, ['1', 'true', 'sim', 'yes'], true)) return 1;
            if (in_array($v, ['0', 'false', 'nao', 'não', 'no'], true)) return 0;
            return null;
        };

        $agente = $mapBool($request->input('agente_fifa'));
        if ($agente !== null) {
            $query->where('agente_fifa', $agente);
        }

        $oficial = $mapBool($request->input('oficial_cbf'));
        if ($oficial !== null) {
            $query->where('oficial_cbf', $oficial);
        }

        $sem = $mapBool($request->input('sem_registro'));
        if ($sem !== null) {
            $query->where('sem_registro', $sem);
        }

        // Ordenação
        $allowedSorts = ['nome', 'agente_fifa', 'oficial_cbf', 'sem_registro', 'empresa'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'nome';
        }
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Aplicar join para ordenar por empresa, antes da contagem
        if ($sort === 'empresa') {
            $query->leftJoin('Contabilidade.Empresas as e', 'e.ID', '=', 'Representantes.EmpresaID');
        }

        // Total antes da paginação
    $total = (clone $query)->count();

        // Seleciona apenas colunas do modelo principal para evitar colisão de nomes do join
    // Apenas colunas do modelo principal
        $query->select('representantes.*');

        // Paginação com preservação de filtros e ordenação
        $perPage = (int)($request->input('per_page', 25));
        if ($perPage <= 0) { $perPage = 25; }

        if ($sort === 'empresa') {
            $query->orderBy('e.Descricao', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $model = $query
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('Representantes.index', compact('model', 'total', 'perPage', 'sort', 'dir', 'empresas'));
    }

    public function export(Request $request)
    {
        // Reaproveita a construção da query da index
        $empresaIds = DB::table('Contabilidade.EmpresasUsuarios')
            ->where('UsuarioID', Auth::id())
            ->pluck('EmpresaID');
        $query = Representantes::query()->whereIn('EmpresaID', $empresaIds);

        if ($request->filled('nome')) {
            $nome = trim($request->input('nome'));
            $query->where('Representantes.nome', 'like', "%{$nome}%");
        }
        if ($request->filled('email')) {
            $email = trim($request->input('email'));
            $query->where('Representantes.email', 'like', "%{$email}%");
        }

        $mapBool = function ($v) {
            if (is_null($v) || $v === '') return null;
            $v = strtolower((string)$v);
            if (in_array($v, ['1', 'true', 'sim', 'yes'], true)) return 1;
            if (in_array($v, ['0', 'false', 'nao', 'não', 'no'], true)) return 0;
            return null;
        };
        $agente = $mapBool($request->input('agente_fifa'));
        if ($agente !== null) $query->where('agente_fifa', $agente);
        $oficial = $mapBool($request->input('oficial_cbf'));
        if ($oficial !== null) $query->where('oficial_cbf', $oficial);
        $sem = $mapBool($request->input('sem_registro'));
        if ($sem !== null) $query->where('sem_registro', $sem);

        $allowedSorts = ['nome', 'agente_fifa', 'oficial_cbf', 'sem_registro'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) $sort = 'nome';
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $query->select('representantes.*');
        $data = $query->orderBy($sort, $dir)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="representantes.csv"',
        ];

        $columns = ['Nome', 'Telefone', 'Email', 'CPF', 'CNPJ', 'Agente FIFA', 'Oficial CBF', 'Sem registro'];

        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->nome,
                    $row->telefone,
                    $row->email,
                    $row->cpf,
                    $row->cnpj,
                    $row->agente_fifa ? 'SIM' : 'NÃO',
                    $row->oficial_cbf ? 'SIM' : 'NÃO',
                    $row->sem_registro ? 'SIM' : 'NÃO',
                ], ';');
            }
            fclose($out);
        }, 'representantes.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['nome','email','agente_fifa','oficial_cbf','sem_registro','sort','dir']);
        return Excel::download(new RepresentantesExport($filters), 'representantes.xlsx');
    }

    public function representantecadastro()
    {
        if ($this->middleware('permission:REPRESENTANTES - CADASTRO DO USUARIO')) {
            $model = Representantes::join('Contabilidade.EmpresasUsuarios', 'Representantes.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
                ->where('email', 'like', Auth::user()->email)
                ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
                ->orderBy('nome')
                ->get();
                return view('Representantes.index', compact('model'));
        }

    }

    public function create()
    {
        // Empresas acessíveis ao usuário atual
        $empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::id())
            ->orderBy('Empresas.Descricao')
            ->get(['Empresas.ID', 'Empresas.Descricao']);

        return view('Representantes.create', compact('empresas'));
    }

    public function store(RepresentantesCreateRequest $request)
    {
        $cpf = $request->cpf;
        $cnpj = $request->cnpj;
        $LiberaCPF = $request->liberacpf;
        $LiberaCNPJ = $request->liberacnpj;
        $limpacpf = $request->limpacpf;
        $limpacnpj = $request->limpacnpj;

        $request['nome'] = strtoupper($request['nome']);

        $existecadastro = Representantes::where('nome', trim($request['nome']))->first();
        if ($existecadastro) {
            session(['error' => 'NOME:  ' . $request->nome . ', já existe! NADA INCLUÍDO! ']);
            return redirect(route('Representantes.index'));
        }

        if ($LiberaCPF == null) {
            if ($cpf) {
                if (validarCPF($cpf)) {
                    session(['cpf' => 'CPF:  ' . $request->cpf . ', VALIDADO! ']);
                } else {
                    session(['error' => 'CPF:  ' . $request->cpf . ', DEVE SER CORRIGIDO! NADA INCLUÍDO! ']);
                    return redirect()->route('Representantes.create')->withInput();
                }
            }
        } else {
            if ($limpacpf) {
                $request['cpf'] = '';
            }
        }

        if ($LiberaCNPJ == null) {
            if ($cnpj) {
                if (validarCNPJ($cnpj)) {
                    session(['cnpj' => 'CNPJ:  ' . $request->cnpj . ', VALIDADO! ']);
                } else {
                    session(['error' => 'CNPJ:  ' . $request->cnpj . ', DEVE SER CORRIGIDO! NADA INCLUÍDO! ']);
                    return redirect()->route('Representantes.create')->withInput();
                }
            }
        } else {
            if ($limpacnpj) {
                $request['cnpj'] = null;
            }
        }

        $enderecoEmailIncorreto = $request->email;
        $enderecoEmailCorrigido = corrigirEnderecoEmail($enderecoEmailIncorreto);
        $request['email'] = $enderecoEmailCorrigido;

        // Definir EmpresaID: prioridade para sessão, depois empresas do usuário
        $empresaId = $request->input('EmpresaID');
        if (empty($empresaId)) {
            $empresaId = session('EmpresaID');
        }
        if (empty($empresaId)) {
            $empresaIds = DB::table('Contabilidade.EmpresasUsuarios')
                ->where('UsuarioID', Auth::id())
                ->pluck('EmpresaID');
            if ($empresaIds->count() === 1) {
                $empresaId = $empresaIds->first();
            } elseif ($empresaIds->count() > 1) {
                session(['error' => 'Selecione a empresa antes de incluir o representante.']);
                return redirect()->back()->withInput();
            } else {
                session(['error' => 'Nenhuma empresa vinculada ao usuário para gravar o representante.']);
                return redirect()->back()->withInput();
            }
        }

        $request['EmpresaID'] = $empresaId;
        $request['user_created'] = Auth::user()->email;
        $request['user_updated'] = Auth::user()->email;

        $model = $request->all();
        Representantes::create($model);

        return redirect(route('Representantes.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Representantes::find($id);
        return view('Representantes.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        session(['Representante_id' => $id]);
        $RedeSocial = RedeSocial::orderBy('nome')->get();

        $redesocialUsuario = RedeSocialUsuarios::where('RedeSocialRepresentante_id', $id)
            ->orderBy('RedeSocialRepresentante')
            ->get();

        $tipor = TipoRepresentante::orderBy('nome')->get();

        $model = Representantes::with('MostraEmpresa')->find($id);
        // Empresas acessíveis para possível troca
        $empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::id())
            ->orderBy('Empresas.Descricao')
            ->get(['Empresas.ID', 'Empresas.Descricao']);
        $retorno['redesocial'] = $model->RedeSocial;
        $tiporep['tiporepresentante'] = $model->tipo_representante;

    return view('Representantes.edit', compact('model', 'RedeSocial', 'retorno', 'redesocialUsuario', 'tipor', 'tiporep', 'empresas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\RepresentantesUpdateRequest $request, string $id)
    {
        // Regra: sem_registro não pode coexistir com as outras flags
        $agente = (bool)$request->input('agente_fifa');
        $oficial = (bool)$request->input('oficial_cbf');
        $sem = (bool)$request->input('sem_registro');
        if ($sem && ($agente || $oficial)) {
            session(['error' => 'Sem registro não pode ser marcado junto com Agente FIFA ou Oficial CBF.']);
            return redirect(route('Representantes.edit', $id));
        }

        $cpf = $request->cpf;
        $cnpj = $request->cnpj;
        $LiberaCPF = $request->liberacpf;
        $LiberaCNPJ = $request->liberacnpj;
        $limpacpf = $request->limpacpf;
        $limpacnpj = $request->limpacnpj;

        if ($LiberaCPF == null) {
            if ($cpf) {
                if (validarCPF($cpf)) {
                    session(['cpf' => 'CPF:  ' . $request->cpf . ', VALIDADO! ']);
                } else {
                    session(['error' => 'CPF:  ' . $request->cpf . ', DEVE SER CORRIGIDO! NADA ALTERADO! ']);
                    return redirect(route('Representantes.edit', $id));
                }
            }
        } else {
            if ($limpacpf) {
                $request['cpf'] = '';
            }
        }

        if ($LiberaCNPJ == null) {
            if ($cnpj) {
                if (validarCNPJ($cnpj)) {
                    session(['cnpj' => 'CNPJ:  ' . $request->cnpj . ', VALIDADO! ']);
                } else {
                    session(['error' => 'CNPJ:  ' . $request->cnpj . ', DEVE SER CORRIGIDO! NADA ALTERADO! ']);
                    return redirect(route('Representantes.edit', $id));
                }
            }
        } else {
            if ($limpacnpj) {
                $request['cnpj'] = null;
            }
        }

        // Obtém o endereço de e-mail do objeto $request
        $email = $request->email;

        // Remove caracteres inválidos do endereço de e-mail
        $emailCorrigido = preg_replace('/[^a-zA-Z0-9.@_-]/', '', $email);

        // Verifica se o símbolo "@" está presente no endereço corrigido
        if (strpos($emailCorrigido, '@') === false) {
            // Endereço de e-mail inválido, pode lidar com o erro aqui
            // Por exemplo, lançar uma exceção ou retornar uma mensagem de erro
            // ...

            session(['error' => 'EMAIL:  ' . $request->email . ', DEVE SER CORRIGIDO! NADA ALTERADO! RETORNADO AO VALOR JÁ REGISTRADO! ']);
            return redirect(route('Representantes.edit', $id));

            // Definir o endereço de e-mail corrigido como vazio ou null
            // $emailCorrigido = '';
        }

        // Atualiza a propriedade email do objeto $request com o endereço corrigido
        $request['email'] = $emailCorrigido;

        // Normalização para evitar gravar NULL em colunas não-nullables
        // Regras de limpeza amigáveis:
        // - Se o usuário deixar o CPF/CNPJ em branco e possuir a permissão de limpeza, zera o campo ("") sem exigir marcar checkbox
        // - Caso contrário, mantém o comportamento de preservar quando vazio (não sobrescrever)

    $canCleanCpf = \Illuminate\Support\Facades\Gate::allows('REPRESENTANTES - LIMPA CAMPO CPF');
    $canCleanCnpj = \Illuminate\Support\Facades\Gate::allows('REPRESENTANTES - LIMPA CAMPO CNPJ');

        // CPF
        if ($request->has('cpf') && $request->input('cpf') === '' && $canCleanCpf) {
            // Limpeza por deixar em branco (com permissão)
            $request->merge(['cpf' => '']);
        } else {
            if (!$LiberaCPF) {
                if (!$request->filled('cpf')) {
                    // Preserva o valor atual no banco
                    $request->request->remove('cpf');
                }
            } else {
                if ($limpacpf) {
                    $request->merge(['cpf' => '']);
                } else {
                    if (!$request->filled('cpf')) {
                        $request->request->remove('cpf');
                    }
                }
            }
        }

        // CNPJ
        if ($request->has('cnpj') && $request->input('cnpj') === '' && $canCleanCnpj) {
            $request->merge(['cnpj' => '']);
        } else {
            if (!$LiberaCNPJ) {
                if (!$request->filled('cnpj')) {
                    $request->request->remove('cnpj');
                }
            } else {
                if ($limpacnpj) {
                    $request->merge(['cnpj' => '']);
                } else {
                    if (!$request->filled('cnpj')) {
                        $request->request->remove('cnpj');
                    }
                }
            }
        }

        $cadastro = Representantes::find($id);

        // Troca de empresa (opcional): só aplica se o usuário tiver acesso à empresa selecionada
        if ($request->filled('EmpresaID')) {
            $novaEmpresaId = (int) $request->input('EmpresaID');
            $temAcesso = DB::table('Contabilidade.EmpresasUsuarios')
                ->where('UsuarioID', Auth::id())
                ->where('EmpresaID', $novaEmpresaId)
                ->exists();
            if ($temAcesso) {
                $request['EmpresaID'] = $novaEmpresaId;
            } else {
                session(['error' => 'Você não possui acesso à empresa selecionada.']);
                return redirect(route('Representantes.edit', $id));
            }
        }
        $request['nome'] = strtoupper($request['nome']);
        $request['user_updated'] = Auth::user()->email;
        $cadastro->fill($request->all());

        $cadastro->save();

        session(['success' => 'NOME:  ' . $request->nome . ', ALTERADO! ']);
        return redirect(route('Representantes.edit',$id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = Representantes::find($id);

        $model->delete();
        return redirect(route('Representantes.index'));
    }

    public function CreateRedeSocialRepresentantes(RedeSocialRepresentantesCreateRequest $request)
    {
        $request['user_created'] = Auth::user()->email;
        $model = $request->all();
        RedeSocialUsuarios::create($model);

        return redirect(route('Representantes.edit',$request->RedeSocialRepresentante_id));
    }

    // public function UpdateRedeSocialRepresentantes(Request $request, string $id)
    // {

    //     $cadastro =  RedeSocialUsuarios::find($id);
    //     $request['user_updated'] = Auth ::user()->email;
    //     $cadastro->fill($request->all()) ;

    //     $cadastro->save();

    //     return redirect(route('Representantes.index'));
    // }
    public function DestroyRedeSocialRepresentantes(string $id)
    {
        dd($id);

        $model = RedeSocialUsuarios::find($id);

        $model->delete();
        session(['success' => 'REDE SOCIAIS:  ' . $model->RedeSocialRepresentantes->nome . ' EXCLUÍDO COM SUCESSO!']);
        return redirect(route('Representantes.edit'));
    }
}
