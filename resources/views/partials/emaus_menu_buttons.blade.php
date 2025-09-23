@canany(['IRMAOS_EMAUS_NOME_SERVICO - LISTAR','IRMAOS_EMAUS_NOME_PIA - LISTAR','IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR'])
<div class="d-flex flex-wrap gap-2">
  @can('IRMAOS_EMAUS_NOME_SERVICO - LISTAR')
    <a class="btn btn-outline-secondary btn-sm" href="/Irmaos_EmausServicos">Serviços</a>
  @endcan
  @can('IRMAOS_EMAUS_NOME_PIA - LISTAR')
    <a class="btn btn-outline-secondary btn-sm" href="/Irmaos_EmausPia">PIA</a>
  @endcan
  @can('IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR')
    <a class="btn btn-outline-secondary btn-sm" href="/Irmaos_Emaus_FichaControle">Ficha Controle</a>
  @endcan
</div>
@endcanany
