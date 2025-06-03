@extends('layouts.bootstrap5')

@section('content')
<div class="py-5 bg-light">
    <div class="container">

        <div class="card">
            <div class="badge bg-primary text-wrap w-100">
                ENVIAR ARQUIVOS PARA FICHA DE CONTROLE - SERVIÇOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
            </div>

            <div class="row mt-2">
                <div class="col-6">
                    <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="btn btn-warning">
                        Retornar para lista de fichas de controle
                    </a>
                </div>
            </div>

            <div class="row mt-3">
                <div class="card">
                    <div class="card-header bg-warning fw-bold">
                        EXIBIÇÃO DO REGISTRO DE FICHA DE CONTROLE
                    </div>



 @can('IRMAOS_EMAUS_FICHA_CONTROLE - VER_ARQUIVOS')

 <a href="{{ route('irmaos_emaus.ficha_controle_arquivo.index', $cadastro->id) }}" class="btn btn-success" tabindex="-1"
                                            role="button" aria-disabled="true">Gerenciar os Documentos e ou arquivos</a>
@endcan

                    <div class="card-body bg-success-subtle">
                        <label class="form-label text-primary fw-bold">Serviço</label>
                        <p>{{ $cadastro->idServicos ? $cadastro->Irmaos_EmausServicos->nomeServico : 'Sem serviço' }}</p>
                    </div>

                    <div class="card-body bg-secondary-subtle">
                        <label class="form-label text-danger fw-bold">Nome</label>
                        <p>{{ $cadastro->id . ' - ' . $cadastro->Nome }}</p>
                    </div>

                    {{-- Exibição de arquivos já anexados --}}
                    @if($cadastro->arquivos->count())
                        <div class="card-body">
                            <label class="form-label fw-bold">Arquivos anexados:</label>
                            <ul>
                                @foreach($cadastro->arquivos as $arquivo)
                                    <li>
                                        <a href="{{ asset('storage/' . $arquivo->caminho) }}" target="_blank">
                                            {{ basename($arquivo->caminho) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Formulário de envio de arquivos --}}
                    <div class="card-body">
                        <form method="POST" action="{{ route('irmaos_emaus.ficha_controle_arquivo.store', $cadastro->id) }}" enctype="multipart/form-data">

                            @csrf
                            <div class="mb-3">
                                <label for="arquivos" class="form-label">Anexar Arquivos/Imagens</label>
                                <input type="file" name="arquivos[]" multiple class="form-control" accept="image/*,application/pdf">
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar Arquivos</button>
                        </form>
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('Irmaos_Emaus_FichaControle.index') }}">Retornar para a lista</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
