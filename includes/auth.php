<?php
// includes/auth.php — Helpers de sessão e controle de acesso

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // mude para true em produção (HTTPS)
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

/**
 * Retorna true se há um usuário logado.
 */
function estaLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Retorna o role do usuário logado ('admin' | 'usuario') ou null.
 */
function getRoleAtual(): ?string
{
    return $_SESSION['role'] ?? null;
}

/**
 * Redireciona para o login se não estiver autenticado.
 */
function requerLogin(string $redirect = 'login.php'): void
{
    if (!estaLogado()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Redireciona com erro 403 se não for admin.
 */
function requerAdmin(): void
{
    requerLogin();
    if (getRoleAtual() !== 'admin') {
        http_response_code(403);
        include __DIR__ . '/../erros/403.php';
        exit;
    }
}

/**
 * Inicia a sessão do usuário após login bem-sucedido.
 */
function iniciarSessao(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['username']   = $usuario['username'];
    $_SESSION['role']       = $usuario['role'];
}

/**
 * Encerra a sessão.
 */
function logout(): void
{
    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}