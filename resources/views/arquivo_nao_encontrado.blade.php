@extends('layouts.bootstrap5')

@section('title', 'Arquivo não encontrado')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="mb-3">
                        Documento: <span class="text-primary">{{ $nome }}</span><br>
                        <small class="text-muted">Rótulo: {{ $rotulo }}</small>
                    </h5>
                    <h1 class="display-4 text-danger">404</h1>
                    <p class="lead">Arquivo não encontrado no servidor.</p>
                    <div class="mb-3">
                        <div class="card border-info mx-auto" style="max-width: 420px;">
                            <div class="card-header bg-info text-white text-center">
                                <strong>Detalhes do Documento</strong>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0 text-start">
                                    <tbody>
                                        <tr>
                                            <th>Nome do arquivo</th>
                                            <td>{{ $nome }}</td>
                                        </tr>
                                        <tr>
                                            <th>Rótulo</th>
                                            <td>{{ $rotulo }}</td>
                                        </tr>
                                        <tr>
                                            <th>Caminho</th>
                                            <td><code>{{ $caminho }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Data da tentativa</th>
                                            <td>{{ $data_tentativa }}</td>
                                        </tr>
                                        @if(!is_null($tamanho))
                                        <tr>
                                            <th>Tamanho</th>
                                            <td>{{ $tamanho }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <p>Verifique se o nome do arquivo está correto.</p>
                    <div class="mt-3">
                        <a href="javascript:history.back()" class="btn btn-secondary">Voltar para a página anterior</a>
                        <a href="/" class="btn btn-link">Ir para a página inicial</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
