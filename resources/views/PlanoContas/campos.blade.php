   @csrf
   <div class="card">
       <div class="card-body">

           @can('AGRUPAMENTOS CONTAS - LISTAR')
                    <a href="{{ route('AgrupamentosContas.index') }}" class="btn btn-primary btn-lg enabled" tabindex="-1" role="button"
                        aria-disabled="true">Agrupamento de conta</a>
           @endcan
           <div class="row">
               <div class="col-6">
                   <label for="nome">DESCRIÇÃO</label>
                   <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Descricao" type="text" id="Descricao" value="{{$cadastro->Descricao??null}}">
                   @error('nome')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

           </div>

           <div class="row">
               <div class="col-6">
                   <label for="Codigo">Código</label>
                   <input class="form-control @error('Codigo') is-invalid @else is-valid @enderror" name="Codigo" type="text" id="Codigo" value="{{$cadastro->Codigo??null}}">
                   @error('Codigo')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>

           <div class="row">
               <div class="col-6">
                   <label for="Grau">Grau</label>
                   <input class="form-control @error('Grau') is-invalid @else is-valid @enderror" name="Grau" type="text" id="Grau" value="{{$cadastro->Grau??null}}">
                   @error('Grau')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>
           <div class="row">
               <div class="col-6">
                   <label for="CodigoSkala">Código Skala</label>
                   <input class="form-control @error('CodigoSkala') is-invalid @else is-valid @enderror" name="CodigoSkala" type="text" id="CodigoSkala" value="{{$cadastro->CodigoSkala??null}}">
                   @error('CodigoSkala')
                   <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>
           </div>

           <div class="form-group">
               <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
                   AGRUPAMENTO
               </div>
               <select required class="form-control select2" id="Agrupamento" name="Agrupamento">
                   <option value="">Selecionar</option>
                   @foreach ($Agrupamentos as $item)
                   <option @if ($item->id == $cadastro->Agrupamento) selected @endif
                       value="{{ $item->id }}">
                       {{ $item->nome }}
                   </option>
                   @endforeach
               </select>

           </div>




           <div class="row mt-2">
               <div class="col-6">
                   <button class="btn btn-primary">Salvar</button>
                   <a href="{{route('PlanoContas.index')}}" class="btn btn-warning">Retornar para lista do plano de contas</a>
               </div>
           </div>
       </div>
   </div>
