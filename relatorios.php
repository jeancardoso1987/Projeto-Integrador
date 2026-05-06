<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db/conexao.php';

requerLogin();

$usuario_id = $_SESSION['usuario_id'];
$role       = $_SESSION['role'];

// Filtros
$filtro_servidor = (int)($_GET['servidor'] ?? 0);
$filtro_de       = $_GET['de']  ?? date('Y-m-01');
$filtro_ate      = $_GET['ate'] ?? date('Y-m-d');
$pagina          = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina      = 20;
$offset          = ($pagina - 1) * $por_pagina;

$where_usuario = ($role === 'admin') ? '' : 'AND m.usuario_id = :uid';

// Total
$sqlCount = "SELECT COUNT(*) FROM monitoramentos m
             WHERE m.iniciado_em BETWEEN :de AND :ate
             " . ($filtro_servidor ? 'AND m.servidor_id = :sid' : '') . "
             $where_usuario";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->bindValue(':de',  $filtro_de  . ' 00:00:00');
$stmtCount->bindValue(':ate', $filtro_ate . ' 23:59:59');
if ($filtro_servidor)  $stmtCount->bindValue(':sid', $filtro_servidor);
if ($role !== 'admin') $stmtCount->bindValue(':uid', $usuario_id);
$stmtCount->execute();
$total = $stmtCount->fetchColumn();
$totalPaginas = max(1, ceil($total / $por_pagina));

// Histórico paginado
$sqlHist = "SELECT m.id, u.username, u.email, mv.nome AS mvp, s.nome AS servidor,
                   m.iniciado_em, m.morto_em, m.spawn_est, m.finalizado
            FROM monitoramentos m
            JOIN usuarios   u  ON u.id  = m.usuario_id
            JOIN mvps       mv ON mv.id = m.mvp_id
            JOIN servidores s  ON s.id  = m.servidor_id
            WHERE m.iniciado_em BETWEEN :de AND :ate
            " . ($filtro_servidor ? 'AND m.servidor_id = :sid' : '') . "
            $where_usuario
            ORDER BY m.iniciado_em DESC
            LIMIT $por_pagina OFFSET $offset";
$stmtHist = $pdo->prepare($sqlHist);
$stmtHist->bindValue(':de',  $filtro_de  . ' 00:00:00');
$stmtHist->bindValue(':ate', $filtro_ate . ' 23:59:59');
if ($filtro_servidor)  $stmtHist->bindValue(':sid', $filtro_servidor);
if ($role !== 'admin') $stmtHist->bindValue(':uid', $usuario_id);
$stmtHist->execute();
$historico = $stmtHist->fetchAll();

// Todos os registros do período para o PDF (sem paginação)
$sqlPdf = "SELECT u.email, mv.nome AS mvp, s.nome AS servidor,
                  m.iniciado_em, m.morto_em, m.spawn_est, m.finalizado
           FROM monitoramentos m
           JOIN usuarios   u  ON u.id  = m.usuario_id
           JOIN mvps       mv ON mv.id = m.mvp_id
           JOIN servidores s  ON s.id  = m.servidor_id
           WHERE m.iniciado_em BETWEEN :de AND :ate
           " . ($filtro_servidor ? 'AND m.servidor_id = :sid' : '') . "
           $where_usuario
           ORDER BY m.iniciado_em DESC";
$stmtPdf = $pdo->prepare($sqlPdf);
$stmtPdf->bindValue(':de',  $filtro_de  . ' 00:00:00');
$stmtPdf->bindValue(':ate', $filtro_ate . ' 23:59:59');
if ($filtro_servidor)  $stmtPdf->bindValue(':sid', $filtro_servidor);
if ($role !== 'admin') $stmtPdf->bindValue(':uid', $usuario_id);
$stmtPdf->execute();
$todosPdf = $stmtPdf->fetchAll();

// Ranking (top 10)
$sqlRanking = "SELECT mv.nome AS mvp, COUNT(m.id) AS total
               FROM monitoramentos m
               JOIN mvps mv ON mv.id = m.mvp_id
               " . ($role !== 'admin' ? 'WHERE m.usuario_id = :uid' : '') . "
               GROUP BY mv.id ORDER BY total DESC LIMIT 10";
$stmtR = $pdo->prepare($sqlRanking);
if ($role !== 'admin') $stmtR->bindValue(':uid', $usuario_id);
$stmtR->execute();
$ranking = $stmtR->fetchAll();

// Servidores para filtro
$servidores = $pdo->query('SELECT id, nome FROM servidores WHERE ativo = 1')->fetchAll();

// Dados da conta
$stmtConta = $pdo->prepare('SELECT email, criado_em, ultimo_login FROM usuarios WHERE id = ?');
$stmtConta->execute([$usuario_id]);
$conta = $stmtConta->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios — MVP Timer</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/relatorios.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">⚔️ MVP Timer</div>
    <div class="nav-links">
        <a href="index.php"      class="nav-link">Dashboard</a>
        <a href="relatorios.php" class="nav-link active">Relatórios</a>
        <?php if ($role === 'admin'): ?>
        <a href="admin.php"      class="nav-link nav-admin">Admin</a>
        <a href="admin_mvps.php" class="nav-link nav-admin">MVPs</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        <span class="badge-role badge-<?= $role ?>"><?= $role ?></span>
        <span class="nav-username"><?= htmlspecialchars($conta['email']) ?></span>
        <a href="logout.php" class="btn-logout">Sair</a>
    </div>
</nav>

