<?php
// ============================================
// DATABASE CONNECTION — FILL THIS IN YOURSELF
// ============================================
// Create the DB + tables in phpMyAdmin using schema.sql first,
// then set your real values below.

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "event";

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$DB_OK = !$conn->connect_error;
?>
