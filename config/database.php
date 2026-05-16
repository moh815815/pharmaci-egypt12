<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmaci_egypt');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('DB_AVAILABLE', false); // Set to TRUE after importing sql/database.sql into MySQL

function getDB() {
    static $pdo = null;
    if ($pdo === null && DB_AVAILABLE) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log("DB connection failed: " . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}
