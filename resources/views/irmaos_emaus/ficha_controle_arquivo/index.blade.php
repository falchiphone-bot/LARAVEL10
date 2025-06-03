@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-primary text-white">
                Arquivos Anexados à Ficha de Controle #{{ $ficha->id }}
            </div>

            <div class="card-body">
                <p><strong>Nome:</strong> {{ $ficha->Nome }}</p>
                <p><strong>Serviço:</strong> {{ $ficha->idServicos ? $ficha->Irmaos_EmausServicos->nomeServico : 'Sem serviço' }}</p>

                @if($ficha->arquivos->count())
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ficha->arquivos as $arquivo)
                                <tr>
                                    <td>
                                             <a href="{{ asset('storage/' . $arquivo->caminho) }}" target="_blank">
                                                {{ $arquivo->nomeArquivo ?? basename($arquivo->caminho) }}
                                            </a>
                                    </td>

                                    @can('IRMAOS_EMAUS_FICHA_CONTROLE - EXCLUIR_ARQUIVOS')
                                        <td>
                                            <form method="POST" action="{{ route('irmaos_emaus.ficha_controle_arquivo.destroy', $arquivo->id) }}" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este arquivo?')">
                                                    Excluir
                                                </button>
                                            </form>
                                        </td>
                                    @endcan

                                    @can('IRMAOS_EMAUS_FICHA_CONTROLE - EDITAR_ARQUIVOS')
                                        {{-- Botão de Editar --}}
                                        <td>
                                            <a href="{{ route('irmaos_emaus.ficha_controle_arquivo.edit', $arquivo->id) }}" class="btn btn-primary btn-sm">
                                                Editar
                                            </a>
                                        </td>
                                    @endcan


                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Nenhum arquivo anexado a esta ficha.</p>
                @endif
            </div>

            <div class="card-footer">
                <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="btn btn-warning">Voltar para Fichas de Controle</a>
            </div>
        </div>

    </div>
</div>
@endsection
