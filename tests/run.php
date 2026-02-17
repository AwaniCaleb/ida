<?php
require_once __DIR__ . '/../test/bootstrap.php';

function run_script($script, array $args) {
    $php = PHP_BINARY ? PHP_BINARY : 'php';
    $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script);
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg($arg);
    }
    $output = [];
    $code = 0;
    exec($cmd, $output, $code);
    return ['code' => $code, 'output' => implode("\n", $output)];
}

function assert_true($condition, $message) {
    if (!$condition) {
        cli_error("TEST FAILED: " . $message);
    }
}

$member_email = 'test_member_' . time() . '_' . random_int(1000, 9999) . '@example.com';
$admin_username = 'admin_test_' . time() . '_' . random_int(1000, 9999);

$member_count_before = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$admin_count_before = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

$add_member = run_script(__DIR__ . '/../test/add_member.php', [
    '--name', 'Test Member',
    '--email', $member_email,
    '--password', 'secret123',
    '--phone', '1234567890',
    '--address', 'Test Address',
    '--next-of-kin', 'Test Kin',
    '--status', 'approved'
]);
assert_true($add_member['code'] === 0, "add_member.php failed: " . $add_member['output']);

$member_count_after_add = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
assert_true($member_count_after_add === $member_count_before + 1, "Member count did not increase.");

$delete_member = run_script(__DIR__ . '/../test/delete_member.php', [
    '--email', $member_email
]);
assert_true($delete_member['code'] === 0, "delete_member.php failed: " . $delete_member['output']);

$member_count_after_delete = (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
assert_true($member_count_after_delete === $member_count_before, "Member count did not return to baseline.");

$add_admin = run_script(__DIR__ . '/../test/add_admin.php', [
    '--username', $admin_username,
    '--password', 'secret123'
]);
assert_true($add_admin['code'] === 0, "add_admin.php failed: " . $add_admin['output']);

$admin_count_after_add = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
assert_true($admin_count_after_add === $admin_count_before + 1, "Admin count did not increase.");

$delete_admin = run_script(__DIR__ . '/../test/delete_admin.php', [
    '--username', $admin_username
]);
assert_true($delete_admin['code'] === 0, "delete_admin.php failed: " . $delete_admin['output']);

$admin_count_after_delete = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
assert_true($admin_count_after_delete === $admin_count_before, "Admin count did not return to baseline.");

echo "All tests passed.\n";
?>
