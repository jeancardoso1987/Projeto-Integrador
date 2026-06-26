<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db/conexao.php';

requerAdmin();

$statsSQL = [
    'usuarios'       => 'SELECT COUNT(*) FROM usuarios',
    'servidores'     => 'SELECT COUNT(*) FROM servidores WHERE ativo = 1',
    'mvps'           => 'SELECT COUNT(*) FROM mvps WHERE ativo = 1',
    'monitoramentos' => 'SELECT COUNT(*) FROM monitoramentos',
];
$stats = [];
foreach ($statsSQL as $k => $sql) {
    $stats[$k] = $pdo->query($sql)->fetchColumn();
}

$usuarios = $pdo->query(
    'SELECT id, username, email, role, ativo, criado_em, ultimo_login
     FROM usuarios ORDER BY criado_em DESC'
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $uid  = (int)($_POST['uid'] ?? 0);

    if ($uid > 0) {
        match($acao) {
            'ativar'    => $pdo->prepare('UPDATE usuarios SET ativo = 1 WHERE id = ?')->execute([$uid]),
            'desativar' => $pdo->prepare('UPDATE usuarios SET ativo = 0 WHERE id = ?')->execute([$uid]),
            'promover'  => $pdo->prepare("UPDATE usuarios SET role = 'admin' WHERE id = ?")->execute([$uid]),
            'rebaixar'  => $pdo->prepare("UPDATE usuarios SET role = 'usuario' WHERE id = ?")->execute([$uid]),
            default     => null,
        };
    }
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — MVP Timer</title>
    <link rel="icon" type="image/svg+xml" href="img/logo.svg">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-brand"><img src="img/logo.svg" class="nav-logo" alt=""><span>MVP Timer</span></a>
    <div class="nav-links">
        <a href="index.php"      class="nav-link">Dashboard</a>
        <a href="relatorios.php" class="nav-link">Relatórios</a>
        <a href="admin.php"      class="nav-link nav-admin active">Admin</a>
        <a href="admin_mvps.php" class="nav-link nav-admin">MVPs</a>
    </div>
    <div class="nav-user">
        <span class="badge-role badge-admin">admin</span>
        <span class="nav-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn-logout">Sair</a>
    </div>
</nav>

<div class="container">

    <div class="stats-grid">
        <?php
        $labels = ['usuarios' => 'Usuários', 'servidores' => 'Servidores', 'mvps' => 'MVPs', 'monitoramentos' => 'Monitoramentos'];
        foreach ($stats as $k => $v): ?>
        <div class="stat-card">
            <span class="stat-num"><?= number_format($v) ?></span>
            <span class="stat-label"><?= $labels[$k] ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-header-row">
            <h2>👥 Usuários</h2>
            <a href="admin_novo_usuario.php" class="btn-primary">+ Novo usuário</a>
        </div>

        <div class="table-responsive">
            <table class="tabela-hist">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>E-mail</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th>Último login</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr class="<?= !$u['ativo'] ? 'row-inativo' : '' ?>">
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge-role badge-<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                        <td><span class="badge-status <?= $u['ativo'] ? 'status-ok' : 'status-inativo' ?>">
                            <?= $u['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                        <td><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
                        <td><?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : '—' ?></td>
                        <td class="acoes">
                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                                <?php if ($u['ativo']): ?>
                                <button name="acao" value="desativar" class="btn-sm btn-danger" title="Desativar">✕</button>
                                <?php else: ?>
                                <button name="acao" value="ativar" class="btn-sm btn-success" title="Ativar">✓</button>
                                <?php endif; ?>
                                <?php if ($u['role'] === 'usuario'): ?>
                                <button name="acao" value="promover" class="btn-sm btn-warn" title="Promover a admin">↑</button>
                                <?php else: ?>
                                <button name="acao" value="rebaixar" class="btn-sm btn-neutral" title="Remover admin">↓</button>
                                <?php endif; ?>
                            </form>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>