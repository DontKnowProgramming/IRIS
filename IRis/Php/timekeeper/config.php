<?php
$servername = "localhost";
$username   = "root";      // default for XAMPP
$password   = "";          // leave blank unless you set a MySQL password
$dbname     = "rocelenrj_db";  // <-- use this name from your phpMyAdmin screenshot

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>