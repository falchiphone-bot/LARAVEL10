@csrf
<div class="card">
    <div class="card-body">
        <div class="row">

            <div class="col-12">
                <label for="usuario">Nome do usuário/Email</label>
                <input class="form-control @error(' usuario') is-invalid @else is-valid @enderror" name=" usuario"
                    type="text" required id=" usuario" value="{{$WebhookConfig->usuario ?? null}}">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="token24horas">Token 24 horas</label>
                <input class="form-control @error(' token24horas') is-invalid @else is-valid @enderror" name="token24horas"
                    type="text" required id="token24horas" value="{{$WebhookConfig->token24horas ?? null}}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="tokenpermanenteusuario">Token 24 horas</label>
                <input class="form-control @error('tokenpermanenteusuario') is-invalid @else is-valid @enderror" name="tokenpermanenteusuario"
                    type="text" required id="tokenpermanenteusuario" value="{{$WebhookConfig->tokenpermanenteusuario ?? null}}">
                @error('nome')
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
