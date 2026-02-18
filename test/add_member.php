<?php
// Gray: CLI utility — add a member directly to the DB.
// Useful for seeding test data without going through the web form.
// Usage: php test/add_member.php --name "Full Name" --email "a@b.com"
//            --password "secret" --phone "123" --address "addr"
//            --next-of-kin "kin" [--status pending|approved|rejected]

require_once __DIR__ . '/bootstrap.php';
// get_arg_value() comes from bootstrap.php — removed duplicate definition

$full_name      = get_arg_value($argv, 'name');
$email          = get_arg_value($argv, 'email');
$password_plain = get_arg_value($argv, 'password');
$phone          = get_arg_value($argv, 'phone');
$address        = get_arg_value($argv, 'address');
$next_of_kin    = get_arg_value($argv, 'next-of-kin');
$status         = get_arg_value($argv, 'status') ?: 'pending';

if (!$full_name || !$email || !$password_plain || !$phone || !$address || !$next_of_kin) {
    cli_error("Missing required arguments. See script header for usage.");
}

$allowed_status = ['pending', 'approved', 'rejected'];
if (!in_array($status, $allowed_status, true)) {
    cli_error("Invalid status. Use pending, approved, or rejected.");
}

try {
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        cli_error("Member with this email already exists.");
    }

    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO members (full_name, email, password, phone, address, next_of_kin, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$full_name, $email, $password_hash, $phone, $address, $next_of_kin, $status]);

    $id = $pdo->lastInsertId();
    cli_print_kv([
        'status'        => 'created',
        'id'            => $id,
        'email'         => $email,
        'member_status' => $status,
    ]);
} catch (Exception $e) {
    cli_error("Failed to add member: " . $e->getMessage());
}
