<?php
// Teste isolado de upload FTP em PHP
$ftp_host = '186.237.229.252';
$ftp_user = 'arquivos';
$ftp_pass = 'arquivos';
$ftp_port = 21;
$ftp_dir  = 'arquivos'; // pasta de destino no FTP
$local_file = __DIR__ . '/teste_upload.txt';
$remote_file = $ftp_dir . '/teste_upload_' . date('Ymd_His') . '.txt';

// Cria um arquivo de teste
file_put_contents($local_file, "Teste de upload FTP - " . date('c'));

$conn = ftp_connect($ftp_host, $ftp_port, 30);
if (!$conn) {
    die("Erro ao conectar no FTP: $ftp_host\n");
}
if (!ftp_login($conn, $ftp_user, $ftp_pass)) {
    die("Erro ao autenticar no FTP\n");
}
ftp_pasv($conn, true);

// Tenta mudar para o diretório de destino
if (!ftp_chdir($conn, $ftp_dir)) {
    echo "Diretório $ftp_dir não existe, tentando criar...\n";
    if (!ftp_mkdir($conn, $ftp_dir)) {
        die("Não foi possível criar o diretório $ftp_dir\n");
    }
    if (!ftp_chdir($conn, $ftp_dir)) {
        die("Não foi possível acessar o diretório $ftp_dir após criar\n");
    }
}

// Tenta enviar o arquivo
$res = ftp_put($conn, basename($remote_file), $local_file, FTP_ASCII);
if ($res) {
    echo "Upload realizado com sucesso: $remote_file\n";
} else {
    $err = error_get_last();
    echo "Falha no upload!\n";
    echo "Mensagem: " . ($err ? $err['message'] : 'erro desconhecido') . "\n";
    if (function_exists('ftp_raw')) {
        $ftpRaw = ftp_raw($conn, 'NOOP');
        echo "ftp_raw: " . implode(' | ', $ftpRaw) . "\n";
    }
}
ftp_close($conn);
@unlink($local_file);
