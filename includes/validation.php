<?php
function normalize_email($email) {
    return strtolower(trim($email));
}

function is_valid_email($email) {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_name($name) {
    // Letters, spaces, apostrophes, and hyphens only.
    return (bool)preg_match("/^[A-Za-z\\s'\\-]+$/", $name);
}

function normalize_phone($phone) {
    return preg_replace('/\\D+/', '', $phone);
}

function is_valid_phone($phone, $min = 7, $max = 20) {
    $digits = normalize_phone($phone);
    $len = strlen($digits);
    return $len >= $min && $len <= $max;
}
?>
