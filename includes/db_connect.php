<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aconito";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>

