
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
         </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <button class="btn btn-primary">Salvar inclusão de contato</button>
            <a href="{{ route('ContatosWhatsapp.index') }}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>
</div>
</div>
