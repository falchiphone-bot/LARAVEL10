
<div class="card-body" style="background-color: rgb(50, 241, 101);">


    {{-- ////////////////////////////////////  RECEBIMENTOS --}}


    <hr>

    <table>
        @if ($recebimentoExiste)
            <tr>
                <th>Data</th>
                <th>Recebimento</th>

            </tr>



            @foreach ($FormandoBaseRecebimento as $item)
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }

                    th,
                    td {
                        border: 1px solid black;
                        padding: 8px;
                    }

                    th {
                        background-color: #f2f2f2;
                    }
                </style>


                <tr>
                    <td>{{ $item->data->format('d/m/Y') ?? null }}</td>
                    <td>{{number_format($item->patrocinio ?? null, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td>TOTAL</td>

                <td>{{number_format($TotalRecebido ?? null, 2, ',', '.') }}</td>


            </tr>

        @endif
    </table>


    {{-- //////////////////////////////////// FIM RECEBIMENTOS --}}
</div>

