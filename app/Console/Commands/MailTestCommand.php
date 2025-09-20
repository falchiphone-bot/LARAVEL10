<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test {to : Endereço de e-mail de destino}
        {--subject=Teste SMTP : Assunto do e-mail}
        {--message=Este é um e-mail de teste do Laravel : Corpo do e-mail}
        {--host= : Override do host SMTP}
        {--port= : Override da porta SMTP}
        {--encryption= : Override da criptografia (tls|ssl|null)}
        {--username= : Override do usuário SMTP}
        {--password= : Override da senha SMTP}
        {--from-address= : Override do remetente (address)}
    {--from-name= : Override do remetente (name)}
    {--mailer= : Usar mailer específico (smtp|gmail|failover|log)}';

    protected $description = 'Envia um e-mail de teste usando a configuração SMTP atual';

    public function handle(): int
    {
        $to = $this->argument('to');
        $subject = (string)$this->option('subject');
        $message = (string)$this->option('message');

        try {
            // Overrides opcionais de configuração
            $overrides = [];
            if ($host = $this->option('host')) { $overrides['mail.mailers.smtp.host'] = $host; }
            if ($port = $this->option('port')) { $overrides['mail.mailers.smtp.port'] = (int)$port; }
            if (($enc = $this->option('encryption')) !== null) {
                $overrides['mail.mailers.smtp.encryption'] = $enc === 'null' ? null : $enc;
            }
            if ($user = $this->option('username')) { $overrides['mail.mailers.smtp.username'] = $user; }
            if (($pass = $this->option('password')) !== null) { $overrides['mail.mailers.smtp.password'] = $pass; }
            if ($fa = $this->option('from-address')) { $overrides['mail.from.address'] = $fa; }
            if ($fn = $this->option('from-name')) { $overrides['mail.from.name'] = $fn; }

            if ($mailer = $this->option('mailer')) { $overrides['mail.default'] = $mailer; }
            if (!empty($overrides)) {
                foreach ($overrides as $k => $v) { config([$k => $v]); }
            }

            Mail::raw($message, function ($m) use ($to, $subject) {
                $m->to($to)->subject($subject);
            });
            $this->info('E-mail de teste enviado para: ' . $to);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Falha ao enviar e-mail: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
