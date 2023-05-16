<?php

namespace App\Helpers;

use App\Models\Lancamento;

class SaldoLancamentoHelper
{
    public static function Anterior($DataInicial, $contaID, $EmpresaID)
    {
        $totalCredito = Lancamento::where(function ($q) use ($DataInicial, $contaID, $EmpresaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '<', $DataInicial);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($DataInicial, $contaID, $EmpresaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '<', $DataInicial);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;
        return $saldoAnterior;
    }


    public static function Dia($DataInicial, $contaID, $EmpresaID)
    {
        $totalCredito = Lancamento::where(function ($q) use ($DataInicial, $contaID, $EmpresaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '=', $DataInicial);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($DataInicial, $contaID, $EmpresaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '=', $DataInicial);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoDia = $totalDebito - $totalCredito;
        return $saldoDia;
    }

}
