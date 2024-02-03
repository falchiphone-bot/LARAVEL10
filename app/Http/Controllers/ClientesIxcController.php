<?php

namespace App\Http\Controllers;
use App\Models\Ixc\ClientIxc;
use Illuminate\Support\Facades\Validator;


class ClientesIxcController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware(['permission:MOEDAS - LISTAR'])->only('index');

    }


    // public function dashboard()
    // {
    //     return view('Moedas.dashboard');
    // }


    public function index()
    {
       $clientes = ClientIxc::limit(500)
       ->orderBy('id','desc')
       ->get();

        return view('Ixc/Clientes.index',compact('clientes'));
    }

}

