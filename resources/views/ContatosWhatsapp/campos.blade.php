@csrf
<div class="card">
    <div class="card-body">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @elseif (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        <div class="row">

            <div class="col-2">
                <label for="created_at">Registrado</label>
                <input class="form-control @error('created_at') is-invalid @else is-valid @enderror" name="created_at"
                    type="text" id="created_at" value="{{  $formattedDate = date('d/m/y H:i', strtotime($model->created_at)) ?? null }} " disabled>
                @error('created_at')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="updated_at">Alterado</label>
                <input class="form-control @error('updated_at') is-invalid @else is-valid @enderror" name="updated_at"
                    type="text" id="updated_at" value="{{  $formattedDate = date('d/m/y H:i', strtotime($model->updated_at)) ?? null }} " disabled>
                @error('updated_at')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-8">
                <label for="user_updated">Alterado por</label>
                <input required class="form-control @error('user_updated') is-invalid @else is-valid @enderror" name="user_updated"
                    type="text" id="user_updated" value="{{ $model->user_updated ?? null }}" disabled>
                @error('user_updated')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-10">
                <label for="contactName">Nome</label>
                <input required class="form-control @error('contactName') is-invalid @else is-valid @enderror" name="contactName"
                    type="text" id="contactName" value="{{ $model->contactName ?? null }}">
                @error('contactName')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-4">
                <label for="recipient_id">Telefone</label>
                <input required class="form-control @error('recipient_id') is-invalid @else is-valid @enderror" name="recipient_id"
                    type="text" id="recipient_id" value="{{ $model->recipient_id ?? null }}">
                @error('recipient_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="quantidade_nao_lida">Quantidade mensagens não lidas</label>
                <input class="form-control @error('quantidade_nao_lida') is-invalid @else is-valid @enderror" name="quantidade_nao_lida"
                    type="text" id="quantidade_nao_lida" value="{{ $model->quantidade_nao_lida ?? null }}">
                @error('quantidade_nao_lida')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-10">
                <label for="user_atendimento">Atendido por</label>
                <input class="form-control @error('user_atendimento') is-invalid @else is-valid @enderror" name="user_atendimento"
                    type="text" id="user_atendimento" value="{{ $model->user_atendimento ?? null }}">
                @error('user_atendimento')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="transferido_para">Transferido para</label>
                <input class="form-control @error('transferido_para') is-invalid @else is-valid @enderror" name="transferido_para"
                    type="text" id="transferido_para" value="{{ $model->user_atendimento ?? null }}">
                @error('transferido_para')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


            <div class="col-2">
                <label for="ultima_entrega">Data da última mensagem entregue</label>
                <input class="form-control @error('ultima_entrega') is-invalid @else is-valid @enderror" name="ultima_entrega"
                    type="text" id="ultima_entrega" value="{{  $formattedDate = date('d/m/y H:i', strtotime($model->ultima_entrega)) ?? null }} " disabled>
                @error('ultima_entrega')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="ultima_leitura">Data da última leitura</label>
                <input class="form-control @error('ultima_leitura') is-invalid @else is-valid @enderror" name="ultima_leitura"
                    type="text" id="ultima_leitura" value="{{  $formattedDate = date('d/m/y H:i', strtotime($model->ultima_leitura)) ?? null }} " disabled>
                @error('ultima_leitura')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>


            <div class="col-2">
                <label for="timestamp">Timestamp</label>
                <input class="form-control @error('timestamp') is-invalid @else is-valid @enderror" name="timestamp"
                    type="text" id="timestamp" value="{{  $model->timestamp ?? null }} ">
                @error('ultima_leitura')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <label class="form-check-label" style="color: green" for="flexCheckDefault">
                           Timestamp de 1 dia atrás:  {{ $umDiaAtras = strtotime('-1 day') }}
                </label>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-check">
                        <label class="form-check-label" style="color: green" for="flexCheckDefault">
                            STATUS DA MENSAGEM ENTREGUE
                        </label>
                        <input type="hidden" name="status_mensagem_entregue" value="0">
                        <input class="form-check-input" name="status_mensagem_entregue" type="checkbox"
                            @if ($model->status_mensagem_entregue) checked @endif value="1" id="flexCheckDefault">
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-6">
                    <div class="form-check">
                        <label class="form-check-label" style="color: red" for="flexCheckDefault">
                            ATUALIZAR PÁGINA NO ATENDIMENTO
                        </label>
                        <input type="hidden" name="pagina_refresh" value="0">
                        <input class="form-check-input" name="pagina_refresh" type="checkbox"
                            @if ($model->pagina_refresh) checked @endif value="1" id="flexCheckDefault">
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <button class="btn btn-primary">Salvar alterações</button>
            <a href="{{ route('ContatosWhatsapp.index') }}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>
</div>
</div>
