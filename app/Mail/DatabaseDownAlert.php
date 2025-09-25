<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseDownAlert extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Nome da conexão de banco (renomeado para evitar conflito com a property $connection do trait Queueable)
     */
    public string $dbConnection;
    public ?string $driver;
    public ?string $host;
    public ?string $database;
    public ?string $errorMessage;

    public function __construct(
        string $connection,
        ?string $driver,
        ?string $host,
        ?string $database,
        ?string $errorMessage,
    ) {
        $this->dbConnection = $connection;
        $this->driver = $driver;
        $this->host = $host;
        $this->database = $database;
        $this->errorMessage = $errorMessage;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALERTA: Falha de Conexão com Banco ('.$this->dbConnection.')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.database_down_alert',
            with: [
                'connection' => $this->connection,
                // Ajusta para a nova propriedade
                'connection' => $this->dbConnection,
                'driver' => $this->driver,
                'host' => $this->host,
                'database' => $this->database,
                'errorMessage' => $this->errorMessage,
                'ts' => now()->toDateTimeString(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
