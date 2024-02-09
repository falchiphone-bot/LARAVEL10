<?php

namespace App\Http\Controllers;
use App\Models\Ixc\ReceberIxc;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

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

                $data_vencimento_inicial = now()->format('Y-m-d');
                $data_vencimento_final = now()->endOfMonth()->format('Y-m-d');

                $receber = ReceberIxc::
                whereBetween('data_vencimento', [$data_vencimento_inicial, $data_vencimento_final])
                ->where('status', 'A')
                    ->orderBy('data_vencimento', 'asc')
                    ->get();

            dd($receber->sum('valor'));

        // return view('Ixc/Clientes.index',compact('receber',));
    }

}

