@include('Layout.Padrao')

<h1 class="text-center">Edição de permissões</h1>
<hr>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{route('TemPermissoes.update', $cadastro->permission_id)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('Model_has_Permissions.campos')
</form>

