@csrf
<div class="card">
    <div class="card-body">
        <div class="row">


            {{-- <div class="col-sm-6">
                <label for="EmpresaID" style="color: black;">Empresa</label>
                <select required class="form-control select2" id="EmpresaID" name="EmpresaID">
                    <option value="">
                        Selecionar empresa
                    </option>
                    @foreach ($Empresas  as $item)
                        <option @if ($ContasCentroCustos ?? null) @if ($ContasCentroCustos->EmpresaID == $item->ID) selected @endif
                            @endif
                            value="{{ $item->ID }}">
                            {{ $item->Descricao }}
                        </option>
                    @endforeach
                </select>
            </div> --}}


            <div class="col-sm-6">
                <label for="CentroCustoID" style="color: black;">Centro de custos</label>
                <select required class="form-control select2" id="CentroCustoID" name="CentroCustoID">
                    <option value="">
                        Selecionar centro de custos
                    </option>
                    @foreach ($SeleCentroCusto  as $item)
                        <option @if ($ContasCentroCustos ?? null) @if ($ContasCentroCustos->CentroCustoID == $item->ID) selected @endif
                            @endif
                            value="{{ $item->ID }}">
                            {{ $item->Descricao }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div class="col-sm-6">
                <label for="ContaID" style="color: black;">Contas para o centro de custos</label>
                <select required class="form-control select2" id="ContaID" name="ContaID">
                    <option value="">
                        Selecionar contas para o centro de custos
                    </option>
                    @foreach ($seleConta  as $item)
                        <option @if ($ContasCentroCustos ?? null) @if ($ContasCentroCustos->ContaID == $item->ID) selected @endif
                            @endif
                            value="{{ $item->ID }}">
                            {{ $item->Descricao }}
                        </option>
                    @endforeach
                </select>
            </div>



            {{-- <div class="col-6">
                <label for="ContaID">Conta para o centro de custos</label>
                <input class="form-control @error('ContaID') is-invalid @else is-valid @enderror" name="ContaID"
                    type="text" id="ContaID" value="{{$ContasCentroCustos->ContaID??null}}">
                @error('ContaID')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div> --}}


        </div>
        </div>

        <div class="row mt-2">
            <div class="col-6">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{route('ContasCentroCustos.index')}}" class="btn btn-warning">Retornar para lista</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        $('form').submit(function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Confirmar!',
                content: 'Confirma?',
                buttons: {
                    confirmar: function() {
                        // $.alert('Confirmar!');
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar?',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    e.currentTarget.submit()
                                },
                                cancelar: function() {
                                    // $.alert('Cancelar!');
                                },

                            }
                        });

                    },
                    cancelar: function() {
                        // $.alert('Cancelar!');
                    },

                }
            });
        });
    </script>
@endpush
