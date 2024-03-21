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


// Agora $id_cidades_unicas contém os itens únicos baseados no campo id_cidade

// dd($id_cidades_unicas);
            $receberperiodo = array();


            foreach ($id_cidades_unicas as $pop) {



                // Obtendo o ID da cidade
                // $Cidade = cidade::where('nome', 'like', 'Valentim Gentil%')->first()->id;
                $Cidade = $pop->id_cidade;


                if($pop->nome_cidade == 'Aparecida do Taboado')
                {
                    dd($pop);
                    continue;
                }

                if($pop->id_cidade == '5106')
                {
                    dd($pop);
                    continue;
                }

                if($Cidade  != '3280'
                && $Cidade   != '3286'
                && $Cidade   != '3409'
                && $Cidade   != '3547'
                && $Cidade   != '3673'
                && $Cidade   != '3711'
                && $Cidade   != '3716'
                && $Cidade   != '3761'
                && $Cidade   != '3778'
                && $Cidade   != '3787'
                && $Cidade   != '3860'
                && $Cidade   != '3880'
                && $Cidade   != '3894'

                && $Cidade   != '3907'


                ){
                    dd($Cidade );
                }



                // Obtendo os clientes da cidade
                $clienteixc = ClientIxc::where('cidade', $Cidade)->get();


                $clienteixcAtivado = ClientIxc::
                where('ativo', 'S')
                ->where('cidade', $Cidade)
                ->get();


                $clienteixcDesativado = ClientIxc::-
                where('ativo', 'N')
                >where('cidade', $Cidade)
                ->get();

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

                //  dd($Cidade, $clienteixc->Count() ,$clienteixc, $receber->sum('valor'));


                // Supondo que $Cidade, $clienteixc, e $receber estejam definidos anteriormente

                // Obtenha os valores das variáveis
                $count = $clienteixc->Count();
                // $countAtivado = $clienteixcAtivado->Count();
                // $countDesativado = $clienteixcDesativado->Count();

                $sum = $receber->sum('valor');
                $NomeCidade = $pop->nome_cidade;





                // Forme o array com as variáveis
                $array = array(
                    "Cidade" => $Cidade,
                    "NomeCidade" => $NomeCidade,
                    "Count" => $count,
                    // "CountAtivado" => $countAtivado,
                    // "CountDesativado" => $countDesativado,
                    "Sum" => $sum
                );

                if($NomeCidade  != 'Álvares Florence'
                && $NomeCidade  != 'Américo de Campos'
                && $NomeCidade  != 'Cosmorama'
                && $NomeCidade  != 'Jales'
                && $NomeCidade  != 'Parisi'
                && $NomeCidade  != 'Poloni'
                && $NomeCidade  != 'Pontes Gestal'
                && $NomeCidade  != 'Riolândia'
                && $NomeCidade  != 'Santa Albertina'
                && $NomeCidade  != 'Santa Fé do Sul'
                && $NomeCidade  != 'Tanabi'
                && $NomeCidade  != 'Três Fronteiras'
                && $NomeCidade  != 'Valentim Gentil'
                ){
dd($NomeCidade);
                }


                // Exemplo de uso do array formado
                // echo  "Nome da cidade: ".$array["Cidade"].'-'. $NomeCidade  . ", Quantidade de clientes: " . $array["Count"] . ", Total: " . $array["Sum"]  . "<br>". "<br>";

                array_push($receberperiodo, $array);


             }
  dd($receberperiodo);
            $receberperiodo = collect($receberperiodo);


            return view('Ixc/Receber.receberperiodo',compact('receberperiodo', 'data_vencimento_inicial', 'data_vencimento_final', 'selecao'));
    }

}

