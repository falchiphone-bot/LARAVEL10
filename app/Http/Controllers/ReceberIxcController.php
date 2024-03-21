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
use Illuminate\Http\Request;

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

        Return view('Ixc/Receber/index');
    }

    public function receberperiodo(Request  $request )
    {

                $ativo = true;

                $selecao = $request->Selecao;
                $data_vencimento_inicial = $request->data_vencimento_inicial ;
                $data_vencimento_final = $request->data_vencimento_final; ;


                $Radpop = Radpop::orderBy('id_cidade', 'asc')->get();
                $id_cidades_unicas = array();

                foreach ($Radpop as $item) {
                    $id_cidade = $item->id_cidade;
                    // Verifique se a cidade ainda não foi adicionada ao array de cidades únicas
                    if (!isset($id_cidades_unicas[$id_cidade])) {
                        // Carregue o objeto da cidade relacionada usando a relação definida no modelo Radpop
                        $cidade = $item->cidade; // Supondo que a relação se chame 'cidade'
                        // Adicione o campo 'nome' da cidade ao objeto Radpop
                        $item->nome_cidade = $cidade->nome; // Supondo que o nome do campo na tabela cidade seja 'nome'
                        // Adicione o item ao array de cidades únicas
                        $id_cidades_unicas[$id_cidade] = $item;
                    }
                }


// dd($id_cidades_unicas);
            $receberperiodo = array();
            foreach ($id_cidades_unicas as $pop) {

                $Cidade = $pop->id_cidade;


                $clienteixc = ClientIxc::where('cidade', $Cidade)->get();
                $countAtivado = 0;
                $countDesativado =  0;
                foreach ($clienteixc as $clienteixAt) {

                    if ($clienteixAt->cidade == $Cidade) {

                        if($clienteixAt->ativo == 'S')
                        {
                            $countAtivado = $countAtivado + 1;

                        }
                        else
                        {

                            $countDesativado =  $countDesativado + 1;
                        }

                    }
                }


                // Obtendo os recebimentos dentro do intervalo de datas para clientes da cidade
                $receber = ReceberIxc::whereBetween('data_vencimento', [$data_vencimento_inicial, $data_vencimento_final]);
                if($selecao == 'Receber' )
                {
                    $receber ->where('status', 'A');
                }
                else if($selecao == 'Recebido')
                {
                    $receber ->where('status', 'R');
                }


                $receber ->whereHas('client', function($query) use ($Cidade) {
                    $query->where('cidade', $Cidade);
                })
                ->orderBy('data_vencimento', 'asc')
                ->get();

                $count = $clienteixc->Count();



                $sum = $receber->sum('valor');
                $NomeCidade = $pop->nome_cidade;

                // Forme o array com as variáveis

                $array = array(
                    "Cidade" => $Cidade,
                    "NomeCidade" => $NomeCidade,
                    "Count" => $count,
                    "CountAtivado" => $countAtivado,
                    "CountDesativado" => $countDesativado,
                    "Sum" => $sum
                );

                // Exemplo de uso do array formado
                // echo  "Nome da cidade: ".$array["Cidade"].'-'. $NomeCidade  . ", Quantidade de clientes: " . $array["Count"] . ", Total: " . $array["Sum"]  . "<br>". "<br>";

                array_push($receberperiodo, $array);


             }

            $receberperiodo = collect($receberperiodo);
            // dd($receberperiodo);

            return view('Ixc/Receber.receberperiodo',compact('receberperiodo', 'data_vencimento_inicial', 'data_vencimento_final', 'selecao'));
    }

}

