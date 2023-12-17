@csrf
<div class="card">
    <div class="card-body">
        <div class="row">

            <div class="col-12">
                <label for="usuario">Nome do usuário/Email</label>
                <input class="form-control @error(' usuario') is-invalid @else is-valid @enderror" name=" usuario" type="text" required id=" usuario" value="{{$WebhookConfig->usuario ?? null}}">
                @error('data')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="identificacaonumerotelefone">Identificação do número de telefone</label>
                <input class="form-control @error('identificacaonumerotelefone') is-invalid @else is-valid @enderror" name="identificacaonumerotelefone" type="text" required id=" identificacaonumerotelefone" value="{{$WebhookConfig-> identificacaonumerotelefone ?? null}}">
                @error('nome')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="telefone">Número de telefone</label>
                <input class="form-control @error('telefone') is-invalid @else is-valid @enderror" name="telefone"
                type="text" required id="telefone" value="{{$WebhookConfig->telefone ?? null}}">
                @error('telefone')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="identificacaocontawhatsappbusiness">Identificação da conta do Whatsapp business</label>
                <input class="form-control @error('identificacaocontawhatsappbusiness') is-invalid @else is-valid @enderror"
                 name="identificacaocontawhatsappbusiness"
                 type="text" required id=" identificacaocontawhatsappbusiness"
                  value="{{$WebhookConfig->identificacaocontawhatsappbusiness ?? null}}">
                @error('nome')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>




            <div class="col-12">
                <label for="token24horas">Token 24 horas</label>
                <textarea id="token24horas" name="token24horas" rows="4" cols="50" class="form-control">
                {{ $WebhookConfig->token24horas ?? NULL }}
                </textarea>
                @error('token24horas')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


            <div class="col-12">
                <label for="tokenpermanenteusuario">Token permanente</label>
                <textarea id="tokenpermanenteusuario" name="tokenpermanenteusuario" rows="4" cols="50" class="form-control">
                {{ $WebhookConfig->tokenpermanenteusuario ?? NULL}}
                </textarea>
                @error('tokenpermanenteusuario')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
               <div class="col-6">
                   <div class="form-check">
                   ATIVADO
                       <label class="form-check-label" style="color: white" for="flexCheckDefault">
                          ATIVADO
                       </label>
                       <input type="hidden" name="ativado" value="0">
                       <input class="form-check-input" name="ativado" type="checkbox"
                           {{ $WebhookConfig->ativado ?? null}} checked  value="1" id="flexCheckDefault">
                   </div>
               </div>
           </div>
            



        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{route('WebhookConfig.index')}}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>
</div>
</div>
