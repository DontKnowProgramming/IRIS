<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: user_management.php");
    exit();
}

require_once 'config.php';
$pdo = new PDO('mysql:host=localhost;dbname=rocelenrj_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
} catch (PDOException $e) {
    // Optionally log the error or display a message
}

header("Location: user_management.php");
exit();
