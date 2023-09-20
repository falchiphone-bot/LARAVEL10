   @csrf
   <div class="card">
       <div class="card-body">
           CONTAS A PAGAR DO SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL - INCLUSÃO
           <div class="row">
               <div class="col-sm-12">
                   <label for="EmpresaID" style="color: black;">Empresa</label>
                   <select required class="form-control select2" id="ContaFornecedorID" name="EmpresaID">
                       <option value="">Selecionar empresa</option>

                       @foreach ($Empresas as $item)
                       <option @if ($item->ID == $contasPagar->EmpresaID) selected @endif
                           value="{{ $item->ID }}">
                           {{ $item->Descricao }}
                       </option>
                       @endforeach
                   </select>
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


           <div class="col-sm-12">
               <label for="ContaFornecedorID" style="color: black;">Contas DÉBITO</label>
               <select required class="form-control select2" id="ContaFornecedorID" name="ContaFornecedorID">
                   <option value="">Selecionar contas DÉBITO</option>

                   @foreach ($ContaFornecedor as $item)
                   <option value="{{ $item->ID }}">
                       {{ $item->Descricao }}
                   </option>
                   @endforeach

               </select>
           </div>

           <div class="col-sm-12">
               <label for="ContaPagamentoID" style="color: black;">Contas CRÉDITO</label>
               <select required class="form-control select2" id="ContaPagamentoID" name="ContaPagamentoID">
                   <option value="">Selecionar contas CRÉDITO</option>
                   @if ($ContaPagamento)
                   @foreach ($ContaPagamento as $item)
                   <option value="{{ $item->ID }}">
                       {{ $item->Descricao }}
                   </option>
                   @endforeach
                   @endif
               </select>
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
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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


       $(document).ready(function() {
           // Verifique se há valores nos campos e armazene-os no localStorage
           $('form').on('submit', function() {
               // Especifique os nomes dos campos que você deseja manter
               var camposParaManter = ["ID","EmpresaID","NumTitulo","Descricao", "Valor", "DataProgramacao", "DataVencimento", "DataDocumento", "ContaFornecedorID", "ContaPagamentoID"];



               camposParaManter.forEach(function(nomeDoCampo) {
                   var inputValue = $('[name="' + nomeDoCampo + '"]').val();
                   localStorage.setItem(nomeDoCampo, inputValue);
               });
           });

           // Verifique se há valores armazenados no localStorage e preencha os campos
           var camposParaManter = ["ID","EmpresaID","NumTitulo","Descricao", "Valor", "DataProgramacao", "DataVencimento", "DataDocumento", "ContaFornecedorID", "ContaPagamentoID"];

           camposParaManter.forEach(function(nomeDoCampo) {
               var storedValue = localStorage.getItem(nomeDoCampo);
               if (storedValue !== null) {
                   $('[name="' + nomeDoCampo + '"]').val(storedValue);
               }
           });
       });
   </script>
   @endpush
