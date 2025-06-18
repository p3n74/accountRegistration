<?php
$servername = "127.0.0.1";
$username = "s21102134_palisade"; 
$password = "webwebwebweb";
$dbname = "s21102134_palisade";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

