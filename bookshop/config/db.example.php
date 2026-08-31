<?php
/**
 * TEMPLATE — copy this file to db.php and fill in your real credentials.
 * db.php is gitignored and never committed, so your password stays private.
 *
 *   cp config/db.example.php config/db.php
 */

$DB_HOST = 'your_db_host_here';      // e.g. sql106.infinityfree.com, or 'localhost' for local dev
$DB_NAME = 'your_db_name_here';      // e.g. if0_12345678_bookshop
$DB_USER = 'your_db_user_here';
$DB_PASS = 'your_db_password_here';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
