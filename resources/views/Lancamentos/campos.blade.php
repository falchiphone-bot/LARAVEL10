   @csrf
   <div class="card">
       <div class="card-body">
           <div class="col-3">
               <label for="Limite" style="color: black;">Empresas permitidas para o usuário</label>
               <select class="form-control select2" id="EmpresaSelecionada" name="EmpresaSelecionada">
                   <option value="">
                       Selecionar empresa
                   </option>
                   @foreach ($Empresas as $Empresa)
                       {{-- <option @if ($retorno['EmpresaSelecionada'] == $Empresa->ID) selected @endif
                            value="{{ $Empresa->ID }}">

                            {{ $Empresa->Descricao }}
                        </option> --}}

                       <option value="{{ $Empresa->ID }}">
                           {{ $Empresa->Descricao }}
                       </option>
                   @endforeach
               </select>
           </div>
           <div class="col-sm-10">
            <label for="selectHistorico">Histórico</label>
            <select name="Historico" id="selectHistorico" class="form-control" required wire:model="selectHistorico">
                <option value="">Selecione o Histórico</option>
                @foreach ($historicos as $historicoID => $descricao)
                    <option value="{{ $historicoID }}">{{ $descricao }}</option>
                @endforeach
            </select>
        </div>




           <div class="row mt-2">
               <div class="col-6">
                   <button class="btn btn-primary">Salvar</button>
                   <a href="{{ route('Lancamentos.index') }}" class="btn btn-warning">Retornar para lista do plano de
                       contas</a>
               </div>
           </div>
       </div>
   </div>
