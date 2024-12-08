@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">
        {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Conta</a></li>
              <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
          </nav> --}}

        <div class="card">
            <div class="card-header">
                <div class="badge bg-success text-wrap" style="width: 100%;">
                    CONTAS A PAGAR PARA O SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
                </div>
            </div>

            @can('CONTASPAGAR - INCLUIR')
            <a href="{{ route('ContasPagar.create') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button" aria-disabled="true">Incluir Contas a pagar </a>
            @endcan


            <style>
  /* Estilo para o fundo azul claro da tabela */
  table {
    background-color: #e6f7ff; /* Azul claro (substitua pela cor desejada) */
    width: 100%; /* 100% de largura para preencher a largura da página */
  }
</style>
            <table class="table">
  <tr>
    <td>
      @can('LANCAMENTOS DOCUMENTOS - LISTAR')
      <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
        <a class="btn btn-success" href="/LancamentosDocumentos">Enviar documentos</a>
      </nav>
      @endcan
    </td>
    <td>
      @can('CONTABILIDADE - LISTAR')
      <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
        <a class="btn btn-primary" href="/Contabilidade">Contabilidade</a>
      </nav>
      @endcan
    </td>
  </tr>
</table>





            <div class="card-body">
                <a href="/dashboard" class="btn btn-warning">Retornar para opções anteriores</a>

                @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @elseif (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif
                <div class="card-header mix-blend-color-burn">
                    <p>Total de Contas a pagar: {{ $contasPagar->count() }}</p>
                </div>
                <div class="card-header">
                    <div class="badge bg-warning text-wrap" style="width: 100%;">

                    </div>
                </div>


            </div>


            @can('CONTASPAGAR - ALTERARVALORMULTIPLOS')
            <form method="POST" action="{{ route('contaspagar.alterarvalormultiplos') }}" accept-charset="UTF-8">
                @csrf

                <div class="card-body" style="background-color: rgb(15, 187, 240)">
                    <div class="row">
                        <div class="col-6">
                            <label for="ValorAlterar">Alterar todos estes valores para o abaixo:</label>

                            <input class="form-control money @error('ValorAlterar') is-invalid @else is-valid @enderror" name="ValorAlterar" type="text" id="ValorAlterar"  }}">
                            @error('ValorAlterar')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-6">
                            <label for="Valor">Alterar todos os valores acima para este abaixo:</label>

                            <input class="form-control money @error('Valor') is-invalid @else is-valid @enderror" name="Valor" type="text" id="Valor"  }}">
                            @error('Valor')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <button class="btn btn-danger">Alterar todos valores conforme acima:</button>
                        </div>
                    </div>
                </div>
                                    <!-- Iterar sobre o array e incluir cada valor como um campo hidden -->
                        @foreach($contasPagar as $index => $conta)
                              <input type="hidden" name="contasPagar[{{ $index }}]" value="{{ $conta }}">


                        @endforeach

            </form>
            @endcan

            <form method="POST" action="{{ route('contaspagar.index.post') }}" accept-charset="UTF-8">
                @csrf

                <div class="card">
                    <div class="card-body" style="background-color: rgb(33, 244, 33)">
                        <div class="row">
                            <div class="col-6">

                                <label for="Texto" style="color: black;">Texto a pesquisar</label>
                                <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Texto" size="70" type="text" id="Texto" value="{{ $retorno['Texto'] ?? null }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-2">

                                <label for="Valor" style="color: black;">Valor a pesquisar(>=)</label>
                                <input class="form-control @error('Valor') is-invalid @else is-valid @enderror" name="Valor" size="30" type="number" step="0.01" id="Valor" value="{{ $retorno['Valor'] ?? null }}">
                            </div>

                            <div class="col-2">

                                <label for="DataInicial" style="color: black;">Data inicial</label>
                                <input class="form-control @error('DataInicial') is-invalid @else is-valid @enderror" name="DataInicial" size="30" type="date" step="1" id="DataInicial" value="{{ $retorno['DataInicial'] ?? null }}">
                            </div>

                            <div class="col-2">

                                <label for="DataFinal" style="color: black;">Data final</label>
                                <input class="form-control @error('DataFinal') is-invalid @else is-valid @enderror" name="DataFinal" size="30" type="date" step="1" id="DataFinal" value="{{ $retorno['DataFinal'] ?? null }}">
                            </div>

                            <div class="col-3">

                                <label for="Limite" style="color: black;">Limite de registros para retorno</label>
                                <input class="form-control @error('limite') is-invalid @else is-valid @enderror" name="Limite" size="30" type="number" step="1" id="Limite" value="{{ $retorno['Limite'] ?? null }}">
                            </div>


                            <div class="col-3">
                                <label for="Limite" style="color: black;">Empresas permitidas para o usuário</label>
                                <select class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                                    <option value="">
                                        Selecionar empresa
                                    </option>
                                    @foreach ($Empresas as $Empresa)
                                    <option @if ($retorno['EmpresaSelecionada']==$Empresa->ID) selected @endif
                                        value="{{ $Empresa->ID }}">

                                        {{ $Empresa->Descricao }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <button class="btn btn-primary">Pesquisar conforme informações constantes do
                                    formulário</button>

                            </div>
                        </div>
                    </div>

                </div>
            </form>





            <table class="table">

                <tr>
                    <th scope="col" class="px-6 py-4">Empresa</th>

                    <th scope="col" class="px-6 py-4">Programado/Contabilidade</th>
                    <th scope="col" class="px-6 py-4">Valor</th>
                    <th scope="col" class="px-6 py-4">Conta Pagar</th>
                    <th scope="col" class="px-6 py-4">Conta de pagamento</th>
                    <th scope="col" class="px-6 py-4">Vencimento</th>
                    <th scope="col" class="px-6 py-4">Data do documento </th>
                    <th scope="col" class="px-6 py-4">Contabilidade</th>

                </tr>


                @foreach ($contasPagar as $conta)

                <tr>
                    <td class="">
                        {{ $conta->Empresa->Descricao }}
                    </td>

                    <td class="">
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $conta->DataProgramacao)->format('d/m/Y') }}

                    </td>
                    <td class="text-end">
                        {{ number_format($conta->Valor, 2, ',', '.') }}
                    </td>
                    </td>
                    <td class="">
                        {{ $conta->ContaDebito->PlanoConta->Descricao}}
                    </td>

                    <td class="">
                        {{ $conta->ContaCredito->PlanoConta->Descricao }}
                    </td>
                    <td class="">
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $conta->DataVencimento)->format('d/m/Y') }}

                    </td>
                    <td class="">
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', substr($conta->DataDocumento, 0, 10))->format('d/m/Y') }}
                    </td>
                    <td class="">
                        {{ $conta->LancamentoID }}
                    </td>
                    <td>
                        @can('CONTASPAGAR - EDITAR')
                        <a href="{{ route('ContasPagar.edit', $conta->ID) }}" class="btn btn-success" tabindex="-1" role="button" aria-disabled="true" target="_blank">Editar</a>
                        @endcan

                        @if ($conta->LancamentoID == 0 || $conta->LancamentoID == null)
                        @can('CONTASPAGAR - INCLUIRLANCAMENTO')
                        <a href="{{ route('contaspagar.IncluirLancamentoContasPagar', $conta->ID) }}" class="btn btn-warning" tabindex="-1" role="button" aria-disabled="true">Lançar contabilidade</a>
                        @endcan
                        @endif



                    </td>
                </tr>



                @endforeach

            </table>

        </div>

    </div>
    <div class="b-example-divider"></div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });

    $('form').submit(function(e) {
        e.preventDefault();
        $.confirm({
            title: 'Confirmar!',
            content: 'Confirma?',
            buttons: {
                confirmar: function() {
                    // $.alert('Confirmar!');
                    $.confirm({
                        title: 'Confirmar!',
                        content: 'Deseja realmente continuar?',
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>
<script>
    $(document).ready(function() {
        $('.money').mask('000.000.000.000.000,00', {
            reverse: true
        });
    });
</script>

@endpush
