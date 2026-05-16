<?php
session_start();

// Auto-detect BASE_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
define('BASE_URL', $protocol . '://' . $host . $scriptDir);
define('ADMIN_URL', BASE_URL . '/admin');
define('UPLOADS_URL', BASE_URL . '/uploads');
define('ASSETS_URL', BASE_URL . '/assets');

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('LICENSES_PATH', UPLOADS_PATH . 'licenses' . DIRECTORY_SEPARATOR);
define('PHOTOS_PATH', UPLOADS_PATH . 'photos' . DIRECTORY_SEPARATOR);

define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOC_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);

define('PHARMACIES_PER_PAGE', 12);
define('ADMIN_PER_PAGE', 20);

date_default_timezone_set('Africa/Cairo');

define('LANG_DIR', 'rtl');
define('LANG_CODE', 'ar');
define('CURRENCY', 'جنيه مصري');
define('CURRENCY_SYMBOL', 'ج.م');

// Custom error handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        error_log("PHP Error [$severity]: $message in $file on line $line");
    }
    return true;
});

require_once ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . 'functions.php';
