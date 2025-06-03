@extends('layouts.bootstrap5')

@section('content')
<div class="container py-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            Editar dados do Arquivo na ficha de controle
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('irmaos_emaus.ficha_controle_arquivo.update', $arquivo->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nomeArquivo" class="form-label">Nome do Arquivo</label>
                    <input type="text" name="nomeArquivo" class="form-control" value="{{ old('nomeArquivo', $arquivo->nomeArquivo) }}" required>
                </div>

                <div class="mb-3">
                    <label for="caminho" class="form-label">Arquivo</label>
                    <p>
                        <a href="{{ asset('storage/' . $arquivo->caminho) }}" target="_blank">
                            {{ basename($arquivo->caminho) }}
                        </a>
                    </p>
                </div>

                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection
