<?php

namespace App\Http\Controllers;

use App\Models\Ixc\Cidade;
use App\Models\Ixc\ReceberIxc;
use App\Models\Ixc\ClientIxc;
use App\Models\Ixc\Radpop;
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

                $Radpop = Radpop::get();


            foreach ($Radpop as $pop) {



                // Obtendo o ID da cidade
                // $Cidade = cidade::where('nome', 'like', 'Valentim Gentil%')->first()->id;
                $Cidade = $pop->id_cidade;

                // Obtendo os clientes da cidade
                $clienteixc = ClientIxc::where('cidade', $Cidade)->limit(20000)->get();

                // Obtendo os recebimentos dentro do intervalo de datas para clientes da cidade
                $receber = ReceberIxc::whereBetween('data_vencimento', [$data_vencimento_inicial, $data_vencimento_final])
                            ->where('status', 'A')
                            ->whereHas('client', function($query) use ($Cidade) {
                                $query->where('cidade', $Cidade);
                            })
                            ->orderBy('data_vencimento', 'asc')
                            ->get();

                //  dd($Cidade, $clienteixc->Count() ,$clienteixc, $receber->sum('valor'));


                // Supondo que $Cidade, $clienteixc, e $receber estejam definidos anteriormente

                // Obtenha os valores das variáveis
                $count = $clienteixc->Count();
                $sum = $receber->sum('valor');

                // Forme o array com as variáveis
                $array = array(
                    "Cidade" => $Cidade,
                    "Count" => $count,
                    "Sum" => $sum
                );

                // Exemplo de uso do array formado
                echo $array["Cidade"] . ", " . $array["Count"] . ", " . $array["Sum"] . "<br>";

              
             }
        // return view('Ixc/Clientes.index',compact('receber',));
    }

}

