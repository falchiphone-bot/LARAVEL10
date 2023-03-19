@include('Layout.Padrao')

<h1 class="text-center">Inclusão</h1>
<hr>
<form method="POST" action="/Usuario" accept-charset="UTF-8">
    @include('Users.campos')
</form>
