<?php
// Application configuration with optional .env overrides.

function load_env_file($path) {
    if (!file_exists($path)) {
        return [];
    }
    $data = parse_ini_file($path, false, INI_SCANNER_TYPED);
    return is_array($data) ? $data : [];
}

$env = load_env_file(__DIR__ . '/../.env');

$APP_CONFIG = [
    'app_name' => $env['APP_NAME'] ?? 'IDA',
    'min_password_length' => $env['MIN_PASSWORD_LENGTH'] ?? 8,
    'max_login_attempts' => $env['MAX_LOGIN_ATTEMPTS'] ?? 5,
    'login_lockout_seconds' => $env['LOGIN_LOCKOUT_SECONDS'] ?? 600,
    'password_reset_seconds' => $env['PASSWORD_RESET_SECONDS'] ?? 1800,
];

$DB_CONFIG = [
    'host' => $env['DB_HOST'] ?? 'localhost',
    'name' => $env['DB_NAME'] ?? 'ida_db',
    'user' => $env['DB_USER'] ?? 'root',
    'pass' => $env['DB_PASS'] ?? '',
    'charset' => $env['DB_CHARSET'] ?? 'utf8mb4',
];
?>
