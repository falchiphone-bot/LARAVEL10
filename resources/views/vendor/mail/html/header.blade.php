@props(['url'])
<tr>
<td class="header">
<a href="{{ rtrim(config('app.url'), '/') }}" style="display: inline-block;">
@php
	// Usa o host da aplicação e o caminho solicitado: /logo/logo-tec.jpeg
	$logoUrl = rtrim(config('app.url'), '/') . '/logo/logo-tec.jpeg';
@endphp
<img src="{{ $logoUrl }}" class="logo" alt="{{ config('app.name') }} Logo">
</a>
</td>
</tr>
