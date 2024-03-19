@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visualização</title>
</head>
<body>

    <div class="card">
        <div class="card-header">
            <div class="badge bg-primary text-wrap" style="width: 100%;
            ;font-size: 24px;lign=˜Center˜">
                Pesquisar por vencimento - IXC NET RUBI - RECEBER
            </div>
        </div>

    <nav class="navbar navbar-secondary" style="background-color: hsla(244, 92%, 27%, 0.096);">
        <form action="{{ route('ReceberIxc.receberperiodo') }}" method="get">
                <label for="data_vencimento_inicial">Data inicial:</label>
                <input required type="date" id="data_vencimento_inicial" name="data_vencimento_inicial">

                <label for="data_vencimento_final">Data final:</label>
                <input required type="date" id="data_vencimento_final" name="data_vencimento_final">

                <button type="submit" class="btn btn-primary">Pesquisar/selecionar por data</button>
        </form>
</nav>

 </div>
</div>
@endsection
</body>
</html>


