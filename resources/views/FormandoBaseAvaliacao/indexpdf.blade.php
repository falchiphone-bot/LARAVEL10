<div class="py-5 bg-light">
    <div class="container">

        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;font-size: 24px;lign=˜Center˜">
                AVALIAÇÃO DOS FORMANDOS/ATLETAS
            </div>
        </div>


        <div class="card-body">
           

            <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">


                <div class="card-header">
                    <div class="badge bg-info text-wrap" style="width: 100%;font-size: 24px">
                        <p>Total de avaliações cadastrados:
                            {{ $model->count() ?? 0 }}
                        </p>
                    </div>
                </div>



        </div>


        <tbody>
            <table class="table" style="background-color: rgb(247, 247, 213);">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-4">DATA</th>
                        <th scope="col" class="px-6 py-4">NOTA</th>
                        <th scope="col" class="px-6 py-4">NOME</th>
                        <th scope="col" class="px-6 py-4"></th>
                        <th scope="col" class="px-6 py-4"></th>
                        <th scope="col" class="px-6 py-4"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($model as $Model)
                    <tr>


                        <td>{{ \Carbon\Carbon::parse($Model->created_at)->format('d/m/Y') }}</td>

                        <td> {{ number_format($Model->avaliacao,2) }} </td>


                        <td>

                                {{ $Model->MostraFormando->nome }}
                        </td>
                    </tr>
                        @endforeach
                </tbody>
            </table>
            <div class="badge bg-primary text-wrap" style="width: 100%;">
            </div>
    </div>

</div>
<div class="b-example-divider"></div>
</div>
