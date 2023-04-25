<div>
    <div class="card">
        <div class="card-header">
            <h4><strong>Edição</strong> de lançamentos</h4>
        </div>
        <div class="card-body">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="lancamento-tab" data-bs-toggle="tab" data-bs-target="#lancamento"
                        type="button" role="tab" aria-controls="lancamento"
                        aria-selected="true">Lançamento</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="comentarios-tab" data-bs-toggle="tab" data-bs-target="#comentarios"
                        type="button" role="tab" aria-controls="comentarios" aria-selected="false">Comentários</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="arquivos-tab" data-bs-toggle="tab" data-bs-target="#arquivos"
                        type="button" role="tab" aria-controls="arquivos" aria-selected="false">Arquivos</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="troca-empresa-tab" data-bs-toggle="tab" data-bs-target="#troca-empresa"
                        type="button" role="tab" aria-controls="troca-empresa" aria-selected="false">Troca Empresa</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="lancamento" role="tabpanel" aria-labelledby="lancamento-tab">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-sm-12">
                                <label for="descricao" class=" form-control-label">Descrição</label>
                                <input type="text" id="descricao" name="Descricao" placeholder=""
                                    class="form-control" value="SAAE Rua: Quatorze, 783 - Sta Fé do Sul">
                                <span class="oculto badge badge-danger">Informação obrigatória</span>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group col-sm-12">
                                        <label for="contadebito" class=" form-control-label">
                                            <a href="/financeiro/contas/extrato/3590">Conta Débito</a>
                                        </label>
                                        <select id="contadebito" name="ContaDebitoID" class="form-control select2">
                                            <option value="12182"> MONTAGEM DE REDE DE FIBRA ÓTICA - COSMORAMA
                                            </option>
                                            <option value="5892">ZYXEL COMMUNICATIONS DO BRASIL LTDA</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="contacredito" class=" form-control-label">
                                            <a href="/financeiro/contas/extrato/3590">Conta Crédito</a>
                                        </label>
                                        <select id="contacredito" name="ContaCreditoID" class="form-control select2">
                                            <option value="12182"> MONTAGEM DE REDE DE FIBRA ÓTICA - COSMORAMA
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="historicoID" class=" form-control-label">
                                    Histórico
                                </label>
                                <select id="historicoID" name="HistoricoID" class="form-control select2">
                                    <option value="12182"> MONTAGEM DE REDE DE FIBRA ÓTICA - COSMORAMA
                                    </option>
                                </select>
                            </div>

                            <div class="form-group col-sm-3">
                                <label for="datacontabilidade" class=" form-control-label">Data
                                    Contabilidade</label>
                                <input type="text" id="datacontabilidade" name="DataContabilidade"
                                    class="form-control required dataBusca hasDatepicker" value="13/04/2023">
                                <span class="oculto badge badge-danger">Informação obrigatória</span>
                            </div>

                            <div class="form-group col-sm-3">
                                <label for="valor" class=" form-control-label">Valor</label>
                                <input type="text" id="valor" name="Valor" placeholder="R$"
                                    class="form-control required" value="58,63">
                                <span class="oculto badge badge-danger">Informação obrigatória</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="comentarios" role="tabpanel" aria-labelledby="comentarios-tab">
                    {{-- comentarios --}}
                </div>
                <div class="tab-pane fade" id="arquivos" role="tabpanel" aria-labelledby="arquivos-tab">
                    {{-- codigo de arquivos --}}
                </div>
            </div>
        </div>
    </div>
</div>
