<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .header { display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #ccc; padding-bottom: 8px; margin-bottom: 12px; }
    .header img { height: 36px; }
    .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 11px; color: #666; display: flex; justify-content: space-between; border-top: 1px solid #ccc; padding: 6px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 6px; text-align: left; }
    th { background: #f2f2f2; }
  </style>
</head>
<body>
  <div class="header">
    @php($logoPath = $logoUrl ?? public_path('images/logo.png'))
    @if($logoUrl)
      <img src="{{ $logoUrl }}" alt="logo" />
    @endif
    <div>
      <strong>{{ $headerTitle ?? 'PIX' }}</strong><br>
      @if(!empty($headerSubtitle))<small>{{ $headerSubtitle }}</small>@endif
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Nome</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
        <tr>
          <td>{{ $row->nome }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    <div>{{ $footerLeft }}</div>
    <div>{{ $footerRight }}</div>
  </div>
</body>
</html>
