<?php

namespace App\Http\Livewire\Lancamento;

use App\Models\Lancamento;
use App\Models\LancamentoDocumento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ArquivoLancamento extends Component
{
    public $files;
    public $arquivos = [];
    public $rotulo;
    public $lancamentoID;

    public $listeners = ['excluir'];

    public function excluir($id)
    {
        $file = LancamentoDocumento::find($id);
        if (Storage::drive('ftp')->delete($file->Nome . '.' . $file->Ext)) {
            $file->delete();
        } else {
            $this->addError('delete', 'Erro ao deletar arquivo.');
        }
        session()->flash('message', 'Arquivo(s) adicionado.');
        $this->mount($file->LancamentoID);
    }

    public function download($id)
    {
        $download = LancamentoDocumento::find($id);
        if ($download) {
            // dd($download->Nome.'.'.$download->Ext);
            return Storage::disk('ftp')->download($download->Nome.'.'.$download->Ext);
        }else {
            $this->addError('download','Arquivo não localizado para baixar.');
        }
    }

    public function mount($lancamento_id)
    {
        $lancamento = Lancamento::find($lancamento_id);
        if ($lancamento) {
            $this->files = $lancamento->arquivos;
            $this->lancamentoID = $lancamento_id;
        }
    }

    use WithFileUploads;
    public function salvarArquivo()
    {
        $this->validate([
            'arquivos.*' => 'required|max:7168', // 7MB Max
            'rotulo' => 'required',
        ]);
        foreach ($this->arquivos as $arquivo) {
            // dd($arquivo->get());
            // $file = $arquivo->store('lancamentos', 'ftp');

            // $file = $arquivo->disk('google')->store('lancamentos', 'ftp');
            $file = Storage::disk('google')->put($arquivo->getFilename(), $arquivo->get());
            if ($file) {
                $ld = LancamentoDocumento::create([
                    'Rotulo' => $this->rotulo,
                    'LancamentoID' => $this->lancamentoID,
                    'Nome' => explode('.', $arquivo->getFilename())[0],
                    'Created' => date('d-m-Y H:i:s'),
                    'UsuarioID' => Auth::user()->id,
                    'Ext' => explode('.', $arquivo->getFilename())[1],
                ]);
                if (!$ld) {
                    Storage::drive('ftp')->delete($file);
                }
                session()->flash('message', 'Arquivo(s) adicionado.');
            }else {
                $this->addError('upload','Erro ao fazer upload do arquivo.');
            }
        }
        $this->mount($this->lancamentoID);
    }

    public function render()
    {
        return view('livewire.lancamento.arquivo-lancamento');
    }
}
