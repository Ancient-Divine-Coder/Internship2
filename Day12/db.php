<?php
$host = "localhost";
$db_user = "root"; // Default phpMyAdmin user
$db_pass = "";     // Default phpMyAdmin password is empty
$db_name = "student_management";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>