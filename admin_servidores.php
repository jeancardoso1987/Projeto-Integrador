<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db/conexao.php';

requerAdmin();

$msg  = '';
$erro = '';
$acao = $_GET['acao'] ?? 'listar';
$id   = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid      = (int)($_POST['id']        ?? 0);
    $nome     = trim($_POST['nome']       ?? '');
    $descricao = trim($_POST['descricao'] ?? '') ?: null;
    $tipo     = $_POST['tipo'] ?? 'mid';

    $tipos_validos = ['low', 'mid', 'high', 'custom'];
    if (!in_array($tipo, $tipos_validos)) $tipo = 'mid';

    if ($nome === '') {
        $erro = 'O nome do servidor é obrigatório.';
    } else {
        $chk = $pdo->prepare('SELECT id FROM servidores WHERE nome = ? AND id != ? LIMIT 1');
        $chk->execute([$nome, $pid]);
        if ($chk->fetch()) $erro = 'Já existe um servidor com este nome.';
    }

    if (!$erro) {
        if ($pid) {
            $pdo->prepare('UPDATE servidores SET nome=?, descricao=?, tipo=? WHERE id=?')
                ->execute([$nome, $descricao, $tipo, $pid]);
            $msg = "Servidor <strong>" . htmlspecialchars($nome) . "</strong> atualizado.";
        } else {
            $pdo->prepare('INSERT INTO servidores (nome, descricao, tipo) VALUES (?,?,?)')
                ->execute([$nome, $descricao, $tipo]);
            $msg = "Servidor <strong>" . htmlspecialchars($nome) . "</strong> cadastrado.";
        }
        header('Location: admin_servidores.php?msg=' . urlencode(strip_tags($msg)));
        exit;
    }
}

if ($acao === 'toggle' && $id) {
    $pdo->prepare('UPDATE servidores SET ativo = !ativo WHERE id = ?')->execute([$id]);
    header('Location: admin_servidores.php');
    exit;
}
if ($acao === 'deletar' && $id) {
    $usos = $pdo->prepare('SELECT COUNT(*) FROM monitoramentos WHERE servidor_id = ?');
    $usos->execute([$id]);
    if ($usos->fetchColumn() > 0) {
        header('Location: admin_servidores.php?erro=' . urlencode('Não é possível excluir: servidor possui histórico. Desative-o.'));
    } else {
        $pdo->prepare('DELETE FROM servidores WHERE id = ?')->execute([$id]);
        header('Location: admin_servidores.php?msg=' . urlencode('Servidor excluído.'));
    }
    exit;
}

if (!$msg  && isset($_GET['msg']))  $msg  = htmlspecialchars($_GET['msg']);
if (!$erro && isset($_GET['erro'])) $erro = htmlspecialchars($_GET['erro']);

$servidores = $pdo->query(
    'SELECT s.*, COUNT(m.id) AS total_monit
     FROM servidores s
     LEFT JOIN monitoramentos m ON m.servidor_id = s.id
     GROUP BY s.id ORDER BY s.nome'
)->fetchAll();

$editando = null;
if ($acao === 'editar' && $id) {
    $stmtEdit = $pdo->prepare('SELECT * FROM servidores WHERE id = ?');
    $stmtEdit->execute([$id]);
    $editando = $stmtEdit->fetch();
}

