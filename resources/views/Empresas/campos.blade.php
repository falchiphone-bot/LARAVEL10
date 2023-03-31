   @csrf
   <div class="card">
       <div class="card-body" style="background-color: rgb(33, 244, 33)">
           <div class="row">
               <div class="col-6">

                   <label for="nome" style="color: white;">DESCRIÇÃO

                       <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror"
                           name="Descricao" size="70" type="text" id="Descricao"
                           value="{{ $cadastro->Descricao ?? null }}">
                       @error('nome')
                           <div class="alert alert-danger">{{ $message }}</div>
                       @enderror
               </div>

           </div>
           <div class="row">
               <div class="col-6">
                   <label for="nome" style="color: white;">CNPJ

                       <input class="form-control @error('Cnpj') is-invalid @else is-valid @enderror" name="Cnpj"
                           size="30" type="text" id="Cnpj" value="{{ $cadastro->Cnpj ?? null }}">
                       @error('Cnpj')
                           <div class="alert alert-danger">{{ $message }}</div>
                       @enderror
               </div>
           </div>

           <div class="row">
               <div class="col-6">

                   <label for="nome" style="color: white;">INSCRIÇÃO ESTADUAL

                       <input class="form-control" name="Ie" size="30" type="text" id="Ie"
                           value="{{ $cadastro->Ie ?? null }}">
               </div>
           </div>


           <div class="row">
               <div class="col-2">

                   <label for="Bloqueiodataanterior" style="color: black;">Bloqueio datas anteriores</label>
                   <input class="form-control" name="Bloqueiodataanterior" type="date" step="1"
                       id="Bloqueiodataanterior" value="{{ $cadastro->Bloqueiodataanterior->format('Y-m-d') ?? null }}">
               </div>
           </div>

           <div class="row">
               <div class="col-6">
                   <div class="form-check">
                       <label class="form-check-label" style="color: white" for="flexCheckDefault">
                           BLOQUEADA
                       </label>
                       <input type="hidden" name="Bloqueio" value="0">
                       <input class="form-check-input" name="Bloqueio" type="checkbox"
                           @if ($cadastro->Bloqueio) checked @endif value="1" id="flexCheckDefault">
                   </div>
               </div>
           </div>



           <div class="row mt-2">
               <div class="col-6">
                   <button class="btn btn-primary">Salvar</button>
                   <a href="{{ route('Empresas.index') }}" class="btn btn-warning">Retornar para lista de empresas</a>
               </div>
           </div>
       </div>
   </div>
