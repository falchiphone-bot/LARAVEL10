<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Formas de Pagamento</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; }
        th { background: #f2f2f2; text-align: left; }
    </style>
    </head>
<body>
    <h2>Formas de Pagamento</h2>
    <table>
        <thead><tr><th>Nome</th></tr></thead>
        <tbody>
            @forelse($registros as $r)
                <tr><td>{{ $r->nome }}</td></tr>
            @empty
                <tr><td>[vazio]</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
