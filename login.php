<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db/conexao.php';

if (estaLogado()) { header('Location: index.php'); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha']      ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, senha_hash, role, ativo
             FROM usuarios WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !$user['ativo']) {
            $erro = 'E-mail não encontrado ou conta inativa.';
        } elseif (!password_verify($senha, $user['senha_hash'])) {
            $erro = 'Senha incorreta.';
        } else {
            $pdo->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?')
                ->execute([$user['id']]);
            iniciarSessao($user);
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Ragnarok MVP Timer</title>
    <link rel="icon" type="image/svg+xml" href="img/logo.svg">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="login-logo">
        <img src="img/logo.svg" class="logo-icon-img" alt="MVP Timer">
        <h1>MVP Timer</h1>
        <p>Ragnarok Online</p>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="login-form" autocomplete="off">
        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required autofocus autocomplete="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="campo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn-login">Entrar</button>
    </form>

    <p class="link-alterno">Não tem conta? <a href="registro.php">Criar conta</a></p>
</div>
</body>
</html>