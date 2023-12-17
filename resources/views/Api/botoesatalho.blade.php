
@can('WebhookTemplate - LISTAR')
<td>
    <a href="{{ route('Templates.index') }}" class="btn btn-primary" tabindex="-1" role="button"
        aria-disabled="true">Mensagens aprovadas para envio</a>
</td>
@endcan
@can('Webhook(WebhookConfig) - LISTAR')
<td>
    <a href="{{ route('WebhookConfig.index') }}" class="btn btn-success" tabindex="-1" role="button"
        aria-disabled="true">Configurações para Whatsapp</a>
</td>
@endcan

@can('WHATSAPP - MENSAGEMAPROVADA')
<td>
    <a href="{{ route('whatsapp.SelecionarMensagemAprovada') }}" class="btn btn-secondary" tabindex="-1"
        role="button" aria-disabled="true">Selecionar mensagem aprovada para enviar</a>
</td>
<td>
    <a href="{{ route('whatsapp.enviarMensagemAprovada') }}" class="btn btn-danger" tabindex="-1"
        role="button" aria-disabled="true">Agradecimento pelo contato</a>
</td>
<td>
    <a href="{{ route('whatsapp.enviarMensagemAprovadaAriane') }}" class="btn btn-warning" tabindex="-1"
        role="button" aria-disabled="true">Agradecimento pelo contato - ARIANE</a>
</td>
<td>
    <a href="{{ route('whatsapp.enviarMensagemAprovadaAngelica') }}" class="btn btn-primary" tabindex="-1"
        role="button" aria-disabled="true">Agradecimento pelo contato - ANGELICA</a>
</td>
@endcan

 
<td>
    <a href="{{ route('temposessaocontato.temposessao') }}" class="btn btn-secondary" tabindex="-1"
        role="button" aria-disabled="true">Verifica tempo das sessões de usuários</a>
</td>
