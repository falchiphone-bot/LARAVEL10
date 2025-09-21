<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class FtpHelper
{
    /**
     * Cria diretórios aninhados no FTP, se não existirem.
     * @param resource $ftpConn Conexão FTP
     * @param string $remotePath Caminho do diretório remoto (ex: arquivos/subpasta)
     */
    public static function ensureFtpDirectories($ftpConn, $remotePath)
    {
        $dirs = explode('/', trim($remotePath, '/'));
        // Sempre começa da raiz
        @ftp_chdir($ftpConn, '/');
        foreach ($dirs as $dir) {
            if ($dir === '' || $dir === '.') continue;
            $cwdResult = @ftp_chdir($ftpConn, $dir);
            Log::info("ftp_chdir('$dir') => ", ['result' => $cwdResult]);
            if (!$cwdResult) {
                $mkdirResult = @ftp_mkdir($ftpConn, $dir);
                Log::info("ftp_mkdir('$dir') => ", ['result' => $mkdirResult]);
                if (!$mkdirResult) {
                    Log::error("Falha ao criar diretório FTP: $dir");
                    throw new \Exception("Falha ao criar diretório FTP: $dir");
                }
                // Após criar, navega para ele
                $cwdResult2 = @ftp_chdir($ftpConn, $dir);
                Log::info("ftp_chdir('$dir') após mkdir => ", ['result' => $cwdResult2]);
                if (!$cwdResult2) {
                    Log::error("Falha ao navegar para diretório recém-criado: $dir");
                    throw new \Exception("Falha ao navegar para diretório recém-criado: $dir");
                }
            }
        }
        // Volta para raiz ao final
        $chdirRoot = ftp_chdir($ftpConn, '/');
        Log::info("ftp_chdir('/') => ", ['result' => $chdirRoot]);
    }

    /**
     * Faz upload de um arquivo, criando diretórios se necessário.
     * Usa configs do Laravel se $ftpConn for null.
     * @param resource|null $ftpConn Conexão FTP já aberta, ou null para abrir via config
     * @param string $localFile Caminho local
     * @param string $remoteFile Caminho remoto (ex: arquivos/teste.txt)
     * @param array|null $config Config FTP (host, user, pass, port, passive)
     */
    public static function uploadFile($ftpConn, $localFile, $remoteFile, $config = null)
    {
        $closeAfter = false;
        if (!$ftpConn) {
            $config = $config ?: config('filesystems.ftp');
            $ftpConn = ftp_connect($config['host'], $config['port'] ?? 21, $config['timeout'] ?? 90);
            if (!$ftpConn) {
                Log::error('Falha ao conectar ao FTP: ' . $config['host']);
                throw new \Exception('Falha ao conectar ao FTP: ' . $config['host']);
            }
            if (!ftp_login($ftpConn, $config['username'], $config['password'])) {
                Log::error('Falha ao autenticar no FTP');
                throw new \Exception('Falha ao autenticar no FTP');
            }
            if (!empty($config['passive'])) {
                ftp_pasv($ftpConn, true);
            }
            $closeAfter = true;
            Log::info('Conexão FTP aberta via config Laravel.');
        }

        // Padroniza caminho remoto para sempre começar com '/'
        $remoteFileAbs = '/' . ltrim($remoteFile, '/');
        $remoteDir = dirname($remoteFileAbs);
        self::ensureFtpDirectories($ftpConn, $remoteDir);

        // Verifica se o arquivo já existe no FTP e se é idêntico (hash MD5 se possível, senão tamanho)
        $ftpSize = @ftp_size($ftpConn, $remoteFileAbs);
        $localSize = @filesize($localFile);
        $localMd5 = @md5_file($localFile);
        $remoteMd5 = null;
        $isIdentical = false;
        Log::info("ftp_size('$remoteFileAbs') => ", ['size' => $ftpSize, 'local_size' => $localSize]);
        if ($ftpSize > -1 && $ftpSize === $localSize) {
            // Tenta obter hash MD5 remoto via comando XMD5/MD5
            $md5Resp = @ftp_raw($ftpConn, "XMD5 $remoteFileAbs");
            if (!$md5Resp || !is_array($md5Resp) || stripos($md5Resp[0], '502') !== false) {
                // Tenta comando alternativo MD5
                $md5Resp = @ftp_raw($ftpConn, "MD5 $remoteFileAbs");
            }
            if ($md5Resp && is_array($md5Resp) && preg_match('/([a-fA-F0-9]{32})/', $md5Resp[0], $matches)) {
                $remoteMd5 = $matches[1];
                Log::info("MD5 remoto de $remoteFileAbs: $remoteMd5");
                if ($remoteMd5 === $localMd5) {
                    $isIdentical = true;
                }
            } else {
                Log::info("Não foi possível obter MD5 remoto, comparando apenas tamanho.");
                $isIdentical = true; // fallback: mesmo tamanho
            }
        }
        if ($isIdentical) {
            Log::info("Arquivo já existe e é idêntico (hash MD5 ou tamanho), não será reenviado: $remoteFileAbs", [
                'local_md5' => $localMd5,
                'remote_md5' => $remoteMd5
            ]);
            if ($closeAfter) {
                $closeResult = ftp_close($ftpConn);
                Log::info('Conexão FTP fechada.', ['result' => $closeResult]);
            }
            return;
        }

        Log::info("Iniciando upload FTP: $localFile => $remoteFileAbs");
        $putResult = ftp_put($ftpConn, $remoteFileAbs, $localFile, FTP_BINARY);
        Log::info("ftp_put('$remoteFileAbs', '$localFile', FTP_BINARY) => ", ['result' => $putResult]);
        if (!$putResult) {
            Log::error("Falha ao enviar arquivo para FTP: $remoteFileAbs");
            if ($closeAfter) ftp_close($ftpConn);
            throw new \Exception("Falha ao enviar arquivo para FTP: $remoteFileAbs");
        }
        Log::info("Upload FTP realizado com sucesso: $remoteFileAbs");

        if ($closeAfter) {
            $closeResult = ftp_close($ftpConn);
            Log::info('Conexão FTP fechada.', ['result' => $closeResult]);
        }
    }
}
