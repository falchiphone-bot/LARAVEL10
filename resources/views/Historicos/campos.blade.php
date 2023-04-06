@csrf
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <label for="Descricao">Nome</label>
                <input class="form-control @error('Descricao') is-invalid @else is-valid @enderror" name="Descricao"
                    type="text" id="Descricao" value="{{ $Historicos->Descricao ?? null }}">
                @error('Descricao')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <label for="ContaDebitoID">Conta débito</label>
                <input class="form-control @error('ContaDebitoID') is-invalid @else is-valid @enderror"
                    name="ContaDebitoID" type="text" id="ContaDebitoID"
                    value="{{ $Historicos->ContaDebitoID ?? null }}">
                @error('ContaDebitoID')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <label for="ContaCreditoID">Conta crédito</label>
                <input class="form-control @error('ContaCreditoID') is-invalid @else is-valid @enderror"
                    name="ContaCreditoID" type="text" id="ContaCreditoID"
                    value="{{ $Historicos->ContaCreditoID ?? null }}">
                @error('ContaCreditoID')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('Historicos.index') }}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>

</div>
