@csrf
    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="col-6">
                    <label for="nome">Data para bloquear</label>
                    <input class="form-control"
                        type="date" name="Bloqueardataanterior"}">
                    @error('nome')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>


            <div class="row mt-2">
                <div class="col-6">
                    <button class="btn btn-danger">Executar os bloqueios das empresas</button>
                </div>

            </div>
        </div>
    </div>
