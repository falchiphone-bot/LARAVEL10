   @csrf
   <div class="card">
       <div class="card-body">

           <div class="row">
               <div class="col-12">
                   <label for="EmpresaID" Empresa</label>
                       <input class="form-control @error('EmpresaID') is-invalid @else is-valid @enderror" name="EmpresaID" type="int" id="EmpresaID" value="{{$contasPagar->Empresa->Descricao??null}}" disabled>
                       @error('EmpresaID')
                       <div class="alert alert-danger">{{ $message }}</div>
                       @enderror
               </div>
           </div>

           <div class="row">
               <div class="col-12">
                   <label for="nome">DESCRIÇÃO</label>
                   <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Descricao" type="text" id="Descricao" value="{{$contasPagar->Descricao??null}}">
                   @error('nome')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>

           <div class="row">
               <div class="col-6">
                   <label for="NumTitulo">TITULO</label>
                   <input class="form-control @error('NumTitulo') is-invalid @else is-valid @enderror" name="NumTitulo" type="text" id="NumTitulo" value="{{$contasPagar->NumTitulo??null}}">
                   @error('nome')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>

           <div class="row">
               <div class="col-6">
                   <label for="Valor">VALOR</label>
                   <input class="form-control @error('Valor') is-invalid @else is-valid @enderror" name="Valor" type="text" id="Valor" value="{{$contasPagar->Valor??null}}">
                   @error('Valor')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>
           <div class="row">
               <div class="col-6">
                   <label for="DataProgramacao">Data programação/contabilidade</label>
                   <input class="form-control @error('DataProgramacao') is-invalid @else is-valid @enderror" name="DataProgramacao" type="date" id="DataProgramacao" value="{{$contasPagar->DataProgramacao??null}}">
                   @error('DataProgramacao')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>
           <div class="row">
               <div class="col-6">
                   <label for="DataVencimento">Data vencimento</label>
                   <input class="form-control @error('DataVencimento') is-invalid @else is-valid @enderror" name="DataVencimento" type="date" id="DataVencimento" value="{{$contasPagar->DataVencimento??null}}">
                   @error('DataVencimento')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>

           <div class="row">
               <div class="col-6">
                   <label for="DataDocumento">Data documento</label>
                   <input class="form-control @error('DataDocumento') is-invalid @else is-valid @enderror" name="DataDocumento" type="date" id="DataDocumento" value="{{$contasPagar->DataDocumento??null}}">
                   @error('DataDocumento')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>

           <div class="row">
               <div class="col-6">
                   <label for="ContaFornecedorID" Conta débito</label>
                       <input class="form-control @error('ContaFornecedorID') is-invalid @else is-valid @enderror" name="ContaFornecedorID" type="int" id="ContaFornecedorID" value="{{$contasPagar->ContaFornecedorID??null}}">
                       @error('ContaFornecedorID')
                       <div class="alert alert-danger">{{ $message }}</div>
                       @enderror
               </div>
           </div>

           <div class="col-sm-12">
               <label for="ContaFornecedorID" style="color: black;">Contas DÉBITO</label>
               <select required class="form-control select2" id="ContaFornecedorID" name="ContaFornecedorID">
                   <option value="">Selecionar contas DÉBITO</option>
                   @if ($ContaDebito)
                        @foreach ($ContaDebito as $item)
                        <option @if ($contasPagar->ContaFornecedorID == $item->Planocontas_id) selected @endif
                            value="{{ $item->Planocontas_id }}">
                            {{ $item->Descricao }}
                        </option>
                        @endforeach
                   @endif
               </select>
           </div>




           <div class="row">
               <div class="col-6">
                   <label for="ContaPagamentoID" Conta Crédito</label>
                       <input class="form-control @error('ContaPagamentoID') is-invalid @else is-valid @enderror" name="ContaPagamentoID" type="int" id="ContaPagamentoID" value="{{$contasPagar->ContaPagamentoID??null}}">
                       @error('ContaFornecedorID')
                       <div class="alert alert-danger">{{ $message }}</div>
                       @enderror
               </div>
           </div>




           <div class="row mt-2">
               <div class="col-6">

                   <button class="btn btn-primary">Salvar</button>
                   <a href="{{route('ContasPagar.index')}}" class="btn btn-warning">Retornar para lista de contas</a>
               </div>
           </div>
       </div>
   </div>
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
    @endpush
