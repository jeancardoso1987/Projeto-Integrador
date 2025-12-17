<?php include('./db/conexao.php'); ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ragnarok MVP Timer</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">

    <div class="div1">
        <h1>Ragnarok MVP Timer</h1>
        <p>
            Um sistema inteligente para monitorar o tempo de respawn dos MVPs no Ragnarok Online.
            Escolha o servidor e acompanhe seus MVPs favoritos com alertas e cronômetros automáticos.
        </p>
        <label for="servidorSelect">Selecione o servidor:</label>
        <select id="servidorSelect">
            <option value="">-- Escolha --</option>
            <?php
            $sql = "SELECT * FROM servidores";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()){
                echo "<option value='{$row['id']}'>{$row['nome']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="div2" id="monitorando" style="display:none;">
        <h2>Monitorando</h2>
        <div id="listaMonitoramento" class="lista-monitoramento"></div>
    </div>

    <div class="div3" id="lista-mvp" style="display: none;">
        <h2>Lista de MVPs</h2>
        <div id="listaMVPs" class="lista-mvps"></div>
    </div>

</div>

<script src="js/script.js"></script>
</body>
</html>
