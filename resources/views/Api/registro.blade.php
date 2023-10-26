@extends('layouts.bootstrap5')
@section('content')
<div class="py-5 bg-light">
    <div class="container">

        <div class="card">
            <div class="badge bg-primary text-wrap" style="width: 100%;">
                SISTEMA DE GERENCIAMENTO ADMINISTRATIVO E CONTÁBIL
            </div>


            <div class="row">
                <div class="card">
                    <div class="card-footer">
                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                    </div>
<!-- inicio da tabela -->
<!DOCTYPE html>
<html>
<head>
<style>
  table {
    border-collapse: collapse;
    width: 100%;
    border: 1px solid #000; /* Borda da tabela */
  }

  th, td {
    border: 1px solid #000; /* Bordas das células */
    padding: 8px;
  }

  th {
    background-color: #33cc33; /* Cor de fundo do cabeçalho da tabela */
    color: white;
  }
</style>
</head>
<body>

<table>
  <tr>
    <th colspan="2">EXIBIÇÃO DO REGISTRO</th>
  </tr>
  <tr>
    <td>Data registro:</td>
    <td>
      <?php
      $dateString = $model->created_at;
      $dateTime = new DateTime($dateString);
      $formattedDate = $dateTime->format("d/m/Y H:i:s");
      ?>
      {{ $formattedDate }}
    </td>
  </tr>
  <tr>
    <td>Data atualização:</td>
    <td>
      <?php
      $dateString = $model->updated_at;
      $dateTime = new DateTime($dateString);
      $formattedDate = $dateTime->format("d/m/Y H:i:s");
      ?>
      {{ $formattedDate }}
    </td>
  </tr>
  <!-- Adicione mais linhas conforme necessário -->
</table>

<table>
  <tr>
    <th colspan="2">Visualização em JSON</th>
  </tr>
  <tr>
    <td colspan="2">
      <div class="container">
        <div class="row">
          <div class="col-md-6 mx-auto card-container">
            <div class="card border border-danger">
              <div class="card-body bg-light">
                <h5 class="card-title" style="color: #33cc33;">Código de Recebimento</h5>
                <p class="card-text font-weight-bold" style="color: #3366cc;">
                  {{ $model['webhook'] }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </td>
  </tr>
</table>

<table>
  <tr>
    <th colspan="2">Outros Dados</th>
  </tr>
  <tr>
    <td>IDENTIFICAÇÃO DA MENSAGEM:</td>
    <td>{{ $model['messageId'] }}</td>
  </tr>
  <tr>
    <td>Objeto:</td>
    <td>{{ $model['object'] }}</td>
  </tr>
  <tr>
    <td>Identificação do registro:</td>
    <td>{{ $model->entry_id }}</td>
  </tr>
  <tr>
    <td>Tempo da entrada:</td>
    <td>{{ $model->entry_time }}</td>
  </tr>
  <tr>
    <td>Tempo da saída:</td>
    <td>{{ htmlspecialchars($model->messages_Timestamp) }}</td>
  </tr>
  <tr>
    <td>Contexto From:</td>
    <td>{{ $model->context_From }}</td>
  </tr>
  <tr>
    <td>Contexto id:</td>
    <td>{{ $model->context_Id }}</td>
  </tr>
  <tr>
    <td>Produto:</td>
    <td>{{ $model->value_messaging_product }}</td>
  </tr>
  <tr>
    <td>Telefone:</td>
    <td>{{ $model->changes_value_metadata_display_phone_number }}</td>
  </tr>
  <tr>
    <td>Id Telefone:</td>
    <td>{{ $model->changes_value_metadata_display_phone_id }}</td>
  </tr>
  <tr>
    <td>Status banimento:</td>
    <td>{{ $model->changes_value_ban_info_waba_ban_state }}</td>
  </tr>
  <tr>
    <td>Data banimento:</td>
    <td>{{ $model->changes_value_ban_info_waba_ban_date }}</td>
  </tr>
  <tr>
    <td>Contato:</td>
    <td>{{ $model->contactName }}</td>
  </tr>
  <tr>
    <td>Telefone:</td>
    <td>{{ $model->waId }}</td>
  </tr>
  <tr>
    <td>De:</td>
    <td>{{ $model->from }}</td>
  </tr>
  <tr>
    <td>Tipo de arquivo:</td>
    <td>{{ $model->changes_field }}</td>
  </tr>
  <tr>
    <td>Evento:</td>
    <td>{{ $model->event }}</td>
  </tr>
  <tr>
    <td>Id do template:</td>
    <td>{{ $model->message_template_id }}</td>
  </tr>
  <tr>
    <td>Nome do template:</td>
    <td>{{ $model->message_template_name }}</td>
  </tr>
  <tr>
    <td>Língua do template:</td>
    <td>{{ $model->message_template_language }}</td>
  </tr>
  <tr>
    <td>Motivo:</td>
    <td>{{ $model->reason }}</td>
  </tr>
  <tr>
    <td>Tipo da mensagem:</td>
    <td>{{ $model->messageType }}</td>
  </tr>
  <tr>
    <td>Mensagem:</td>
    <td>{{ $model->body ?? $model->caption }}</td>
  </tr>
  <tr>
    <td>Nome do documento:</td>
    <td>{{ $model->filename }}</td>
  </tr>
  <tr>
    <td>Tipo do documento:</td>
    <td>{{ $model->mime_type }}</td>
  </tr>
  <tr>
    <td>Animado:</td>
    <td>{{ $model->animated }}</td>
  </tr>
  <tr>
    <td>Tipo do documento:</td>
    <td>{{ $model->sha256 }}</td>
  </tr>
  <tr>
    <td>Id do documento:</td>
    <td>{{ $model->iddocument }}</td>
  </tr>
  <tr>
    <td>Status da mensagem:</td>
    <td>{{ $model->status }}</td>
  </tr>
</table>

</body>
</html>


<!-- final da tabela -->

                    </div>
                    <div class="card-footer">
                        <a href="{{ route('whatsapp.indexlista') }}">Retornar para a lista</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>






<script>
    $('form').submit(function(e) {
        e.preventDefault();
        $.confirm({
            title: 'Confirmar!',
            content: 'Confirma a exclusão? Não terá retorno.',
            buttons: {
                confirmar: function() {
                    // $.alert('Confirmar!');
                    $.confirm({
                        title: 'Confirmar!',
                        content: 'Deseja realmente continuar com a exclusão? Não terá retorno.',
                        buttons: {
                            confirmar: function() {
                                // $.alert('Confirmar!');
                                e.currentTarget.submit()
                            },
                            cancelar: function() {
                                // $.alert('Cancelar!');
                            },

                        }
                    });

                },
                cancelar: function() {
                    // $.alert('Cancelar!');
                },

            }
        });
    });
</script>
@endpush
