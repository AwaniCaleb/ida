<?php
// Gray: CLI utility — add an admin account directly to the DB.
// Usage: php test/add_admin.php --username "admin2" --password "secret"

require_once __DIR__ . '/bootstrap.php';
// get_arg_value() comes from bootstrap.php — removed duplicate definition

$username       = get_arg_value($argv, 'username');
$password_plain = get_arg_value($argv, 'password');

if (!$username || !$password_plain) {
    cli_error("Missing required arguments. See script header for usage.");
}

try {
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        cli_error("Admin with this username already exists.");
    }

    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password_hash]);

    $id = $pdo->lastInsertId();
    cli_print_kv([
        'status'   => 'created',
        'id'       => $id,
        'username' => $username,
    ]);
} catch (Exception $e) {
    cli_error("Failed to add admin: " . $e->getMessage());
}
