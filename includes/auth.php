<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function estaLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function getRoleAtual(): ?string
{
    return $_SESSION['role'] ?? null;
}

function requerLogin(string $redirect = 'login.php'): void
{
    if (!estaLogado()) {
        header("Location: $redirect");
        exit;
    }
}

function requerAdmin(): void
{
    requerLogin();
    if (getRoleAtual() !== 'admin') {
        http_response_code(403);
        include __DIR__ . '/../erros/403.php';
        exit;
    }
}

function iniciarSessao(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['username']   = $usuario['username'];
    $_SESSION['role']       = $usuario['role'];
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}
