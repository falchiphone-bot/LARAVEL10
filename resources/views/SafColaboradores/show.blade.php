@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalhes do Colaborador</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Nome</dt>
                    <dd class="col-sm-9">{{ $cadastro->nome }}</dd>

                    <dt class="col-sm-3">Representante</dt>
                    <dd class="col-sm-9">{{ optional($cadastro->representante)->nome }}</dd>

                    <dt class="col-sm-3">Função Profissional</dt>
                    <dd class="col-sm-9">{{ optional($cadastro->funcaoProfissional)->nome }}</dd>

                    <dt class="col-sm-3">Tipo de Colaborador</dt>
                    <dd class="col-sm-9">{{ optional($cadastro->tipoPrestador)->nome }}</dd>

                    <dt class="col-sm-3">Faixa Salarial</dt>
                    <dd class="col-sm-9">{{ optional($cadastro->faixaSalarial)->nome }}</dd>

                    <dt class="col-sm-3">Chave PIX</dt>
                    <dd class="col-sm-9">{{ optional($cadastro->pix)->nome }}</dd>

                    <dt class="col-sm-3">Forma de Pagamento</dt>
                    <dd class="col-sm-9">{{ optional($cadastro->formaPagamento)->nome }}</dd>

                    <dt class="col-sm-3">Valor de salário</dt>
                    <dd class="col-sm-9">{{ $cadastro->valor_salario !== null ? number_format($cadastro->valor_salario, 2, ',', '.') : '' }}</dd>

                    <dt class="col-sm-3">Documento</dt>
                    <dd class="col-sm-9">{{ $cadastro->documento }} @if($cadastro->cpf) <span class="text-muted">| CPF:</span> {{ $cadastro->cpf }} @endif</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $cadastro->email }}</dd>

                    <dt class="col-sm-3">Telefone</dt>
                    <dd class="col-sm-9">{{ $cadastro->telefone }}</dd>

                    <dt class="col-sm-3">Cidade / UF / País</dt>
                    <dd class="col-sm-9">{{ $cadastro->cidade }} / {{ $cadastro->uf }} / {{ $cadastro->pais }}</dd>

                    <dt class="col-sm-3">Ativo</dt>
                    <dd class="col-sm-9">{{ $cadastro->ativo ? 'SIM' : 'NÃO' }}</dd>

                    <dt class="col-sm-3">Observações</dt>
                    <dd class="col-sm-9">{{ $cadastro->observacoes }}</dd>
                </dl>

                <a href="{{ route('SafColaboradores.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </div>
    </div>
@endsection
