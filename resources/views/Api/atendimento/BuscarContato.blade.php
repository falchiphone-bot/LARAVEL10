 
<form method="GET" action="{{ route('whatsapp.atendimentoWhatsappBuscar') }}" accept-charset="UTF-8" class="text-center">

    @csrf
    <div class="form-group">
        <div class="badge bg-info text-wrap" style="width: 100%; height: 50%; font-size: 24px;">
            BUSCAR POR NOME EM TODOS CANAIS PERMITIDOS AO USUÁRIO
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <label for="Buscar" style="color: black;">Sequência de texto a pesquisar</label>
                <input class="form-control @error('Buscar') is-invalid @else is-valid @enderror"
                    name="Buscar" size="70" type="text" id="Buscar"
                    value="{{ $retorno['Buscar'] ?? null }}">
            </div>



            <div class="col-3">
                <button class="btn btn-success mx-auto">Filtrar por texto no nome</button>
            </div>
        </div>
    </div>
</form>