$tipos = [
    'low'    => 'Low Rate',
    'mid'    => 'Mid Rate',
    'high'   => 'High Rate',
    'custom' => 'Custom',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servidores — Admin</title>
    <link rel="icon" type="image/svg+xml" href="img/logo.svg">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_servidores.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-brand"><img src="img/logo.svg" class="nav-logo" alt=""><span>MVP Timer</span></a>
    <div class="nav-links">
        <a href="index.php"            class="nav-link">Dashboard</a>
        <a href="relatorios.php"       class="nav-link">Relatórios</a>
        <a href="admin.php"            class="nav-link nav-admin">Admin</a>
        <a href="admin_servidores.php" class="nav-link nav-admin active">Servidores</a>
        <a href="admin_mvps.php"       class="nav-link nav-admin">MVPs</a>
    </div>
    <div class="nav-user">
        <span class="badge-role badge-admin">admin</span>
        <span class="nav-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn-logout">Sair</a>
    </div>
</nav>

<div class="container">

    <?php if ($msg):  ?><div class="alert alert-sucesso"><?= $msg ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert alert-erro"><?= $erro ?></div><?php endif; ?>

    <div class="card">
        <h2><?= $editando ? '✏️ Editar servidor' : '➕ Novo servidor' ?></h2>

        <form method="POST" action="admin_servidores.php" class="form-servidor" id="formServidor">
            <input type="hidden" name="id" value="<?= $editando['id'] ?? 0 ?>">

            <div class="form-grid-srv">
                <div class="campo campo-lg">
                    <label>Nome do servidor *</label>
                    <input type="text" name="nome" required maxlength="100"
                           placeholder="ex: Hero RO"
                           value="<?= htmlspecialchars($editando['nome'] ?? $_POST['nome'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label>Tipo</label>
                    <select name="tipo">
                        <?php foreach ($tipos as $val => $label): ?>
                        <option value="<?= $val ?>"
                            <?= ($editando['tipo'] ?? $_POST['tipo'] ?? 'mid') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo campo-lg">
                    <label>Descrição <span class="hint">— opcional</span></label>
                    <input type="text" name="descricao" maxlength="255"
                           placeholder="ex: Servidor brasileiro mid rate"
                           value="<?= htmlspecialchars($editando['descricao'] ?? $_POST['descricao'] ?? '') ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?= $editando ? 'Salvar alterações' : 'Cadastrar servidor' ?>
                </button>
                <?php if ($editando): ?>
                <a href="admin_servidores.php" class="btn-cancelar">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header-row">
            <h2>🌐 Servidores cadastrados
                <span class="badge-count"><?= count($servidores) ?></span>
            </h2>
        </div>

        <?php if (empty($servidores)): ?>
            <p class="vazio">Nenhum servidor cadastrado ainda.</p>
        <?php else: ?>
        <div class="srv-grid">
            <?php foreach ($servidores as $s): ?>
            <div class="srv-card <?= !$s['ativo'] ? 'inativo' : '' ?>">
                <div class="srv-card-top">
                    <div>
                        <strong class="srv-nome"><?= htmlspecialchars($s['nome']) ?></strong>
                        <span class="badge-tipo badge-tipo-<?= $s['tipo'] ?>"><?= $tipos[$s['tipo']] ?></span>
                    </div>
                    <span class="badge-status <?= $s['ativo'] ? 'status-ok' : 'status-inativo' ?>">
                        <?= $s['ativo'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </div>

                <?php if ($s['descricao']): ?>
                <p class="srv-desc"><?= htmlspecialchars($s['descricao']) ?></p>
                <?php endif; ?>

                <div class="srv-card-bottom">
                    <span class="srv-stats">📊 <?= number_format($s['total_monit']) ?> monitoramentos</span>
                    <div class="srv-acoes">
                        <a href="admin_servidores.php?acao=editar&id=<?= $s['id'] ?>"
                           class="btn-sm btn-warn" title="Editar">✏️</a>
                        <a href="admin_servidores.php?acao=toggle&id=<?= $s['id'] ?>"
                           class="btn-sm <?= $s['ativo'] ? 'btn-danger' : 'btn-success' ?>"
                           title="<?= $s['ativo'] ? 'Desativar' : 'Ativar' ?>">
                           <?= $s['ativo'] ? '✕' : '✓' ?>
                        </a>
                        <a href="admin_servidores.php?acao=deletar&id=<?= $s['id'] ?>"
                           class="btn-sm btn-neutral" title="Excluir"
                           onclick="return confirm('Excluir <?= htmlspecialchars(addslashes($s['nome'])) ?>?')">
                           🗑
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
<?php if ($editando): ?>
document.getElementById('formServidor').scrollIntoView({ behavior: 'smooth' });
<?php endif; ?>
</script>
</body>
</html>