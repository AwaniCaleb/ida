<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/validation.php';

function normalize_identifier($value) {
    return strtolower(trim($value));
}

function get_login_attempt($pdo, $role, $identifier) {
    $stmt = $pdo->prepare("SELECT failed_count, locked_until FROM login_attempts WHERE role = ? AND identifier = ?");
    $stmt->execute([$role, $identifier]);
    return $stmt->fetch();
}

function record_login_failure($pdo, $role, $identifier, $max_attempts, $lockout_seconds) {
    $existing = get_login_attempt($pdo, $role, $identifier);
    $failed_count = $existing ? (int)$existing['failed_count'] + 1 : 1;
    $locked_until = 0;
    if ($failed_count >= $max_attempts) {
        $locked_until = time() + $lockout_seconds;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO login_attempts (role, identifier, failed_count, locked_until)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE failed_count = VALUES(failed_count), locked_until = VALUES(locked_until)"
    );
    $stmt->execute([$role, $identifier, $failed_count, $locked_until]);
    return ['failed_count' => $failed_count, 'locked_until' => $locked_until];
}

function clear_login_attempts($pdo, $role, $identifier) {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE role = ? AND identifier = ?");
    $stmt->execute([$role, $identifier]);
}

function get_lockout_info($pdo, $role, $identifier, $max_attempts) {
    $attempt = get_login_attempt($pdo, $role, $identifier);
    if (!$attempt) {
        return ['locked' => false, 'remaining' => $max_attempts, 'locked_until' => 0];
    }
    $locked_until = (int)$attempt['locked_until'];
    $failed_count = (int)$attempt['failed_count'];
    $remaining = max(0, $max_attempts - $failed_count);
    return ['locked' => $locked_until > time(), 'remaining' => $remaining, 'locked_until' => $locked_until];
}

function create_password_reset_token($pdo, $role, $identifier, $expires_seconds) {
    $token = bin2hex(random_bytes(16));
    $expires_at = date('Y-m-d H:i:s', time() + $expires_seconds);
    $stmt = $pdo->prepare(
        "INSERT INTO password_resets (role, identifier, token, expires_at)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$role, $identifier, $token, $expires_at]);
    return $token;
}

function find_valid_password_reset($pdo, $role, $identifier, $token) {
    $stmt = $pdo->prepare(
        "SELECT * FROM password_resets
         WHERE role = ? AND identifier = ? AND token = ? AND used_at IS NULL AND expires_at > NOW()
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([$role, $identifier, $token]);
    return $stmt->fetch();
}

function mark_password_reset_used($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
}

function log_admin_action($pdo, $admin_id, $action, $entity_type, $entity_id, $details = null) {
    $stmt = $pdo->prepare(
        "INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$admin_id, $action, $entity_type, $entity_id, $details]);
}
?>
