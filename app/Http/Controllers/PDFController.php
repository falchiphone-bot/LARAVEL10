<?php

namespace App\Http\Controllers;

use Smalot\PdfParser\Parser;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function readPDF()
    {
        // Caminho do PDF que você deseja ler
        $filePath = storage_path('app/public/sample.pdf');

        // Criando uma instância do Parser
        $parser = new Parser();

        // Parseando o arquivo PDF
        $pdf = $parser->parseFile($filePath);

        // Extraindo o texto do PDF
        $text = $pdf->getText();

        dd($text);



  ////////////////////////////////////////////
        // Texto JSON com caracteres especiais
        $jsonText = $pdf->getText();

        // '{"text":"Cart\u00e3o Sicredi VISA PLATINUM\tFatura mensal\nPEDRO ROBERTO FALCHI\nCart\u00e3o n\u00ba: 4891********2113\nResumo da Fatura\nValor (R$)Valor (US$)\nTotal da fatura anterior\t\u000038.908,79\n(-) Pagamentos | Cr\u00e9ditos\t\u000039.166,07 \u00000,00\n(+) Despesas atuais | D\u00e9bitos no Brasil \u000026.743,71\n(+) Despesas atuais | D\u00e9bitos no Exterior \u0000614,23 \u0000107,52\nSubtotal Despesas e Pagamentos\t\u000027.100,66\n(+) Encargos rotativos\t\u00000,00\n(+) Encargos saque\t\u00000,00\n(+) Encargos compras parceladas\t\u00000,00\n(+) Encargos parcelamento de fatura\t\u00000,00\n(+) Encargos parcelamento de rotativo \u00000,00\n(+) IOF\t\u00000,00\nSubtotal Encargos\t\u00000,00\n(=) Total desta Fatura\t\u000027.100,66\n..."}';

        // Decodificar o JSON para PHP
        $data = json_decode($jsonText, true);

        // Verificar se houve erro ao decodificar
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Erro ao decodificar JSON: " . json_last_error_msg();
            exit;
        }

        // Função para substituir caracteres de controle e tabulações
        function limparTexto($texto) {
            // Substitui caracteres Unicode especiais e de controle (\u0000)
            $texto = preg_replace('/\\\u[0-9a-fA-F]{4}/', '', $texto);

            // Remover espaços e caracteres indesejados
            $texto = preg_replace('/[\t\n]+/', ' ', $texto); // Remove tabulações e quebras de linha

            // Decodificar entidades HTML
            $texto = html_entity_decode($texto, ENT_QUOTES, 'UTF-8');

            // Remover espaços em excesso
            $texto = trim($texto);

            return $texto;
        }

        // Aplicar a função de limpeza no texto da fatura
        $textoLimpado = limparTexto($data['text']);


dd($textoLimpado);

        // Exibir o texto formatado
        echo nl2br($textoLimpado);
////////////////////////////////////////////////////////////////////////////////////////////////////






        // Retornando o texto extraído
        return response()->json(['text' => $text]);
    }
}
