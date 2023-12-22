@include('Layout.Padrao')

<h1 class="text-center">Empresas - Inclusão</h1>
<hr>
<form method="POST" action="/Empresas" accept-charset="UTF-8">
    @include('Empresas.campos')
</form>
