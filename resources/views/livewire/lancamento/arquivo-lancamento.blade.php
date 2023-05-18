<div>
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent='salvarArquivo'>
                <div class="col-sm-12">
                    <label for="rotulo">Rotulo</label>
                    <input type="text" class="form-control" wire:model.lazy="rotulo" required>
                </div>
                <div class="col-sm-12 mt-3">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="validatedCustomFile" wire:model="arquivos"
                            multiple required>
                        <div class="invalid-feedback">Arquivo inválido</div>
                    </div>
                </div>
                <div wire:loading.remove class="col-sm-12 mt-3">
                    <button type="submit" class="btn btn-primary">Enviar novo arquivo</button>
                </div>
                <div class="col-sm-12 mt-3" wire:loading>
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Processando sua solicitação...
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Rotulo</th>
                        <th scope="col">Por</th>
                        <th scope="col">Data</th>
                        <th scope="col">#</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($files)
                        @foreach ($files as $file)
                            <tr>
                                <th scope="row">{{ $file->ID }}</th>
                                <td>{{ $file->Rotulo }}</td>
                                <td>{{ $file->user->name }}</td>
                                <td>{{ $file->Created->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <button onclick="excluirArquivo({{ $file->ID }})" class="btn btn-danger"><i
                                            class="fa fa-trash" aria-hidden="true"></i></button>
                                    <a href="{{ route('lancamentos.download', $file->ID) }}" target="_blank"
                                        class="btn btn-success"><i
                                            class="fa-solid fa-cloud-arrow-down"></i></i></button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
    </div>
</div>
