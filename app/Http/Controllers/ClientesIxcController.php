<?php

namespace App\Http\Controllers;
use App\Models\Ixc\ClientIxc;
use Illuminate\Support\Facades\Validator;


class ClientesIxcController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['CLIENTESIXCNETRUBI - LISTAR'])->only('index');
    }


    public function dashboard()
    {
        return view('Ixc/Clientes/dashboard');
    }


    public function index()
    {
       $clientes = ClientIxc::limit(500)
       ->orderBy('id','desc')
       ->get();

        return view('Ixc/Clientes.index',compact('clientes'));
    }

}

