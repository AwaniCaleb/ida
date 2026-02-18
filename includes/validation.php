<?php
// Gray: Central validation helpers used across both the public site and admin.
// Keeping all validation in one place means a fix here fixes everywhere.
// Existing functions are untouched — new ones are appended at the bottom.

// ── String normalisers ───────────────────────────────────────────────────────

function normalize_email($email) {
    return strtolower(trim($email));
}

function is_valid_email($email) {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_name($name) {
    // Letters, spaces, apostrophes, and hyphens only
    return (bool) preg_match("/^[A-Za-z\\s'\\-]+$/", $name);
}

function normalize_phone($phone) {
    return preg_replace('/\\D+/', '', $phone);
}

function is_valid_phone($phone, $min = 7, $max = 20) {
    $digits = normalize_phone($phone);
    $len    = strlen($digits);
    return $len >= $min && $len <= $max;
}

// ── File upload validators ───────────────────────────────────────────────────

/**
 * Gray: Validate an uploaded image by BOTH extension AND actual MIME type.
 *
 * Checking only the extension is trivially bypassed — someone can rename
 * a .php file to .jpg and bypass a naive check. finfo reads the file's
 * magic bytes instead, so the actual content has to match.
 *
 * @param  array  $file    A single entry from $_FILES
 * @param  string $error   Populated with a human-readable message on failure
 * @return bool
 */
function is_valid_image_upload(array $file, string &$error): bool {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowed_mimes      = ['image/jpeg', 'image/png', 'image/gif'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions, true)) {
        $error = 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions) . '.';
        return false;
    }

    // finfo reads actual bytes — extension spoofing will not fool this
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed_mimes, true)) {
        $error = 'File content does not match its extension. Please upload a real image.';
        return false;
    }

    return true;
}

/**
 * Gray: Validate an uploaded document (PDF, Word) by extension AND MIME type.
 *
 * @param  array  $file
 * @param  string $error
 * @return bool
 */
function is_valid_document_upload(array $file, string &$error): bool {
    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $allowed_mimes      = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions, true)) {
        $error = 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions) . '.';
        return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed_mimes, true)) {
        $error = 'File content does not match its extension.';
        return false;
    }

    return true;
}

// ── URL validators ───────────────────────────────────────────────────────────

/**
 * Gray: Only allow YouTube embed URLs for library video entries.
 *
 * We store the URL directly and render it in an iframe — accepting
 * arbitrary URLs would be a stored XSS and open-redirect risk.
 * This regex only accepts the standard YouTube embed format.
 *
 * Valid examples:
 *   https://www.youtube.com/embed/dQw4w9WgXcQ
 *   https://youtube.com/embed/dQw4w9WgXcQ?start=30
 *
 * @param  string $url
 * @return bool
 */
function is_valid_youtube_embed(string $url): bool {
    return (bool) preg_match(
        '#^https://(?:www\.)?youtube\.com/embed/[A-Za-z0-9_\-]{11}(?:[?&][^"<>]*)?$#',
        trim($url)
    );
}

/**
 * Gray: Strip anything dangerous from a URL before storing it.
 * Used as a second pass after is_valid_youtube_embed() — belt and suspenders.
 * filter_var rejects javascript: and data: schemes outright.
 *
 * @param  string $url
 * @return string  Clean URL, or empty string if invalid
 */
function sanitize_url(string $url): string {
    $clean = filter_var(trim($url), FILTER_SANITIZE_URL);
    return (filter_var($clean, FILTER_VALIDATE_URL) !== false) ? $clean : '';
}
