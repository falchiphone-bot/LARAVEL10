<div>
    <div class="card">
        <div class="card-header">
            <h4><strong>Edição</strong> de lançamentos</h4>
        </div>
        <div class="card-body">
            <div class="default-tab">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active show" id="nav-lancamento-tab" data-toggle="tab"
                            href="#nav-lancamento" role="tab" aria-controls="nav-lancamento"
                            aria-selected="true">Lançamento</a>
                        <a class="nav-item nav-link" id="nav-comentarios-tab" data-toggle="tab" href="#nav-comentarios"
                            role="tab" aria-controls="nav-comentarios" aria-selected="false">Comentarios</a>
                        <a class="nav-item nav-link" id="nav-arquivos-tab" data-toggle="tab" href="#nav-arquivos"
                            role="tab" aria-controls="nav-arquivos" aria-selected="false">Arquivos</a>
                        <a class="nav-item nav-link" id="nav-trocaempresa-tab" data-toggle="tab"
                            href="#nav-trocaempresa" role="tab" aria-controls="nav-trocaempresa"
                            aria-selected="false">Troca de Empresa</a>
                    </div>
                </nav>
                <div class="tab-content pl-3 pt-2" id="nav-tabContent">
                    <div class="tab-pane fade active show" id="nav-lancamento" role="tabpanel"
                        aria-labelledby="nav-lancamento-tab">
                        <div class="card-body">
                            <div class="form-group col-sm-3">
                                <label for="datacontabilidade" class=" form-control-label">Data Contabilidade</label>
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

                            <div class="form-group col-sm-6">
                                <label for="descricao" class=" form-control-label">Descrição</label>
                                <input type="text" id="descricao" name="Descricao" placeholder=""
                                    class="form-control" value="SAAE Rua: Quatorze, 783 - Sta Fé do Sul">
                                <span class="oculto badge badge-danger">Informação obrigatória</span>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="contadebito" class=" form-control-label">
                                    <a href="/financeiro/contas/extrato/3590">Conta Débito</a>
                                </label>
                                <select id="contadebito" name="ContaDebitoID"
                                    class="form-control required select2 select2-hidden-accessible"
                                    data-select2-id="contadebito" tabindex="-1" aria-hidden="true">
                                    <option value="12182"> MONTAGEM DE REDE DE FIBRA ÓTICA - COSMORAMA</option>
                                    <option value="5892">ZYXEL COMMUNICATIONS DO BRASIL LTDA</option>
                                </select>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="historico" class=" form-control-label">Históricos</label>
                                <select id="historico" name="HistoricoID"
                                    class="form-control select2 select2-hidden-accessible" data-select2-id="historico"
                                    tabindex="-1" aria-hidden="true">
                                    <option value="" data-select2-id="6"></option>
                                    <option value="84"></option>
                                    <option value="26"> ELIMINADO- TRANSF.AUTORIZ.ENTRE C/C MIGUEL MAGOSSI FALCHI
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-comentarios" role="tabpanel"
                        aria-labelledby="nav-comentarios-tab">
                        <div class="form-group col-sm-12">
                            <label for="comentario" class=" form-control-label">Novo comentário</label>
                            <input type="text" id="comentario" name="Comentario" placeholder=""
                                class="form-control">
                            <span class="oculto badge badge-danger">Informação obrigatória</span>
                        </div>

                        <div class="form-group col-sm-12">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-arquivos" role="tabpanel" aria-labelledby="nav-arquivos-tab">
                        <div class="form-group col-sm-6">
                            <label for="rotulo" class=" form-control-label">Rótulo do arquivo</label>
                            <input type="text" id="rotulo" name="Rotulo" placeholder=""
                                class="form-control">
                            <span class="oculto badge badge-danger">Informação obrigatória</span>
                        </div>

                        <div class="form-group col-sm-6">
                            <label for="documento" class=" form-control-label">Selecionar arquivo</label>
                            <input type="file" id="documento" name="Documento" placeholder=""
                                class="form-control">
                            <span class="oculto badge badge-danger">Informação obrigatória</span>
                        </div>

                        <div class="form-group">
                            <div class="card">
                                <div class="card-header"><strong>Arquivos anexados</strong></div>
                                <div class="card-body">
                                    <!-- //documentos importados de contas a pagar -->
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane fade" id="nav-trocaempresa" role="tabpanel"
                        aria-labelledby="nav-trocaempresa-tab">
                        <div class="card">
                            <div class="card-body">
                                <div class="col-md-12">
                                    <label for="tdebito" style="width: 100%;">
                                        <strong>Conta Débito</strong>
                                        <select id="tdebito" style="width: 100%"
                                            class="form-control select2 select2-hidden-accessible" name="tdebito"
                                            data-select2-id="tdebito" tabindex="-1" aria-hidden="true">
                                            <option value="0" data-select2-id="8"></option>
                                            <option value="17598"> CONSORCIO SICREDI PEDRO ROBERTO FALCHI GRUPO 070057
                                                COTA 378-00 CONTRATO 942676 ||| PEDRO ROBERTO FALCHI</option>
                                        </select>
                                </div>

                                <div class="col-md-12 result">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group col-sm-10">
                <a href="/financeiro/contas/extrato/220" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="fa fa-plus-square"></i> Salvar
                </button>
                <button data-idconta="220" data-id="90914" type="button"
                    class="btn btn-secondary btn-sm excluirlancamento">
                    <i class="fa fa-trash-o"></i> Excluir
                </button>
            </div>
            <div class="form-group col-sm-2">
                <input type="hidden" value="0" name="novoregistro" id="novoRegistro">
                <button type="button" id="btnNovo" class="btn btn-secondary btn-sm">
                    <i class="fa fa-plus-square"></i> Salvar como Novo
                </button>
            </div>
        </div>
    </div>
</div>
