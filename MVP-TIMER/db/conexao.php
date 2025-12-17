<?php
$servername = "localhost";
$porta = "3308";
$username = "root";
$password = "";
$dbname = "ragnarok";

$conn = new mysqli($servername, $username, $password, $dbname, $porta);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
