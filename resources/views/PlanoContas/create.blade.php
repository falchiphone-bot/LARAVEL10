@include('Layout.Padrao')

<h1 class="text-center">Inclusão de contas no plano de contas padrão</h1>
<hr>
<form method="POST" action="/PlanoContas" accept-charset="UTF-8">
    @include('PlanoContas.campos')
</form>
