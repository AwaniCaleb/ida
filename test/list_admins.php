<?php
require_once __DIR__ . '/bootstrap.php';

// Usage:
// php test/list_admins.php

$stmt = $pdo->query("SELECT id, username FROM admins ORDER BY id ASC");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    cli_print_kv([
        'id' => $row['id'],
        'username' => $row['username'],
    ]);
    echo "----\n";
}
?>
