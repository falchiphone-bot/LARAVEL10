<?php

namespace App\Helpers;

use App\Models\Lancamento;

class SaldoLancamentoHelper
{
    public static function Anterior($DataInicial, $contaID, $EmpresaID)
    {
        // Corrige formato da data para SQL Server (Y-m-d)
        $dataSql = $DataInicial;
        if (!empty($DataInicial) && preg_match('/\d{2}\/\d{2}\/\d{4}/', $DataInicial)) {
            try {
                $dataSql = \Carbon\Carbon::createFromFormat('d/m/Y', $DataInicial)->format('Y-m-d');
            } catch (\Exception $e) {
                $dataSql = $DataInicial; // fallback
            }
        }
        $totalCredito = Lancamento::where(function ($q) use ($dataSql, $contaID, $EmpresaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '<', $dataSql);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($dataSql, $contaID, $EmpresaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '<', $dataSql);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoAnterior = $totalDebito - $totalCredito;
        return $saldoAnterior;
    }


    public static function Dia($DataInicial, $contaID, $EmpresaID)
    {
        // Corrige formato da data para SQL Server (Y-m-d)
        $dataSql = $DataInicial;
        if (!empty($DataInicial) && preg_match('/\d{2}\/\d{2}\/\d{4}/', $DataInicial)) {
            try {
                $dataSql = \Carbon\Carbon::createFromFormat('d/m/Y', $DataInicial)->format('Y-m-d');
            } catch (\Exception $e) {
                $dataSql = $DataInicial; // fallback
            }
        }
        $totalCredito = Lancamento::where(function ($q) use ($dataSql, $contaID, $EmpresaID) {
            return $q
                ->where('ContaCreditoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '=', $dataSql);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $totalDebito = Lancamento::where(function ($q) use ($dataSql, $contaID, $EmpresaID) {
            return $q
                ->where('ContaDebitoID', $contaID)
                ->where('EmpresaID', $EmpresaID)
                ->where('DataContabilidade', '=', $dataSql);
        })
            ->whereDoesntHave('SolicitacaoExclusao')
            ->sum('Lancamentos.Valor');

        $saldoDia = $totalDebito - $totalCredito;
        return $saldoDia;
    }

}