<div class="container">

    <!-- Info da conta -->
    <div class="card card-conta">
        <div class="conta-info">
            <div>
                <span class="label-info">E-mail</span>
                <strong><?= htmlspecialchars($conta['email']) ?></strong>
            </div>
            <div>
                <span class="label-info">Conta criada em</span>
                <strong><?= date('d/m/Y \à\s H:i', strtotime($conta['criado_em'])) ?></strong>
            </div>
            <div>
                <span class="label-info">Último login</span>
                <strong><?= $conta['ultimo_login']
                    ? date('d/m/Y \à\s H:i', strtotime($conta['ultimo_login']))
                    : '—' ?></strong>
            </div>
            <div>
                <span class="label-info">Total no período</span>
                <strong><?= $total ?></strong>
            </div>
        </div>
    </div>

    <!-- Ranking -->
    <div class="card">
        <h2>🏆 Ranking de MVPs mais monitorados</h2>
        <?php if (empty($ranking)): ?>
            <p class="vazio">Nenhum dado ainda.</p>
        <?php else: ?>
        <div class="ranking-lista">
            <?php foreach ($ranking as $i => $r): ?>
            <div class="ranking-item">
                <span class="ranking-pos"><?= $i + 1 ?></span>
                <span class="ranking-nome"><?= htmlspecialchars($r['mvp']) ?></span>
                <span class="ranking-total"><?= $r['total'] ?>x</span>
                <div class="ranking-bar">
                    <div class="ranking-fill" style="width:<?= min(100, round($r['total'] / max(1, $ranking[0]['total']) * 100)) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Histórico -->
    <div class="card">
        <div class="card-header-row">
            <h2>📋 Histórico de monitoramentos</h2>
            <button class="btn-pdf" id="btnExportarPdf" onclick="exportarPDF()" title="Exportar PDF">
                ⬇ Exportar PDF
            </button>
        </div>

        <form method="GET" class="filtros-form">
            <div class="filtro-grupo">
                <label>Servidor</label>
                <select name="servidor">
                    <option value="">Todos</option>
                    <?php foreach ($servidores as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $filtro_servidor == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nome']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro-grupo">
                <label>De</label>
                <input type="date" name="de" value="<?= htmlspecialchars($filtro_de) ?>">
            </div>
            <div class="filtro-grupo">
                <label>Até</label>
                <input type="date" name="ate" value="<?= htmlspecialchars($filtro_ate) ?>">
            </div>
            <button type="submit" class="btn-filtrar">Filtrar</button>
        </form>

        <?php if (empty($historico)): ?>
            <p class="vazio">Nenhum registro encontrado para os filtros selecionados.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="tabela-hist" id="tabelaHistorico">
                <thead>
                    <tr>
                        <?php if ($role === 'admin'): ?>
                        <th>Usuário</th>
                        <?php endif; ?>
                        <th>MVP</th>
                        <th>Servidor</th>
                        <th>Iniciado em</th>
                        <th>Morto em</th>
                        <th>Spawn est.</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $h): ?>
                    <tr>
                        <?php if ($role === 'admin'): ?>
                        <td><?= htmlspecialchars($h['email']) ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($h['mvp']) ?></td>
                        <td><?= htmlspecialchars($h['servidor']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($h['iniciado_em'])) ?></td>
                        <td><?= $h['morto_em']  ? date('d/m/Y H:i', strtotime($h['morto_em']))  : '—' ?></td>
                        <td><?= $h['spawn_est'] ? date('d/m/Y H:i', strtotime($h['spawn_est'])) : '—' ?></td>
                        <td>
                            <span class="badge-status <?= $h['finalizado'] ? 'status-ok' : 'status-ativo' ?>">
                                <?= $h['finalizado'] ? 'Finalizado' : 'Ativo' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <div class="paginacao">
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                <a href="?pagina=<?= $p ?>&servidor=<?= $filtro_servidor ?>&de=<?= $filtro_de ?>&ate=<?= $filtro_ate ?>"
                   class="pag-btn <?= $p === $pagina ? 'pag-ativa' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

</div>

<!-- Dados completos para o PDF (todos do período, sem paginação) -->
<script>
const PDF_DADOS = <?= json_encode(array_map(function($r) {
    return [
        'email'       => $r['email'],
        'mvp'         => $r['mvp'],
        'servidor'    => $r['servidor'],
        'iniciado_em' => date('d/m/Y H:i', strtotime($r['iniciado_em'])),
        'morto_em'    => $r['morto_em']  ? date('d/m/Y H:i', strtotime($r['morto_em']))  : '—',
        'spawn_est'   => $r['spawn_est'] ? date('d/m/Y H:i', strtotime($r['spawn_est'])) : '—',
        'status'      => $r['finalizado'] ? 'Finalizado' : 'Ativo',
    ];
}, $todosPdf)) ?>;

const PDF_META = {
    email      : <?= json_encode($conta['email']) ?>,
    criado_em  : <?= json_encode(date('d/m/Y H:i', strtotime($conta['criado_em']))) ?>,
    de         : <?= json_encode(date('d/m/Y', strtotime($filtro_de))) ?>,
    ate        : <?= json_encode(date('d/m/Y', strtotime($filtro_ate))) ?>,
    total      : <?= json_encode($total) ?>,
    role       : <?= json_encode($role) ?>,
    ranking    : <?= json_encode(array_map(fn($r) => ['mvp' => $r['mvp'], 'total' => $r['total']], $ranking)) ?>,
};
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="js/relatorios-pdf.js"></script>
</body>
</html>