<div>
    <div class="card">
        <div class="card-header">
            <h4><strong>Edição</strong> de lançamentos | {{ $lancamento->Empresa->Descricao }}</h4>
        </div>
        <div class="card-body">

            <div class="col-sm-12">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($successMsg)
                    <div class="alert alert-success">
                        {{ $successMsg }}
                    </div>
                @endif
            </div>

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="lancamento-tab" data-bs-toggle="tab"
                        data-bs-target="#lancamento" type="button" role="tab" aria-controls="lancamento"
                        aria-selected="true">Lançamento</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="comentarios-tab" data-bs-toggle="tab" data-bs-target="#comentarios"
                        type="button" role="tab" aria-controls="comentarios"
                        aria-selected="false">Comentários</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="arquivos-tab" data-bs-toggle="tab" data-bs-target="#arquivos"
                        type="button" role="tab" aria-controls="arquivos" aria-selected="false">Arquivos</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="troca-empresa-tab" data-bs-toggle="tab" data-bs-target="#troca-empresa"
                        type="button" role="tab" aria-controls="troca-empresa" aria-selected="false">Troca
                        Empresa</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="lancamento" role="tabpanel" aria-labelledby="lancamento-tab">
                    <div class="card-body">
                        <form wire:submit.prevent="salvarLancamento">
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <label for="descricao" class=" form-control-label">Descrição</label>
                                    <input type="text" id="descricao" name="Descricao" placeholder=""
                                        class="form-control" wire:model.lazy='lancamento.Descricao'>
                                    <span class="oculto badge badge-danger">Informação obrigatória</span>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group col-sm-12">
                                            <label for="contadebito" class=" form-control-label">
                                                <a href="">Conta Debito</a>
                                            </label>
                                            <select id="contadebito" wire:model.lazy='lancamento.ContaDebitoID'
                                                name="ContaDebitoID" class="form-control select2">
                                                @foreach ($contas as $ContaID => $ContaDescricao)
                                                    <option value="{{ $ContaID }}">
                                                        {{ $ContaDescricao }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <label for="contacredito" class=" form-control-label">
                                                <a href="">Conta Crédito</a>
                                            </label>
                                            <select id="contacredito" wire:model.lazy='lancamento.ContaCreditoID'
                                                name="ContaCreditoID" class="form-control select2">
                                                @foreach ($contas as $ContaID => $ContaDescricao)
                                                    <option value="{{ $ContaID }}">
                                                        {{ $ContaDescricao }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12">
                                    <label for="historicoID" class=" form-control-label">
                                        Histórico
                                    </label>
                                    <select id="historicoID" name="HistoricoID" class="form-control select2">
                                    </select>
                                </div>

                                <div class="form-group col-sm-3">
                                    <label for="datacontabilidade" class=" form-control-label">Data
                                        Contabilidade</label>
                                    <input type="date" id="datacontabilidade"
                                        class="form-control" wire:model.lazy="lancamento.DataContabilidade">
                                    <span class="oculto badge badge-danger">Informação obrigatória</span>
                                </div>

                                <div class="form-group col-sm-2">
                                    <label for="valor" class=" form-control-label">Valor</label>
                                    <input type="text" id="valor" name="Valor" placeholder="R$"
                                        class="form-control required" wire:model.lazy="lancamento.Valor">
                                    <span class="oculto badge badge-danger">Informação obrigatória</span>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Lancamento</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="comentarios" role="tabpanel" aria-labelledby="comentarios-tab">
                    <div class="card">
                        <div class="card-body">
                            <form wire:submit.prevent='salvarComentario'>
                                <div class="col-sm-12">
                                    <label for="comentario">Inserir novo comentário</label>
                                    <input type="text" class="form-control" wire:model="comentario">
                                </div>
                                <div class="col-sm-12 mt-3">
                                    <button type="submit" class="btn btn-primary">Inserir novo comentário</button>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <p>
                                @foreach ($comentarios as $comentario)
                                    <li>{{ $comentario->Descricao }} <br/>Em {{ $comentario->Created->format('d/m/Y H:i:s') }} | Por: {{ $comentario->user->name }}</li>
                                @endforeach
                            </p>
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade" id="arquivos" role="tabpanel" aria-labelledby="arquivos-tab">
                    codigo de arquivos
                </div>
                <div class="tab-pane fade" id="troca-empresa" role="tabpanel" aria-labelledby="arquivos-tab">
                    @livewire('lancamento.troca-empresa',['lancamento_id'=>$lancamento->ID])
                </div>
            </div>
        </div>
    </div>
</div>
