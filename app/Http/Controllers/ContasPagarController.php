<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContasPagar;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContasPagarController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware(['permission:CONTASCENTROCUSTOS - DASHBOARD'])->only('dashboard');
        $this->middleware(['permission:CONTASPAGAR - LISTAR'])->only('index');
        $this->middleware(['permission:CONTASPAGAR - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:CONTASPAGAR - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:CONTASPAGAR - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:CONTASPAGAR - EXCLUIR'])->only('destroy');
    }


    public function index()
    {
        $contasPagar = ContasPagar::limit(100)->OrderBy('ID','desc')->get();

        if ($contasPagar->count() > 0) {
            session(['entrada' => 'A pesquisa abaixo mostra os 100 últimos lançamentos de todas as empresas autorizadas!']);
            session(['success' => '']);
            session(['error' => '']);
        } else {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        $retorno['DataInicial'] = date('Y-m-d');
        $retorno['DataFinal'] = date('Y-m-d');
        $retorno['EmpresaSelecionada'] = null;

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        return view('ContaPagar.index', compact('contasPagar', 'retorno', 'Empresas'));
    }

    public function indexpost(Request $Request)
    {
        $CompararDataInicial = $Request->DataInicial;

        $contasPagar = ContasPagar::Limit($Request->Limite ?? 100)
            ->join('Contabilidade.EmpresasUsuarios', 'ContasPagar.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->Where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            // ->select(['Lancamentos.ID', 'DataContabilidade', 'Lancamentos.Descricao', 'Lancamentos.EmpresaID', 'Contabilidade.Lancamentos.Valor', 'Historicos.Descricao as DescricaoHistorico', 'Lancamentos.ContaDebitoID', 'Lancamentos.ContaCreditoID'])
            ->orderBy('ContasPagar.ID', 'desc');

        if ($Request->Texto) {
            $texto = $Request->Texto;
            $contasPagar->where(function ($query) use ($texto) {
                return $query->where('ContasPagar.Descricao', 'like', '%' . $texto . '%');
            });
        }

        if ($Request->Valor) {
            $contasPagar->where('ContasPagar.Valor', '=', $Request->Valor);
        }

        if ($Request->DataInicial) {
            $DataInicial = Carbon::createFromFormat('Y-m-d', $Request->DataInicial);
            $contasPagar->where('ContasPagar.DataProgramacao', '>=', $DataInicial->format('d/m/Y'));
        }

        if ($Request->DataFinal) {
            $DataFinal = Carbon::createFromFormat('Y-m-d', $Request->DataFinal);
            $contasPagar->where('ContasPagar.DataProgramacao', '<=', $DataFinal->format('d/m/Y'));
        }

        $Empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->OrderBy('Descricao')
            ->select(['Empresas.ID', 'Empresas.Descricao'])
            ->get();

        $retorno = $Request->all();

        if ($contasPagar->count() > 0) {
            session(['success' => 'A pesquisa abaixo mostra os lançamentos de todas as empresas autorizadas conforme a pesquisa proposta!']);
        } else {
            session(['error' => 'Nenhum lançamento encontrado para as empresas autorizadas!']);
        }

        if ($Request->DataInicial && $Request->DataFinal) {
            if ($DataInicial > $DataFinal) {
                session(['error' => 'Data de início MAIOR que a final. VERIFIQUE!']);
                return view('ContasPagar.index', compact('$contasPagar', 'retorno', 'Empresas'));
            }
        }

        if ($Request->EmpresaSelecionada) {
            $contasPagar->where('ContasPagar.EmpresaID', $Request->EmpresaSelecionada);
        }

        $contasPagar = $contasPagar->get();

        return view('ContaPagar.index', compact('contasPagar', 'retorno', 'Empresas'));
    }


    public function create()
    {
        // Lógica para exibir o formulário de criação
    }

    public function store(Request $request)
    {
        // Lógica para salvar uma nova entrada na tabela
    }

    public function show($id)
    {
        // Lógica para exibir um registro específico
    }

    public function edit($id)
    {
        // Lógica para exibir o formulário de edição
    }

    public function update(Request $request, $id)
    {
        // Lógica para atualizar um registro específico
    }

    public function destroy($id)
    {
        // Lógica para excluir um registro específico
    }
}

