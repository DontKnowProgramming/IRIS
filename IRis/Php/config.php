<?php
$host = 'localhost';
$dbname = 'rocelenrj_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
