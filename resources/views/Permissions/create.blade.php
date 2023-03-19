@include('Layout.Padrao')

<h1 class="text-center">Inclusão de permissões</h1>
<hr>
<form method="POST" action="/Permissoes" accept-charset="UTF-8">
    @include('Permissions.campos')
</form>
