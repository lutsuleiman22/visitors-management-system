<?php

declare(strict_types=1);

return [
    'adminEmail' => getenv('APP_ADMIN_EMAIL') ?: 'admin@example.com',
    'supportEmail' => getenv('APP_SUPPORT_EMAIL') ?: 'support@example.com',
    'senderEmail' => getenv('APP_SENDER_EMAIL') ?: 'noreply@example.com',
    'senderName' => getenv('APP_SENDER_NAME') ?: 'Visitor Management System',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
    // Base URL of the frontend app (where reception/security/user accounts log in
    // and set their password). Backend uses this to build the "set your password"
    // link sent by email when an admin creates a new account.
    'frontendHostInfo' => getenv('APP_FRONTEND_HOST') ?: 'http://localhost:8080',
];
