{{-- @can('WHATSAPP - ATENDIMENTO - CANCELAR TRANSFERENCIA ATENDIMENTO') --}}

    <div class="card-body" style="background-color: #add8e6;">
        <form method="GET" action="{{ route('whatsapp.CancelarTransferirAtendimento', $NomeAtendido->id) }}" accept-charset="UTF-8">
            @csrf

            <div class="row">

                <div class="col-sm-6">
                    <button class="btn btn-warning">Cancelar transferência do atendimento</button>
                </div>

            </div>


        </form>


    </div>

{{-- @endcan --}}
