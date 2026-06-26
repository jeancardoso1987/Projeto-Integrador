<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db/conexao.php';

if (estaLogado()) { header('Location: index.php'); exit; }

$erros   = [];
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $senha    = $_POST['senha']         ?? '';
    $confirma = $_POST['confirma']      ?? '';

    if ($email === '') {
        $erros[] = 'O e-mail é obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido.';
    }
    if (strlen($senha) < 6) {
        $erros[] = 'A senha deve ter pelo menos 6 caracteres.';
    }
    if ($senha !== $confirma) {
        $erros[] = 'As senhas não coincidem.';
    }

    if (empty($erros)) {
        $chk = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $erros[] = 'Este e-mail já possui uma conta.';
        }
    }

    if (empty($erros)) {
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (username, email, senha_hash, role, ativo)
             VALUES (?, ?, ?, "usuario", 1)'
        );
        $stmt->execute([$email, $email, $hash]);
        $uid = $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO configuracoes (usuario_id) VALUES (?)')->execute([$uid]);
        $sucesso = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta — Ragnarok MVP Timer</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="login-logo">
        <span class="logo-icon">⚔️</span>
        <h1>Criar conta</h1>
        <p>Ragnarok MVP Timer</p>
    </div>

    <?php if ($sucesso): ?>
        <div class="alert alert-sucesso">
            <strong>Conta criada com sucesso!</strong><br>
            Seu login é o e-mail que você cadastrou.<br><br>
            <a href="login.php" class="btn-login" style="display:inline-block;text-align:center;text-decoration:none;">
                Ir para o login
            </a>
        </div>
    <?php else: ?>

        <?php if (!empty($erros)): ?>
            <div class="alert alert-erro">
                <?php foreach ($erros as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="registro.php" class="login-form" autocomplete="off">
            <div class="campo">
                <label for="email">E-mail <span class="hint">— será usado para entrar</span></label>
                <input type="email" id="email" name="email" required autofocus autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="campo">
                <label for="senha">Senha <span class="hint">(mín. 6 caracteres)</span></label>
                <input type="password" id="senha" name="senha" required autocomplete="new-password">
            </div>
            <div class="campo">
                <label for="confirma">Confirmar senha</label>
                <input type="password" id="confirma" name="confirma" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn-login">Criar conta</button>
        </form>

    <?php endif; ?>

    <p class="link-alterno">Já tem conta? <a href="login.php">Entrar</a></p>
</div>
</body>
</html>