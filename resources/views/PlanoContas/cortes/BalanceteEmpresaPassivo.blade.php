if($Passivo  ){
    $totalDebitoPassivo = Lancamento::where(function ($q) use ($DataInicial, $DataFinal, $contaID, $EmpresasID) {
        return $q
            ->where('ContaDebitoID', $contaID)
            ->whereIn('EmpresaID', $EmpresasID)
            ->where('DataContabilidade', '>=', $DataInicial)
            ->where('DataContabilidade', '<=', $DataFinal);
    })
        ->whereDoesntHave('SolicitacaoExclusao')
        ->sum('Lancamentos.Valor');


        $totalDebitoSoma = $totalDebitoSoma + $totalDebito;
        $totalCreditoSoma =  $totalCreditoSoma +$totalCredito;
        $SaldoAtualConta = $totalDebitoSoma -  $totalCreditoSoma ;
        echo '  <br>';
        echo 'LINHA: 532'.'<br>';
        echo 'DEBITO: ' . $totalDebito . '<br>';
        echo 'CREDITO: ' . $totalCredito . '<br>';
        echo 'ID: ' . $contaID . '<br>';
        echo 'SALDO: ' . $SaldoAtual . '<br>';
        echo 'ANTERIOR: ' . $saldoAnterior . '<br>';
        echo 'SALDO DO DIA: ' . $SaldoDia . '<br>';
        echo '  <br>';
        echo 'SALDO DEBITO: ' . $totalDebitoSoma  . '<br>';
        echo 'SALDO CREDITO: ' . $totalCreditoSoma  . '<br>';
        echo 'SALDO ATUAL GERAL: ' . $SaldoAtualConta  . '<br>';


// dd(531, $SaldoAtual, $contasEmpresa5, $totalCredito, $totalDebito, $saldoAnterior, $SaldoDia,
// $totalDebitoPassivo, $totalDebitoAtivo, $ValorRecebido, $Agrupamento, $NomeAgrupamento, $Ativo, $Passivo, $Despesas, $Receitas, $contasEmpresa5,
//  $ResultadoLoop, $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID, $DataInicial,
//  $DataFinal, $contaID, $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID, $DataInicial,
//   $DataFinal, $contaID, $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID, $DataInicial, $DataFinal, $contaID,
//    $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID, $DataInicial, $DataFinal, $contaID, $EmpresasID);

}
dd(532, $SaldoAtual, $contasEmpresa5, $totalCredito, $totalDebito, $saldoAnterior, $SaldoDia);

