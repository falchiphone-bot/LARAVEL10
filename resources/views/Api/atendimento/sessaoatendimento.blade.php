
<?php
// Inicia a sessão (se ainda não estiver iniciada)


// Define variáveis
$urlArquivo = '../' . $item->url_arquivoo;
$nomeUsuario = 'PEDRO';

// Armazena as variáveis na sessão
$_SESSION['atendimento'] = $urlArquivo;
$_SESSION['nomeUsuario'] = $nomeUsuario;
?>
