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
//  dd($consulta);
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
}
