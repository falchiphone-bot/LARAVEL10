<?php

namespace App\Http\Controllers;

use App\Models\Irmaos_Emaus_FichaControle;
use App\Models\Irmaos_Emaus_FichaControleArquivo;
use App\Models\IrmaosEmausArquivo;
use App\Models\IrmaosEmausFichaControle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Irmaos_Emaus_FichaControleArquivoController extends Controller
{





    public function index(Request $request, $ficha)
    {
        $ficha = Irmaos_Emaus_FichaControle::findOrFail($ficha);
        $arquivos = $ficha->arquivos()->paginate(5);

        return view('irmaos_emaus.ficha_controle_arquivo.index', compact('ficha'));

    }

    public function create($ficha)
    {
        $ficha = Irmaos_Emaus_FichaControle::findOrFail($ficha);
        return view('Irmaos_Emaus_FichaControleArquivo.create', compact('ficha'));
    }
    public function destroy($id)
    {
        $arquivo = Irmaos_Emaus_FichaControleArquivo::findOrFail($id);
        Storage::delete($arquivo->caminho);
        $arquivo->delete();

        return redirect()->back()->with('success', 'Arquivo excluído com sucesso!');
    }


    public function store(Request $request, $ficha)
    {
        $ficha = Irmaos_Emaus_FichaControle::findOrFail($ficha);




        if($request->hasFile('arquivos')) {
            foreach($request->file('arquivos') as $file) {
                $path = $file->store('ficha_controle_arquivos', 'public');

                $ficha->arquivos()->create([
                    'caminho' => $path,
                    'user_created' => auth()->user()->email,
                    'user_updated' => auth()->user()->email,
                ]);
            }
        }

        return redirect()->route('irmaos_emaus.ficha_controle_arquivo.index', $ficha->id)
                        ->with('success', 'Arquivos enviados com sucesso!');
    }


}

