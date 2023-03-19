@include('Layout.Padrao')

<h1 class="text-center">Inclusão de o que tem permissões</h1>
<hr>
<form method="POST" action="/TemPermissoes" accept-charset="UTF-8">
    @include('Model_has_Permissions.campos')
</form>
