<?php

namespace App\Http\Controllers;

use App\Helpers\SicredApiHelper;
use App\Models\Atletas\CobrancaSicredi;
use Illuminate\Http\Request;
use Carbon\Carbon;


class SicrediController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dia = Carbon::now()->subDay(1)->format('d/m/Y');
        $consulta = SicredApiHelper::boletoLiquidadoDia('72334','0703','16',$dia);
        if ($consulta['error']??null) {
            return $consulta['message'];
        }

        return view('Sicredi.index',compact('consulta','dia'));

    }

    public function salvarLiquidacaoDia()
    {
        $consulta = SicredApiHelper::boletoLiquidadoDia('72334','0703','16','03/04/2023');

        $nossonumero = 0;
        foreach($consulta['items'] as $item)
        {
            $verificar = CobrancaSicredi::orderBy('DataLiquidacao','DESC')->where('NossoNumero',$item['nossoNumero'])->first();
            if ($verificar) {
                $nossonumero ++;
            }else {
                $cs = CobrancaSicredi::create([
                    'NossoNumero' => $item['nossoNumero'],
                    'Carteira' => 'SIMPLES',
                    'NumeroDocumento' => $item['seuNumero'],
                    'Pagador' => '',
                    'DataEmissao' => '',
                    'DataVencimento' => '',
                    'Valor' => $item['valor'],
                    'Liquidacao' => $item['valorLiquidado'],
                    'DataLiquidacao' => $item['dataPagamento'],
                    'SituacaoTitulo' => 'LIQUIDADO',
                    'Motivo' => date('d/m/Y H:i:s'),
                    'Associado' => '0703 - 16 - 072334 - PRF PROVEDOR DE INTERNET LTDA',
                    'Conta' => '72334',
                    'Beneficiario' => '0703 - 16 - 072334 - PRF PROVEDOR DE INTERNET LTDA',
                    'Cobrando' => null,
                    'CobrandoEm' => null,
                    'PrevisaoPgto' => null,
                    'MovimentoPorUser' => null,
                    'MovimentoEm' => null,
                    'Atualizado' => date('d/m/Y H:i:s'),
                    'QuitadoIXC' => null,
                    'status_internet' => null,
                    'BaixarBanco' => 0,
                ]);
            }
        }
        return "Econtrado ".$nossonumero." Nosso Numero ja cadastrado e não cadastardo no banco de dados";

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Testes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TesteCreateRequest $request)
    {


        $dados = $request->all();
        //dd($dados);

        Teste::create($dados);

        return redirect(route('Testes.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = Teste::find($id);

        return view('Testes.show', compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastro = Teste::find($id);
        // dd($cadastro);

        return view('Testes.edit', compact('cadastro'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = Teste::find($id);

        $cadastro->fill($request->all());
        //dd($dados);

        $cadastro->save();

        return redirect(route('Testes.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cadastro = Teste::find($id);
        $cadastro->delete();
        return redirect(route('Testes.index'));
    }
}
