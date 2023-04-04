@csrf
<div class="card">
    <div class="card-body">
        <div class="row">


            <div class="col-4">
                <label for="EmpresaID" style="color: black;">Empresas disponíveis</label>
                <select required class="form-control select2" id="EmpresaID" name="EmpresaID"> --}}
                    <option value="">
                        Selecionar empresa
                    </option>
                    @foreach ($empresas as $EmpresasSelecionar)
                        <option @if ($faturamentos ?? null) @if ($faturamentos->EmpresaID == $EmpresasSelecionar->ID) selected @endif
                            @endif
                            value="{{ $EmpresasSelecionar->ID }}">
                            {{ $EmpresasSelecionar->Descricao }}
                        </option>
                    @endforeach


                </select>
            </div>


                {{-- ajustar codigos verificar se ficou com espaços:  value="@if ($faturamentos ?? null){{$faturamentos->data->format('Y-m-d')}}@endif">  --}}

            <div class="col-2">
                <label for="data">Data</label>
                <input required class="form-control @error('data') is-invalid @else is-valid @enderror" name="data"
                    type="date" id="data"
                    value="@if($faturamentos??null){{$faturamentos->data->format('Y-m-d')}}@endif">
                @error('data')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="ValorFaturamento">Valor do faturamento</label>

                <input required class="form-control money @error('ValorFaturamento') is-invalid @else is-valid @enderror"
                    name="ValorFaturamento" type="decimal" id="ValorFaturamento"
                    value="{{ $faturamentos->ValorFaturamento ?? null }}">
                @error('ValorFaturamento')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="PercentualImposto">Percentual do imposto</label>
                <input readonly disabled
                    class="form-control @error('PercentualImposto') is-invalid @else is-valid @enderror"
                    name="PercentualImposto" type="decimal" id="PercentualImposto"
                    value="{{ $faturamentos->PercentualImposto ?? null }}">
                @error('PercentualImposto')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="ValorImposto">Valor do imposto</label>
                <input required class="form-control money @error('ValorImposto') is-invalid @else is-valid @enderror"
                    name="ValorImposto" type="decimal" id="ValorImposto"
                    value="{{ $faturamentos->ValorImposto ?? null }}">
                @error('ValorImposto')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="ValorBaseLucroLiquido">Valor base lucro líquido</label>
                <input readonly disabled
                    class="form-control @error('ValorBaseLucroLiquido') is-invalid @else is-valid @enderror"
                    name="ValorBaseLucroLiquido" type="decimal" id="ValorBaseLucroLiquido"
                    value="{{ $faturamentos->ValorBaseLucroLiquido ?? null }}">
                @error('ValorBaseLucroLiquido')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="PercentualLucroLiquido">Percentual lucro líquido</label>
                <input readonly disabled
                    class="form-control @error('PercentualLucroLiquido') is-invalid @else is-valid @enderror"
                    name="PercentualLucroLiquido" type="decimal" id="PercentualLucroLiquido"
                    value="{{ $faturamentos->PercentualLucroLiquido ?? null }}">
                @error('PercentualLucroLiquido')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-2">
                <label for="LucroLiquido">Lucro líquido</label>
                <input readonly disabled class="form-control @error('LucroLiquido') is-invalid @else is-valid @enderror"
                    name="LucroLiquido" type="decimal" id="LucroLiquido"
                    value="{{ $faturamentos->LucroLiquido ?? null }}">
                @error('LucroLiquido')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-4">
                <label for="LancadoPor">Lancado por</label>
                <input readonly disabled class="form-control @error('LancadoPor') is-invalid @else is-valid @enderror"
                    name="LancadoPor" type="decimal" id="LancadoPor" value="{{ $faturamentos->LancadoPor ?? null }}">
                @error('LancadoPor')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('Faturamentos.index') }}" class="btn btn-warning">Retornar para lista</a>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>
    <script>

        $(document).ready(function() {
            $('.money').mask('000.000.000.000.000,00', {reverse: true});
        });
    </script>
@endpush
