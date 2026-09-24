<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

$smtpUser = 'faridasleyman@gmail.com';
$smtpPass = 'vpyeoroajaxjepqz';

$dsn = "smtp://$smtpUser:$smtpPass@smtp.gmail.com:587?encryption=tls&auth_mode=login";

$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from($smtpUser)
    ->to('faridasleyman@gmail.com')
    ->subject('Test Email')
    ->text('Hello test');

$mailer->send($email);

echo "Email sent";
