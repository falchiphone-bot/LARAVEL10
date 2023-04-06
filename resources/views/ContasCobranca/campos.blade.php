   @csrf
   <div class="card">
       <div class="card-body">
           <div class="row">
               <div class="col-4">
                   <label for="EmpresaID">Empresa</label>
                   <select name="EmpresaID" id="EmpresaID" class="form-control" required>
                       <option value="">Selecione a Empresa</option>
                       @foreach ($empresas as $ID => $Descricao)
                           <option @if ($contaCobranca ?? null) @selected($contaCobranca->EmpresaID==$ID) @endif
                               value="{{ $ID }}">{{ $Descricao }}</option>
                       @endforeach
                   </select>
                   @error('EmpresaID')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

               <div class="col-2">
                   <label for="Conta">Conta</label>
                   <input type="number" name="conta" class="form-control" id="Conta"
                       value="{{ $contaCobranca->conta ?? null }}" required>
                   @error('conta')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

               <div class="col-3">
                   <label for="agencia">Agência</label>
                   <input type="number" name="agencia" class="form-control" id="agencia"
                       value="{{ $contaCobranca->agencia ?? null }}" required>
                   @error('agencia')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>


               <div class="col-2">
                   <label for="posto">Posto</label>
                   <input type="number" name="posto" class="form-control" id="posto"
                       value="{{ $contaCobranca->posto ?? null }}" required>
                   @error('posto')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

               <div class="col-12">
                   <label for="associadobeneficiario">Associado Beneficiário</label>
                   <input type="text" name="associadobeneficiario" class="form-control" id="associadobeneficiario"
                       value="{{ $contaCobranca->associadobeneficiario ?? null }}" required>
                   @error('associadobeneficiario')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

               <div class="col-12">
                   <label for="token_conta">Token da Conta</label>
                   <input type="text" name="token_conta" class="form-control" id="token_conta"
                       value="{{ $contaCobranca->token_conta ?? null }}" required>
                   @error('token_conta')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

               <div class="col-12">
                   <label for="idDevSicredi">Contas de Desenvolvedores</label>
                   <select name="idDevSicredi" id="idDevSicredi" class="form-control" required>
                       <option value="">Selecione a conta do Desenvolvedor</option>
                       @foreach ($contasDev as $idDev => $DESENVOLVEDOR)
                           <option
                               @if ($contaCobranca ?? null) @selected($contaCobranca->idDevSicredi==$idDev) @endif
                               value="{{ $idDev }}">{{ $DESENVOLVEDOR }}</option>
                       @endforeach
                   </select>
                   @error('idDevSicredi')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

               <div class="col-12">
                   <label for="selectContaCredito">Histórico para Conta Credito</label>
                   <select name="selectContaCredito" id="selectContaCredito" class="form-control" required>
                       <option value="">Selecione a conta do Desenvolvedor</option>
                       @foreach ($historicos as $historicoID => $descricao)
                           <option value="{{ $historicoID }}">{{ $descricao }}</option>
                       @endforeach
                   </select>
                   @error('selectContaCredito')
                       <div class="alert alert-danger">{{ $message }}</div>
                   @enderror
               </div>

               @if ($historicoContaID)
                   <div class="col-6">
                       <label for="selectContaCredito">Histórico para Conta Credito</label>
                       <select name="selectContaCredito" id="selectContaCredito" class="form-control" required>
                           <option value="">Selecione a conta do Desenvolvedor</option>
                           @foreach ($historicos as $historicoID => $descricao)
                               <option value="{{ $historicoID }}">{{ $descricao }}</option>
                           @endforeach
                       </select>
                       @error('selectContaCredito')
                           <div class="alert alert-danger">{{ $message }}</div>
                       @enderror
                   </div>
                   <div class="col-6">
                       <label for="selectContaCredito">Histórico para Conta Credito</label>
                       <select name="selectContaCredito" id="selectContaCredito" class="form-control" required>
                           <option value="">Selecione a conta do Desenvolvedor</option>
                           @foreach ($historicos as $historicoID => $descricao)
                               <option value="{{ $historicoID }}">{{ $descricao }}</option>
                           @endforeach
                       </select>
                       @error('selectContaCredito')
                           <div class="alert alert-danger">{{ $message }}</div>
                       @enderror
                   </div>
               @endif

           </div>
           <div class="row mt-2">
               <div class="col-6">
                   <button class="btn btn-primary">Salvar</button>
                   <a href="{{ route('ContasCobranca.index') }}" class="btn btn-warning">Retornar para lista de
                       contas</a>
               </div>
           </div>
       </div>
   </div>
