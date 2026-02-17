<?php
require_once __DIR__ . '/bootstrap.php';

// Usage:
// php test/add_admin.php --username "admin2" --password "secret"

function get_arg_value(array $argv, $name) {
    $flag = '--' . $name;
    $index = array_search($flag, $argv, true);
    if ($index === false || !isset($argv[$index + 1])) {
        return null;
    }
    return $argv[$index + 1];
}

$username = get_arg_value($argv, 'username');
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
        'status' => 'created',
        'id' => $id,
        'username' => $username,
    ]);
} catch (Exception $e) {
    cli_error("Failed to add admin: " . $e->getMessage());
}
?>
