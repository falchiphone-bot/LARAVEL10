@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            <div class="card">
                <div class="badge bg-primary text-wrap" style="width: 100%;">
                    SERVIÇOS DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
                <a href="{{ route('Irmaos_Emaus_FichaControle.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                    aria-disabled="true">Incluir Ficha de controle</a>

                <div class="row">
                    <div class="card">
                        <div class="card-header">
                            EXIBIÇÃO DO REGISTRO DE FICHA DE CONTROLE
                        </div>


                        <div class="card-body" style="background-color: #cfe699;">
                        <label for="Serviço" class="form-label" style="color: #2c0170; font-weight: bold;">Serviço</label>

                        <p>
                            {{ $cadastro->idServicos ? $cadastro->Irmaos_EmausServicos->nomeServico : 'Sem serviço' }}
                        </p>
                    </div>

                    <div class="card-body" style="background-color: #e9ecef;">
                        <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Nome</label>
                        <p>
                            {{ $cadastro->Nome }}
                        </p>
                    </div>

                    <div class="card-body" style="background-color: #f8f9fa;">
                        <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Nascimento</label>
                        <p>
                            {{ isset($cadastro->Nascimento) ? $cadastro->Nascimento->format('d-m-Y') : '' }}
                        </p>
                    </div>

                    <div class="card-body" style="background-color: #e9ecef;">
                        <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Cidade da naturalidade</label>
                        <p>
                            {{ isset($cadastro->CidadeNaturalidade) ? $cadastro->CidadeNaturalidade : '' }}
                        </p>
                    </div>

<div class="card-body" style="background-color: #f8f9fa;">
     <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">UF Cidade da naturalidade</label>
    <p>
        {{ isset($cadastro->UF_Naturalidade) ? $cadastro->UF_Naturalidade : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #e9ecef;">
     <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Mãe</label>
    <p>
        {{ isset($cadastro->Mae) ? $cadastro->Mae : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #f8f9fa;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Pai</label>
    <p>
        {{ isset($cadastro->Pai) ? $cadastro->Pai : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #e9ecef;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Rg</label>
    <p>
        {{ isset($cadastro->Rg) ? $cadastro->Rg : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #f8f9fa;">
       <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Cpf</label>
    <p>
        {{ isset($cadastro->Cpf) ? $cadastro->Cpf : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #e9ecef;">
       <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Nis</label>
    <p>
        {{ isset($cadastro->Nis) ? $cadastro->Nis : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #f8f9fa;">
       <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Escolaridade</label>
    <p>
        {{ isset($cadastro->Escolaridade) ? $cadastro->Escolaridade : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #e9ecef;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Entrada da Primeira Vez</label>
    <p>
        {{ isset($cadastro->EntradaPrimeiraVez) ? $cadastro->EntradaPrimeiraVez->format('d-m-Y') : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #f8f9fa;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Saída da Primeira Vez</label>
    <p>
        {{ isset($cadastro->SaidaPrimeiraVez) ? $cadastro->SaidaPrimeiraVez->format('d-m-Y') : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #e9ecef;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Prontuário</label>
    <p>
        {{ isset($cadastro->Prontuario) ? $cadastro->Prontuario : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #f8f9fa;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Livro</label>
    <p>
        {{ isset($cadastro->Livro) ? $cadastro->Livro : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #e9ecef;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Folha</label>
    <p>
        {{ isset($cadastro->Folha) ? $cadastro->Folha : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #f8f9fa;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Entrada</label>
    <p>
        {{ isset($cadastro->Entrada) ? $cadastro->Entrada->format('d-m-Y') : '' }}
    </p>
</div>

<div class="card-body" style="background-color: #e9ecef;">
      <label for="Serviço" class="form-label" style="color: #ef0808; font-weight: bold;">Saída</label>
    <p>
        {{ isset($cadastro->Saida) ? $cadastro->Saida->format('d-m-Y') : '' }}
    </p>
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

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <script>
        $('form').submit(function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Confirmar!',
                content: 'Confirma a exclusão? Não terá retorno.',
                buttons: {
                    confirmar: function() {
                        // $.alert('Confirmar!');
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar com a exclusão? Não terá retorno.',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    e.currentTarget.submit()
                                },
                                cancelar: function() {
                                    // $.alert('Cancelar!');
                                },

                            }
                        });

                    },
                    cancelar: function() {
                        // $.alert('Cancelar!');
                    },

                }
            });
        });
    </script>
@endpush
