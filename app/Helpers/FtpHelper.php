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
        $path = '';
        foreach ($dirs as $dir) {
            $path .= '/' . $dir;
            $cwdResult = @ftp_chdir($ftpConn, $path);
            Log::info("ftp_chdir('$path') => ", ['result' => $cwdResult]);
            if ($cwdResult) {
                Log::info("Diretório FTP já existe: $path");
                continue;
            }
            $mkdirResult = @ftp_mkdir($ftpConn, $path);
            Log::info("ftp_mkdir('$path') => ", ['result' => $mkdirResult]);
            if ($mkdirResult) {
                Log::info("Diretório FTP criado: $path");
            } else {
                Log::error("Falha ao criar diretório FTP: $path");
                throw new \Exception("Falha ao criar diretório FTP: $path");
            }
        }
        // Volta para raiz
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

        $remoteDir = dirname($remoteFile);
        self::ensureFtpDirectories($ftpConn, $remoteDir);

        Log::info("Iniciando upload FTP: $localFile => $remoteFile");
        $putResult = ftp_put($ftpConn, $remoteFile, $localFile, FTP_BINARY);
        Log::info("ftp_put('$remoteFile', '$localFile', FTP_BINARY) => ", ['result' => $putResult]);
        if (!$putResult) {
            Log::error("Falha ao enviar arquivo para FTP: $remoteFile");
            if ($closeAfter) ftp_close($ftpConn);
            throw new \Exception("Falha ao enviar arquivo para FTP: $remoteFile");
        }
        Log::info("Upload FTP realizado com sucesso: $remoteFile");

        if ($closeAfter) {
            $closeResult = ftp_close($ftpConn);
            Log::info('Conexão FTP fechada.', ['result' => $closeResult]);
        }
    }
}
