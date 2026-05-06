<?php
// db/conexao.php — Conexão PDO centralizada

define('DB_HOST', 'localhost');
define('DB_NAME', 'ragnarok_mvp');
define('DB_USER', 'root');
define('DB_PASS', '');          // Altere para sua senha do WAMP
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['erro' => 'Falha na conexão com o banco de dados.']));
}

// Mantém $conn para compatibilidade com código legado (mysqli)
// Remova quando migrar tudo para PDO
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Erro mysqli: ' . $conn->connect_error);
}
$conn->set_charset(DB_CHARSET);