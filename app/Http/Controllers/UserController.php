<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Mail\RedefinicaoSenha;
use App\Models\Empresa;
use App\Models\User;
use App\Models\EmpresaUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use Dompdf\Dompdf;
use Dompdf\Options;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->middleware( ["permission:USUARIOS - LISTAR"])->only("index");
        // $this->middleware( ["permission:USUARIOS - INCLUIR"])->only(["create","store"]);
        // $this->middleware( ["permission:USUARIOS - EDITAR"])->only(["edit","update"]);
        // $this->middleware( ["permission:USUARIOS - EXCLUIR"])->only("destroy");
    }
    /**public function __construct()
    {
        $this->middleware('auth');
    }**/

    public function salvarEmpresa(Request $request, $idusuario)
    {
        EmpresaUsuario::where('UsuarioID', $idusuario)->delete();
        foreach ($request->empresa as $empresaID) {
            $novo = new EmpresaUsuario();
            $novo->UsuarioID = $idusuario;
            $novo->EmpresaID = $empresaID;
            $novo->save();
        }
        return redirect('/Usuarios/' . $idusuario)->with('success', 'Empresas atualizadas');
    }
    public function salvarfuncao(Request $request, $idusuario)
    {
        $usuario = User::find($idusuario);
        $usuario->syncRoles($request->funcao);
        return redirect('/Usuarios/' . $idusuario)->with('success', 'Função atualizada');
    }

    public function salvarpermissao(Request $request, $idusuario)
    {
        $usuario = User::find($idusuario);
        $usuario->syncPermissions($request->permissao);
        //$user->syncPermissions(['edit articles', 'delete articles']);
        return redirect('/Usuarios/' . $idusuario)->with('success', 'Permissão atualizadas');
    }

    public function index(Request $request)
    {
        $allowedSorts = ['name', 'email', 'created_at'];
        $sort = $request->query('sort', 'name');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'name'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
    // Persistência de preferência de paginação em sessão
    $defaultPerPage = (int) ($request->session()->get('users.per_page', 20));
    if ($defaultPerPage < 5 || $defaultPerPage > 100) { $defaultPerPage = 20; }
    $perPage = (int) $request->query('per_page', $defaultPerPage);
    if ($perPage < 5) { $perPage = 5; }
    if ($perPage > 100) { $perPage = 100; }
    $request->session()->put('users.per_page', $perPage);

        $q = trim((string) $request->query('q', ''));
        $createdFrom = $request->query('created_from');
        $createdTo = $request->query('created_to');
        $query = User::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if (!empty($createdFrom)) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if (!empty($createdTo)) {
            $query->whereDate('created_at', '<=', $createdTo);
        }
        $cadastros = $query
            ->orderBy($sort, $dir)
            ->when($sort !== 'name', function($q) {
                $q->orderBy('name', 'asc');
            })
            ->paginate($perPage)
            ->appends([
                'q' => $q,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
            ]);
        $linhas = $cadastros->total();
        session(['error' => '']);
        return view('Users.index', compact('cadastros', 'linhas', 'sort', 'dir', 'q', 'createdFrom', 'createdTo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserCreateRequest $request)
    {
        $dados = $request->all();
        //dd($dados);

        User::create($dados);

        return redirect(route('Usuarios.index'))->with('success', 'Salvo com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = User::find($id);
        $permissoes = Permission::pluck('name', 'id');
        $funcoes = Role::pluck('name', 'id');
        $empresas = Empresa::orderBy('Descricao')->pluck('Descricao', 'ID');
        $empresaUsuarios = EmpresaUsuario::where('UsuarioID', $id)->get();

        return view('Users.show', compact('cadastro', 'permissoes', 'funcoes', 'empresas', 'empresaUsuarios'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = User::find($id);
        // dd($cadastro);

        return view('Users.edit', compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cadastro = User::find($id);

        $cadastro->fill($request->all());
        //dd($cadastro);

        $cadastro->save();
        //dd($cadastro->save());

        return redirect(route('Usuarios.index'))->with('success', 'Atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        //PROTEGIDO PARA NÃO EXCLUIR CASO TIVER SENDO USADO EM ALGUMA MODEL.
        if ($user->permissions->count() > 0) {
            return back()->with('status', "Usuário {$user->name} tem permissão em uso.");
        }

        $user->delete();
        return back()->with('status', "Usuário {$user->name} EXCLUÍDO.");

        return redirect(route('Usuarios.index'))->with('success', 'Excluído com sucesso!');
    }

    public function fogotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = hash('sha256',rand(500,5000));
            //limpando tokens
            DB::table('password_reset_tokens')->where('email',$request->email)->delete();

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => date('d/m/Y H:i:s'),
            ]);

            $token = $request->schemeAndHttpHost()."/reset-password/$token";

            Mail::to($request->email)->send(new RedefinicaoSenha($token));

            return back()->with('status', 'Link de redefinição de senha enviado');
        } else {
            return back();
        }
    }

    // Exportação CSV
    public function export(Request $request)
    {
        $allowedSorts = ['name','email','created_at'];
        $sort = $request->query('sort', 'name');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'name'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $q = trim((string) $request->query('q', ''));
        $createdFrom = $request->query('created_from');
        $createdTo = $request->query('created_to');

        $query = User::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if (!empty($createdFrom)) { $query->whereDate('created_at', '>=', $createdFrom); }
        if (!empty($createdTo)) { $query->whereDate('created_at', '<=', $createdTo); }

    $query->orderBy($sort, $dir);
    if ($sort !== 'name') { $query->orderBy('name', 'asc'); }
        $data = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="usuarios.csv"',
        ];
        $columns = ['Nome','Email','Data de cadastro'];
        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [
                    $row->name,
                    $row->email,
                    optional($row->created_at)->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($out);
        }, 'usuarios.csv', $headers);
    }

    // Exportação XLSX
    public function exportXlsx(Request $request)
    {
        $filters = $request->only(['q','created_from','created_to','sort','dir']);
        return Excel::download(new UsersExport($filters), 'usuarios.xlsx');
    }

    // Exportação PDF
    public function exportPdf(Request $request)
    {
        $allowedSorts = ['name','email','created_at'];
        $sort = $request->query('sort', 'name');
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'name'; }
        $dir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $q = trim((string) $request->query('q', ''));
        $createdFrom = $request->query('created_from');
        $createdTo = $request->query('created_to');

        $query = User::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if (!empty($createdFrom)) { $query->whereDate('created_at', '>=', $createdFrom); }
        if (!empty($createdTo)) { $query->whereDate('created_at', '<=', $createdTo); }

    $registros = $query->orderBy($sort, $dir)->when($sort !== 'name', function($q){ $q->orderBy('name', 'asc'); })->get();

        $html = view('Users.export-pdf', [
            'registros' => $registros,
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
            'headerTitle' => $request->query('header_title') ?? 'Usuários',
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
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        $fileName = 'usuarios-'.date('Ymd-His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
