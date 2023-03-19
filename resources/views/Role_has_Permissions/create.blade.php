@include('Layout.Padrao')

<h1 class="text-center">Inclusão de função tem permissões</h1>
<hr>
<form method="POST" action="/TemFuncoes" accept-charset="UTF-8">
    @include('Role_has_Permissions.campos')
</form>
