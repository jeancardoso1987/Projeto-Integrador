<?php
include('../db/conexao.php');

$servidor_id = $_GET['servidor_id'];

$sql = "SELECT * FROM mvps WHERE servidor_id = $servidor_id";
$result = $conn->query($sql);

$mvps = [];
while ($row = $result->fetch_assoc()) {
    $mvps[] = $row;
}

echo json_encode($mvps);
?>
