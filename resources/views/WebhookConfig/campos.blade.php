@csrf
<div class="card">
    <div class="card-body">
        <div class="row">

            <div class="col-12">
                <label for="name">Nome do template</label>
                <input class="form-control @error('name') is-invalid @else is-valid @enderror" name="name"
                    type="text" id="name" value="{{$WebhookTemplate->name ?? null}}">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-4">
                <label for="language">Língua do template</label>
                <input class="form-control @error('nome') is-invalid @else is-valid @enderror" name="language"
                    type="text" id="language" value="{{$WebhookTemplate->language ?? null}}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="texto">Texto</label>
                    <textarea id="texto" name="texto" rows="4" cols="50" class="form-control"></textarea>
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('Templates.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
