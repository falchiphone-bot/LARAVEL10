@canany(['CONTABILIDADE - LISTAR','CONTABILIDADE - LISTAR-AQUI-TAMBEM','CONTASPAGAR - LISTAR','COBRANCA - LISTAR','LANCAMENTOS DOCUMENTOS - LISTAR','EMPRESAS - LISTAR','CENTROCUSTOS - LISTAR'])
<div class="d-flex flex-wrap gap-2">
  @can('CONTABILIDADE - LISTAR')
    <a class="btn btn-outline-warning btn-sm" href="/Contabilidade">Contabilidade</a>
  @endcan
  @canany(['COBRANCA - LISTAR','CONTABILIDADE - LISTAR-AQUI-TAMBEM'])
    <a class="btn btn-outline-warning btn-sm" href="/Cobranca">Cobrança</a>
  @endcanany
  @can('CONTASPAGAR - LISTAR')
    <a class="btn btn-outline-warning btn-sm" href="/ContasPagar">Contas a pagar</a>
  @endcan
  @can('LANCAMENTOS DOCUMENTOS - LISTAR')
    <a class="btn btn-outline-warning btn-sm" href="/LancamentosDocumentos">Documentos</a>
  @endcan
  @can('EMPRESAS - LISTAR')
    <a class="btn btn-outline-warning btn-sm" href="/Empresas">Empresas</a>
  @endcan

  @can('CENTROCUSTOS - LISTAR')
    <span class="vr"></span>
    <a class="btn btn-outline-warning btn-sm" href="/CentroCustos">Centro de Custos</a>
    <a class="btn btn-outline-warning btn-sm" href="/ContasCentroCustos">Contas por Centro de Custos</a>
    <a class="btn btn-outline-warning btn-sm" href="{{ route('CentroCustos.dashboard') }}">Dashboard CC</a>
  @endcan
</div>
@endcanany
