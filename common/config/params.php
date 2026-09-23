<?php

declare(strict_types=1);

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
    // Base URL of the frontend app (where reception/security/user accounts log in
    // and set their password). Backend uses this to build the "set your password"
    // link sent by email when an admin creates a new account.
    // Override the real value in common/config/params-local.php per environment.
    'frontendHostInfo' => 'http://localhost:8080',
];
