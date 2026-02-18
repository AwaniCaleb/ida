<?php
// Gray: Shared bootstrap for all CLI test/utility scripts.
// Guards against web execution, connects to DB, and provides
// the small helpers every script needs.
//
// get_arg_value() now lives here — it was copy-pasted across five
// scripts before. Fix it once here, it's fixed everywhere.

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden: CLI only.\n";
    exit(1);
}

require_once __DIR__ . '/../includes/db.php';

if (!isset($pdo)) {
    fwrite(STDERR, "Database connection is not available. Check includes/db.php.\n");
    exit(1);
}

// ── Output helpers ───────────────────────────────────────────────────────────

/**
 * Gray: Write an error to STDERR and exit non-zero.
 * STDERR keeps errors separate from normal output so shell scripts
 * can pipe stdout without noise.
 */
function cli_error($message, $code = 1) {
    fwrite(STDERR, $message . "\n");
    exit($code);
}

/**
 * Gray: Print an associative array as "key: value" lines.
 * Plain text so it stays greppable from the terminal.
 */
function cli_print_kv(array $data) {
    foreach ($data as $key => $value) {
        echo $key . ": " . $value . "\n";
    }
}

// ── Argument parser ──────────────────────────────────────────────────────────

/**
 * Gray: Pull a named flag value out of $argv.
 *
 * Usage in scripts:
 *   php script.php --email foo@bar.com
 *   $email = get_arg_value($argv, 'email'); // → "foo@bar.com"
 *
 * Returns null if the flag is absent or has no following value.
 *
 * @param  array  $argv  Global $argv from the calling script
 * @param  string $name  Flag name without dashes (e.g. 'email', 'next-of-kin')
 * @return string|null
 */
function get_arg_value(array $argv, $name) {
    $flag  = '--' . $name;
    $index = array_search($flag, $argv, true);
    if ($index === false || !isset($argv[$index + 1])) {
        return null;
    }
    return $argv[$index + 1];
}
