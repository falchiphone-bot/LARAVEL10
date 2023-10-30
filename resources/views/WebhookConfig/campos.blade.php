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



            <div class="col-12">
                <label for="token24horas">Token 24 horas</label>
                <textarea id="token24horas" name="token24horas" rows="4" cols="50" class="form-control">
                {{ $WebhookConfig->token24horas }}
                </textarea>
                @error('token24horas')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


            <div class="col-12">
                <label for="tokenpermanenteusuario">Token 24 horas</label>
                <textarea id="tokenpermanenteusuario" name="tokenpermanenteusuario" rows="4" cols="50" class="form-control">
                {{ $WebhookConfig->tokenpermanenteusuario }}
                </textarea>
                @error('tokenpermanenteusuario')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
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
