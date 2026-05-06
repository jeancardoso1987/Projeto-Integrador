<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db/conexao.php';

requerLogin();

$usuario_id = $_SESSION['usuario_id'];
$role       = $_SESSION['role'];
$email      = $_SESSION['username'];

$servidores = $pdo->query('SELECT * FROM servidores WHERE ativo = 1 ORDER BY nome')->fetchAll();

$stmtTotal = $pdo->prepare('SELECT COUNT(*) FROM monitoramentos WHERE usuario_id = ?');
$stmtTotal->execute([$usuario_id]);
$totalMonitor = $stmtTotal->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT m.*, mv.nome AS mvp_nome, mv.mapa, mv.spawn_min,
            mv.item_id, mv.imagem_url, s.nome AS servidor_nome
     FROM monitoramentos m
     JOIN mvps mv ON mv.id = m.mvp_id
     JOIN servidores s ON s.id = m.servidor_id
     WHERE m.usuario_id = ? AND m.finalizado = 0
     ORDER BY m.iniciado_em DESC'
);
$stmt->execute([$usuario_id]);
$ativos = $stmt->fetchAll();

foreach ($ativos as &$a) {
    if (!empty($a['imagem_url']))   $a['img'] = $a['imagem_url'];
    elseif (!empty($a['item_id'])) $a['img'] = 'https://static.divine-pride.net/images/items/cards/' . $a['item_id'] . '.png';
    else                            $a['img'] = '';
}
unset($a);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ragnarok MVP Timer</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">⚔️ MVP Timer</div>
    <div class="nav-links">
        <a href="index.php"            class="nav-link active">Dashboard</a>
        <a href="relatorios.php"       class="nav-link">Relatórios</a>
        <?php if ($role === 'admin'): ?>
        <a href="admin.php"            class="nav-link nav-admin">Admin</a>
        <a href="admin_servidores.php" class="nav-link nav-admin">Servidores</a>
        <a href="admin_mvps.php"       class="nav-link nav-admin">MVPs</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        <span class="badge-role badge-<?= $role ?>"><?= $role ?></span>
        <span class="nav-username"><?= htmlspecialchars($email) ?></span>
        <a href="logout.php" class="btn-logout">Sair</a>
    </div>
</nav>

<div class="container">

    <!-- Cabeçalho -->
    <div class="card card-header-mvp">
        <h1>⚔️ Ragnarok MVP Timer</h1>
        <p>Selecione o servidor, encontre o MVP e registre o horário da morte para iniciar o timer.</p>

        <div class="campo-inline">
            <label for="servidorSelect">Servidor:</label>
            <select id="servidorSelect">
                <option value="">-- Escolha --</option>
                <?php foreach ($servidores as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <span class="stat-num" id="statAtivos"><?= count($ativos) ?></span>
                <span class="stat-label">Monitorando agora</span>
            </div>
            <div class="stat-item">
                <span class="stat-num"><?= $totalMonitor ?></span>
                <span class="stat-label">Total histórico</span>
            </div>
        </div>
    </div>

    <!-- Monitorando ativos -->
    <div class="card" id="monitorando" <?= empty($ativos) ? 'style="display:none"' : '' ?>>
        <h2>Monitorando</h2>
        <div id="listaMonitoramento" class="lista-monitoramento">
            <?php foreach ($ativos as $a): ?>
            <div class="mvp-ativo" id="ativo-<?= $a['id'] ?>"
                 data-id="<?= $a['id'] ?>"
                 data-spawn="<?= $a['spawn_min'] ?>"
                 data-morto-em="<?= htmlspecialchars($a['spawn_est'] ?? '') ?>">

                <div class="mvp-ativo-img">
                    <?php if ($a['img']): ?>
                        <img src="<?= htmlspecialchars($a['img']) ?>"
                             alt="<?= htmlspecialchars($a['mvp_nome']) ?>"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <span class="sem-img-ativo">?</span>
                    <?php endif; ?>
                </div>

                <div class="mvp-info">
                    <strong><?= htmlspecialchars($a['mvp_nome']) ?></strong>
                    <small><?= htmlspecialchars($a['mapa']) ?> — <?= htmlspecialchars($a['servidor_nome']) ?></small>
                    <?php if (!$a['spawn_est']): ?>
                    <small class="texto-aguardando">Aguardando hora da morte…</small>
                    <?php endif; ?>
                </div>

                <div class="mvp-timer" id="timer-<?= $a['id'] ?>">
                    <?= $a['spawn_est'] ? '--:--' : '⏸' ?>
                </div>

                <button class="btn-finalizar" onclick="finalizarMonitor(<?= $a['id'] ?>)" title="Remover">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Lista de MVPs do servidor -->
    <div class="card" id="lista-mvp" style="display:none;">
        <div class="card-header-row">
            <h2>Lista de MVPs</h2>
            <input type="text" id="buscaMvp" placeholder="🔍 Buscar MVP…" class="input-busca-mvp">
        </div>
        <div id="listaMVPs" class="lista-mvps"></div>
    </div>

</div>

<!-- Modal: hora da morte -->
<div id="modalMorte" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-mvp-id">
                <div class="modal-img" id="modalImg"></div>
                <div>
                    <h3 id="modalMvpNome">MVP</h3>
                    <small id="modalMvpMapa" class="modal-mapa"></small>
                </div>
            </div>
            <button class="modal-close" onclick="fecharModal()">✕</button>
        </div>

        <p class="modal-sub">Informe a hora em que o MVP foi morto. O timer de respawn será calculado a partir daí.</p>

        <div class="campo">
            <label for="modalHoraMorte">Hora da morte <span class="hint">— deixe em branco para usar agora</span></label>
            <input type="time" id="modalHoraMorte" step="60" class="input-time-modal">
        </div>

        <div class="modal-spawn-preview" id="modalSpawnPreview"></div>

        <div class="modal-acoes">
            <button class="btn-primary btn-block" id="btnConfirmarModal" onclick="confirmarMonitor()">
                ⚔️ Iniciar monitoramento
            </button>
            <button class="btn-cancelar btn-block" onclick="fecharModal()">Cancelar</button>
        </div>
    </div>
</div>

<script>
const USUARIO_ID = <?= json_encode($usuario_id) ?>;
</script>
<script src="js/script.js"></script>
</body>
</html>