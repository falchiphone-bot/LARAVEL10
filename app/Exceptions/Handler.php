<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException as SpatieUnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Database\QueryException;
use PDOException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Retorna JSON padronizado quando faltar permissão e o client espera JSON
        $this->renderable(function (SpatieUnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'SEM PERMISSÃO PARA ESTE SERVIÇO. CONSULTE O ADMINISTRADOR!'
                ], 403);
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'SEM PERMISSÃO PARA ESTE SERVIÇO. CONSULTE O ADMINISTRADOR!'
                ], 403);
            }
        });

        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if ($e->getStatusCode() === 403 && $request->expectsJson()) {
                return response()->json([
                    'error' => 'SEM PERMISSÃO PARA ESTE SERVIÇO. CONSULTE O ADMINISTRADOR!'
                ], 403);
            }
        });

        // Falhas de conexão com banco de dados (MySQL, Postgres, etc.)
        $this->renderable(function (PDOException|QueryException $e, $request) {
            if (!$this->isDatabaseConnectionError($e)) {
                return null; // deixa fluxo normal (pode ser outro tipo de erro SQL)
            }

            $connectionName = config('database.default');
            $connectionConfig = config("database.connections.$connectionName", []);

            // Circuit breaker: marca indisponibilidade por alguns segundos para evitar repetir timeout custoso
            $ttl = (int) env('DB_BREAKER_TTL', 30); // segundos
            if ($ttl > 0) {
                $key = 'db:down:'.$connectionName;
                // Só grava/renova se ainda não houver (evita sobrescrever janela com múltiplas falhas em paralelo)
                if (!Cache::has($key)) {
                    Cache::put($key, [
                        'until' => now()->addSeconds($ttl)->timestamp,
                        'since' => now()->timestamp,
                        'driver' => $connectionConfig['driver'] ?? null,
                        'host' => $connectionConfig['host'] ?? ($connectionConfig['read']['host'] ?? null),
                    ], $ttl);
                }
            }

            $data = [
                'exceptionMessage' => config('app.debug') ? $e->getMessage() : null,
                'connectionName'   => $connectionName,
                'host'             => $connectionConfig['host'] ?? ($connectionConfig['read']['host'] ?? null),
                'database'         => $connectionConfig['database'] ?? null,
                'driver'           => $connectionConfig['driver'] ?? null,
                'requestId'        => request()->header('X-Request-ID') ?? null,
            ];

            $status = 500; // Erro interno (primeira detecção). Middleware poderá devolver 503 durante janela.

            $headers = [
                'X-DB-Connection-Error' => '1',
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'FALHA NA CONEXÃO COM O BANCO DE DADOS',
                    'detalhes' => array_filter($data, fn($v) => !is_null($v)),
                ], $status)->withHeaders($headers);
            }

            return response()->view('errors.database', $data, $status)->withHeaders($headers);
        });
    }

    /**
     * Determina se a exceção representa falha de conexão (e não erro de query lógico).
     */
    protected function isDatabaseConnectionError(Throwable $e): bool
    {
        $message = Str::lower($e->getMessage());

        // Se vier código HYT00 (timeout) já classifica
        if (method_exists($e, 'getCode')) {
            $code = Str::lower((string)$e->getCode());
            if (in_array($code, ['hyt00', '08006'])) { // HYT00 = login timeout (SQL Server), 08006 = conn failure (Postgres)
                return true;
            }
        }

        $needles = [
            'connection refused',
            'could not find driver',
            'sqlstate[hy000] [2002]', // MySQL/MariaDB host
            'sqlstate[hy000] [1049]', // Unknown database
            'no such file or directory', // socket
            'sqlstate[08006]', // Postgres connection issue
            'server has gone away',
            'timeout expired',
            'login timeout expired', // SQL Server ODBC
            'could not connect to server',
            'sqlstate[hy000] [1045]', // acesso negado mysql
            'permission denied',
        ];

        foreach ($needles as $n) {
            if (str_contains($message, $n)) {
                return true;
            }
        }
        return false;
    }
}
