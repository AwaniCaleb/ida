<?php
require_once __DIR__ . '/bootstrap.php';

// Usage:
// php test/delete_member.php --id 123
// php test/delete_member.php --email "a@b.com"

function get_arg_value(array $argv, $name) {
    $flag = '--' . $name;
    $index = array_search($flag, $argv, true);
    if ($index === false || !isset($argv[$index + 1])) {
        return null;
    }
    return $argv[$index + 1];
}

$id = get_arg_value($argv, 'id');
$email = get_arg_value($argv, 'email');

if (!$id && !$email) {
    cli_error("Provide --id or --email.");
}
if ($id && $email) {
    cli_error("Provide only one of --id or --email.");
}

try {
    if ($id) {
        $stmt = $pdo->prepare("SELECT id, email, full_name FROM members WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, email, full_name FROM members WHERE email = ?");
        $stmt->execute([$email]);
    }
    $member = $stmt->fetch();
    if (!$member) {
        cli_error("Member not found.");
    }

    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$member['id']]);

    cli_print_kv([
        'status' => 'deleted',
        'id' => $member['id'],
        'email' => $member['email'],
        'name' => $member['full_name'],
    ]);
} catch (Exception $e) {
    cli_error("Failed to delete member: " . $e->getMessage());
}
?>
