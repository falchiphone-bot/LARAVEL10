<?php
        if($funcao == 'confirmarSemDefinir'){
            if ($lancamento->SaidasGeral) {
                dd("REGISTRO JÁ MARCADO COMO SAIDA GERAL");
            }

            if ($lancamento->EntradasGeral) {
                dd("REGISTRO JÁ MARCADO COMO ENTRADA GERAL");
            }

            if ($lancamento->Investimentos) {
            dd("REGISTRO JÁ MARCADO COMO INVESTIMENTOS");
            }

            if ($lancamento->Transferencias) {
                dd("REGISTRO JÁ MARCADO COMO TRANSFERENCIAS");
            }

            // if ($lancamento->SemDefinir) {
            //     dd("REGISTRO JÁ MARCADO COMO Sem Definir");
            // }
        }

        if($funcao == 'confirmarLancamentoSaidasGeral'){

            // if ($lancamento->SaidasGeral) {
            //     dd("REGISTRO JÁ MARCADO COMO SAIDA GERAL");
            // }

            if ($lancamento->EntradasGeral) {
                dd("REGISTRO JÁ MARCADO COMO ENTRADA GERAL");
            }

            if ($lancamento->Investimentos) {
            dd("REGISTRO JÁ MARCADO COMO INVESTIMENTOS");
            }

            if ($lancamento->Transferencias) {
                dd("REGISTRO JÁ MARCADO COMO TRANSFERENCIAS");
            }

            if ($lancamento->SemDefinir) {
                dd("REGISTRO JÁ MARCADO COMO Sem Definir");
            }
        }

        if($funcao == 'confirmarTransferencias'){

            if ($lancamento->SaidasGeral) {
                dd("REGISTRO JÁ MARCADO COMO SAIDA GERAL");
            }

            if ($lancamento->EntradasGeral) {
                dd("REGISTRO JÁ MARCADO COMO ENTRADA GERAL");
            }

            if ($lancamento->Investimentos) {
            dd("REGISTRO JÁ MARCADO COMO INVESTIMENTOS");
            }

            // if ($lancamento->Transferencias) {
            //     dd("REGISTRO JÁ MARCADO COMO TRANSFERENCIAS");
            // }

            if ($lancamento->SemDefinir) {
                dd("REGISTRO JÁ MARCADO COMO Sem Definir");
            }
        }

        if($funcao == 'confirmarLancamentoEntradasGeral'){

            if ($lancamento->SaidasGeral) {
                dd("REGISTRO JÁ MARCADO COMO SAIDA GERAL");
            }

            // if ($lancamento->EntradasGeral) {
            //     dd("REGISTRO JÁ MARCADO COMO ENTRADA GERAL");
            // }

            if ($lancamento->Investimentos) {
            dd("REGISTRO JÁ MARCADO COMO INVESTIMENTOS");
            }

            if ($lancamento->Transferencias) {
                dd("REGISTRO JÁ MARCADO COMO TRANSFERENCIAS");
            }

            if ($lancamento->SemDefinir) {
                dd("REGISTRO JÁ MARCADO COMO Sem Definir");
            }
        }


        if($funcao == 'confirmarInvestimentos'){

            if ($lancamento->SaidasGeral) {
                dd("REGISTRO JÁ MARCADO COMO SAIDA GERAL");
            }

            if ($lancamento->EntradasGeral) {
                dd("REGISTRO JÁ MARCADO COMO ENTRADA GERAL");
            }

            // if ($lancamento->Investimentos) {
            // dd("REGISTRO JÁ MARCADO COMO INVESTIMENTOS");
            // }

            if ($lancamento->Transferencias) {
                dd("REGISTRO JÁ MARCADO COMO TRANSFERENCIAS");
            }

            if ($lancamento->SemDefinir) {
                dd("REGISTRO JÁ MARCADO COMO Sem Definir");
            }
        }



