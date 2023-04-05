@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <label for="nome">DESENVOLVEDOR</label>
                <input required class="form-control @error('DESENVOLVEDOR') is-invalid @else is-valid @enderror"
                    name="DESENVOLVEDOR" type="text" id="DESENVOLVEDOR" value="{{ $DevSicredi->DESENVOLVEDOR ?? null }}">
                @error('nome')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

         

        <div>
            <label for="nome">SICREDI_CLIENT_ID</label>
            <input required class="form-control @error('SICREDI_CLIENT_ID') is-invalid @else is-valid @enderror"
                name="SICREDI_CLIENT_ID" type="text" id="SICREDI_CLIENT_ID"
                value="{{ $DevSicredi->SICREDI_CLIENT_ID ?? null }}">
            @error('SICREDI_CLIENT_ID')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

        </div>

        <div>
            <label for="nome">SICREDI_CLIENT_SECRET</label>
            <input required class="form-control @error('SICREDI_CLIENT_SECRET') is-invalid @else is-valid @enderror"
                name="SICREDI_CLIENT_SECRET" type="text" id="SICREDI_CLIENT_SECRET"
                value="{{ $DevSicredi->SICREDI_CLIENT_SECRET ?? null }}">
            @error('SICREDI_CLIENT_SECRET')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

        </div>

        <div>
            <label for="nome">SICREDI_TOKEN</label>
            <input required class="form-control @error('SICREDI_TOKEN') is-invalid @else is-valid @enderror"
                name="SICREDI_TOKEN" type="text" id="SICREDI_TOKEN"
                value="{{ $DevSicredi->SICREDI_TOKEN ?? null }}">
            @error('SICREDI_TOKEN')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

        </div>

        <div>
            <label for="nome">URL_API</label>
            <input required class="form-control @error('URL_API') is-invalid @else is-valid @enderror"
                name="URL_API" type="text" id="URL_API"
                value="{{ $DevSicredi->URL_API ?? null }}">
            @error('URL_API')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('DevSicredi.index') }}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>
