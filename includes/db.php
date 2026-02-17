<?php
require_once __DIR__ . '/config.php';

$host = $DB_CONFIG['host'];
$db   = $DB_CONFIG['name'];
$user = $DB_CONFIG['user'];
$pass = $DB_CONFIG['pass'];
$charset = $DB_CONFIG['charset'];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Later I might want to show a friendly message or something... Maybe
     // throw new \PDOException($e->getMessage(), (int)$e->getCode());
     error_log($e->getMessage());
     http_response_code(500);
     die('Database connection failed.');
}
?>
