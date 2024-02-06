<?php

namespace App\Http\Controllers;
use App\Models\Ixc\ReceberIxc;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\assertNotEmpty;

class ReceberIxcController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:RECEBERIXCNETRUBI - LISTAR'])->only('index');
    }


    public function dashboard()
    {
        return view('Ixc/Receber/dashboard');
    }


    public function index()
    {
       $receber = ReceberIxc::limit(500)
       ->orderBy('id','desc')
       ->get();


        return view('Ixc/Clientes.index',compact('receber',
        ));
    }

}

