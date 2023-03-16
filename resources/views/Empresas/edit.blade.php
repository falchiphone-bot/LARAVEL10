@include('Layout.Padrao')

<h1 class="text-center">Edição da empresa</h1>
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
<form method="POST" action="{{route('Empresas.update',$cadastro->ID)}}" accept-charset="UTF-8">
    <input type="hidden" name="_method" value="PUT">
    @include('Empresas.campos')
</form>

