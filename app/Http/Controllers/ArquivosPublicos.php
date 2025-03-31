<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\LancamentoDocumento;
use Illuminate\Http\Request;

class ArquivosPublicos extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id_arquivo)
    {
        //aqui a função vai verificar se o arquivo existe e pode ser baixado




        $documento = LancamentoDocumento ::where('id', $id_arquivo)->first();
        if ($documento) {
            if ($documento->Publico) {
                    return response()->file('../storage/app/arquivos/' . $documento->NomeLocalTimeStamps . '.'.$documento->Ext);
                }
          else {
                return response()->json(['error' => 'Documento não disponível para download.'], 403);
            }
        } else {
            return response()->json(['error' => 'Documento não encontrado.'], 404);
        }

    }
}
