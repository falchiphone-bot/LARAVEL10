@include('Layout.Padrao')

<h1 class="text-center">Testes iniciais de Laravel - Edição</h1>
<hr>
<form method="POST" action="{{route('Testes.update',$cadastro->id)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('Testes.campos')
</form>

