<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Cria a mensagem para o e-mail de redefinição usando o mailer padrão atual.
     */
    protected function buildMailMessage($url)
    {
        $mail = parent::buildMailMessage($url);

        // Garante o uso do mailer configurado (failover ou smtp) e remetente do .env
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');
        if ($fromAddress) {
            $mail->from($fromAddress, $fromName ?: null);
        }
        $defaultMailer = config('mail.default');
        if ($defaultMailer) {
            $mail->mailer($defaultMailer);
        }
        return $mail;
    }
}
