<?php
require_once __DIR__ . '/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Demo credentials — CHANGE THESE before deploying anywhere real.
 * Username: admin   Password: orientation2026
 * Generate a new hash with: php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
 */
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2b$10$8aJ8NNQdKR0sTQrbLuqM7OSOtEdPHFQVkqq5i7h2xmWC1VfbL3y6.');

function is_admin_logged_in()
{
    return !empty($_SESSION['is_admin']);
}

function require_admin_login()
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
