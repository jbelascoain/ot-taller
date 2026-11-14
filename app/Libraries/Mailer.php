<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;

class Mailer
{
    public function isConfigured(): bool
    {
        return trim(setting('smtp_host')) !== '';
    }

    public function send(string $to, string $subject, string $html): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $host   = trim(setting('smtp_host'));
        $user   = trim(setting('smtp_user'));
        $pass   = setting('smtp_pass');
        $port   = (int) (setting('smtp_port') ?: 587);
        $crypto = trim(setting('smtp_crypto'));
        $from   = trim(setting('smtp_from_email')) ?: ($user !== '' ? $user : 'no-reply@localhost');
        $name   = trim(setting('smtp_from_name')) ?: setting('workshop_name', 'Taller');

        $email = new Email([
            'protocol'     => 'smtp',
            'SMTPHost'     => $host,
            'SMTPPort'     => $port,
            'SMTPUser'     => $user,
            'SMTPPass'     => $pass,
            'SMTPCrypto'   => $crypto,
            'SMTPAuth'     => $user !== '',
            'SMTPTimeout'  => 15,
            'mailType'     => 'html',
            'charset'      => 'utf-8',
            'fromEmail'    => $from,
            'fromName'     => $name,
            'wordWrap'     => true,
            'newline'      => "\r\n",
            'CRLF'         => "\r\n",
        ]);

        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($html);

        if (! $email->send(false)) {
            log_message('error', 'SMTP error: {debug}', ['debug' => (string) $email->printDebugger(['headers'])]);

            return false;
        }

        return true;
    }
}
