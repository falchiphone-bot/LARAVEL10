<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContasPagar;

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
        $contasPagar = ContasPagar::limit(200)->get();


        return view('ContasPagar.index', compact('contasPagar'));
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

