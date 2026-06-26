<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/db/conexao.php';

requerAdmin();

$msg   = '';
$erro  = '';
$acao  = $_GET['acao'] ?? 'listar';
$id    = (int)($_GET['id'] ?? 0);

define('IMG_BASE', 'https://static.divine-pride.net/images/items/cards/');

function imagemMvp(array $mvp): string {
    if (!empty($mvp['imagem_url'])) return $mvp['imagem_url'];
    if (!empty($mvp['item_id']))    return IMG_BASE . $mvp['item_id'] . '.png';
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid          = (int)($_POST['id'] ?? 0);
    $nome         = trim($_POST['nome']         ?? '');
    $mapa         = trim($_POST['mapa']         ?? '');
    $spawn        = (int)($_POST['spawn']       ?? 0);
    $item_id      = (int)($_POST['item_id']     ?? 0) ?: null;
    $imagem_url   = trim($_POST['imagem_url']   ?? '') ?: null;
    $nivel        = (int)($_POST['nivel']       ?? 0) ?: null;
    $elemento     = trim($_POST['elemento']     ?? '') ?: null;

    if ($nome === '')        $erro = 'Nome é obrigatório.';
    elseif ($mapa === '')    $erro = 'Mapa é obrigatório.';
    elseif ($spawn <= 0) $erro = 'Tempo de respawn inválido.';

    if (!$erro) {
        if ($pid) {
            $stmt = $pdo->prepare(
                'UPDATE mvps SET nome=?, mapa=?, spawn_min=?,
                 item_id=?, imagem_url=?, nivel=?, elemento=? WHERE id=?'
            );
            $stmt->execute([$nome, $mapa, $spawn,
                            $item_id, $imagem_url, $nivel, $elemento, $pid]);
            $msg = "MVP <strong>" . htmlspecialchars($nome) . "</strong> atualizado.";
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO mvps (nome, mapa, spawn_min,
                 item_id, imagem_url, nivel, elemento)
                 VALUES (?,?,?,?,?,?,?)'
            );
            $stmt->execute([$nome, $mapa, $spawn,
                            $item_id, $imagem_url, $nivel, $elemento]);
            $msg = "MVP <strong>" . htmlspecialchars($nome) . "</strong> cadastrado.";
        }
        header('Location: admin_mvps.php?msg=' . urlencode(strip_tags($msg)));
        exit;
    }
}

if ($acao === 'toggle' && $id) {
    $pdo->prepare('UPDATE mvps SET ativo = !ativo WHERE id = ?')->execute([$id]);
    header('Location: admin_mvps.php');
    exit;
}
if ($acao === 'deletar' && $id) {
    $usos = $pdo->prepare('SELECT COUNT(*) FROM monitoramentos WHERE mvp_id = ?');
    $usos->execute([$id]);
    if ($usos->fetchColumn() > 0) {
        header('Location: admin_mvps.php?erro=' . urlencode('Não é possível excluir: MVP possui histórico de monitoramentos. Desative-o.'));
    } else {
        $pdo->prepare('DELETE FROM mvps WHERE id = ?')->execute([$id]);
        header('Location: admin_mvps.php?msg=' . urlencode('MVP excluído.'));
    }
    exit;
}

if (!$msg  && isset($_GET['msg']))  $msg  = htmlspecialchars($_GET['msg']);
if (!$erro && isset($_GET['erro'])) $erro = htmlspecialchars($_GET['erro']);

$busca = trim($_GET['q'] ?? '');
$sqlMvps = 'SELECT * FROM mvps' . ($busca ? ' WHERE nome LIKE ?' : '') . ' ORDER BY nome';
$stmtMvps = $pdo->prepare($sqlMvps);
$stmtMvps->execute($busca ? ['%' . $busca . '%'] : []);
$mvps = $stmtMvps->fetchAll();

$editando = null;
if ($acao === 'editar' && $id) {
    $stmtEdit = $pdo->prepare('SELECT * FROM mvps WHERE id = ?');
    $stmtEdit->execute([$id]);
    $editando = $stmtEdit->fetch();
}

$elementos = ['Neutro','Fogo','Água','Terra','Vento','Veneno','Sagrado','Sombrio','Fantasma','Maldito'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar MVPs — Admin</title>
    <link rel="icon" type="image/svg+xml" href="img/logo.svg">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_mvps.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-brand"><img src="img/logo.svg" class="nav-logo" alt=""><span>MVP Timer</span></a>
    <div class="nav-links">
        <a href="index.php"      class="nav-link">Dashboard</a>
        <a href="relatorios.php" class="nav-link">Relatórios</a>
        <a href="admin.php"      class="nav-link nav-admin">Admin</a>
        <a href="admin_mvps.php" class="nav-link nav-admin active">MVPs</a>
    </div>
    <div class="nav-user">
        <span class="badge-role badge-admin">admin</span>
        <span class="nav-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn-logout">Sair</a>
    </div>
</nav>

<div class="container container-wide">

    <?php if ($msg):  ?><div class="alert alert-sucesso"><?= $msg ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert alert-erro"><?= $erro ?></div><?php endif; ?>

    <div class="card">
        <h2><?= $editando ? '✏️ Editar MVP' : '➕ Novo MVP' ?></h2>

        <form method="POST" action="admin_mvps.php" class="form-mvp" id="formMvp">
            <input type="hidden" name="id" value="<?= $editando['id'] ?? 0 ?>">

            <div class="form-grid">
                <div class="campo campo-lg">
                    <label>Nome do MVP *</label>
                    <input type="text" name="nome" required maxlength="100"
                           value="<?= htmlspecialchars($editando['nome'] ?? $_POST['nome'] ?? '') ?>">
                </div>

                <div class="campo campo-lg">
                    <label>Mapa de respawn *</label>
                    <input type="text" name="mapa" required maxlength="100"
                           placeholder="ex: gef_fild14"
                           value="<?= htmlspecialchars($editando['mapa'] ?? $_POST['mapa'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label>Respawn (min) *</label>
                    <input type="number" name="spawn" required min="1" max="99999"
                           value="<?= (int)($editando['spawn_min'] ?? $_POST['spawn'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label>
                        ID do card (divine-pride)
                        <span class="hint">— gera URL automática</span>
                    </label>
                    <input type="number" name="item_id" min="1"
                           id="inputItemId"
                           placeholder="ex: 4236"
                           value="<?= (int)($editando['item_id'] ?? $_POST['item_id'] ?? '') ?: '' ?>"
                           oninput="previewImagem()">
                </div>

                <div class="campo campo-lg">
                    <label>
                        URL da imagem
                        <span class="hint">— tem prioridade sobre o ID</span>
                    </label>
                    <input type="url" name="imagem_url" maxlength="512"
                           id="inputImgUrl"
                           placeholder="https://..."
                           value="<?= htmlspecialchars($editando['imagem_url'] ?? $_POST['imagem_url'] ?? '') ?>"
                           oninput="previewImagem()">
                </div>

                <div class="campo">
                    <label>Nível</label>
                    <input type="number" name="nivel" min="1" max="999"
                           value="<?= (int)($editando['nivel'] ?? $_POST['nivel'] ?? '') ?: '' ?>">
                </div>

                <div class="campo">
                    <label>Elemento</label>
                    <select name="elemento">
                        <option value="">—</option>
                        <?php foreach ($elementos as $el): ?>
                        <option value="<?= $el ?>"
                            <?= ($editando['elemento'] ?? $_POST['elemento'] ?? '') === $el ? 'selected' : '' ?>>
                            <?= $el ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo campo-preview">
                    <label>Preview</label>
                    <div class="img-preview" id="imgPreview">
                        <?php
                        $urlPreview = $editando ? imagemMvp($editando) : '';
                        if ($urlPreview): ?>
                            <img src="<?= htmlspecialchars($urlPreview) ?>" alt="preview">
                        <?php else: ?>
                            <span class="sem-img">sem imagem</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?= $editando ? 'Salvar alterações' : 'Cadastrar MVP' ?>
                </button>
                <?php if ($editando): ?>
                <a href="admin_mvps.php" class="btn-cancelar">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header-row">
            <h2>📋 MVPs cadastrados <span class="badge-count"><?= count($mvps) ?></span></h2>
            <form method="GET" class="busca-form">
                <input type="text" name="q" placeholder="Buscar por nome…"
                       value="<?= htmlspecialchars($busca) ?>">
                <button type="submit" class="btn-filtrar">Buscar</button>
                <?php if ($busca): ?>
                <a href="admin_mvps.php" class="btn-cancelar">✕</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($mvps)): ?>
            <p class="vazio">Nenhum MVP encontrado.</p>
        <?php else: ?>
        <div class="mvp-grid-admin">
            <?php foreach ($mvps as $m):
                $img = imagemMvp($m);
            ?>
            <div class="mvp-admin-card <?= !$m['ativo'] ? 'inativo' : '' ?>">
                <div class="mvp-admin-img">
                    <?php if ($img): ?>
                        <img src="<?= htmlspecialchars($img) ?>"
                             alt="<?= htmlspecialchars($m['nome']) ?>"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <span class="sem-img" style="display:none">?</span>
                    <?php else: ?>
                        <span class="sem-img">?</span>
                    <?php endif; ?>
                </div>

                <div class="mvp-admin-info">
                    <strong><?= htmlspecialchars($m['nome']) ?></strong>
                    <span class="mvp-mapa"><?= htmlspecialchars($m['mapa']) ?></span>
                    <span class="mvp-spawn">⏱ <?= $m['spawn_min'] ?> min</span>
                    <?php if ($m['nivel']): ?>
                    <span class="mvp-extra">Lv <?= $m['nivel'] ?>
                        <?= $m['elemento'] ? '· ' . $m['elemento'] : '' ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="mvp-admin-acoes">
                    <a href="admin_mvps.php?acao=editar&id=<?= $m['id'] ?>"
                       class="btn-sm btn-warn" title="Editar">✏️</a>

                    <a href="admin_mvps.php?acao=toggle&id=<?= $m['id'] ?>"
                       class="btn-sm <?= $m['ativo'] ? 'btn-danger' : 'btn-success' ?>"
                       title="<?= $m['ativo'] ? 'Desativar' : 'Ativar' ?>">
                       <?= $m['ativo'] ? '✕' : '✓' ?>
                    </a>

                    <a href="admin_mvps.php?acao=deletar&id=<?= $m['id'] ?>"
                       class="btn-sm btn-neutral"
                       title="Excluir"
                       onclick="return confirm('Excluir <?= htmlspecialchars(addslashes($m['nome'])) ?>?')">
                       🗑
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
const IMG_BASE = 'https://static.divine-pride.net/images/items/cards/';

function previewImagem() {
    const urlInput  = document.getElementById('inputImgUrl').value.trim();
    const itemInput = document.getElementById('inputItemId').value.trim();
    const preview   = document.getElementById('imgPreview');

    const url = urlInput || (itemInput ? IMG_BASE + itemInput + '.png' : '');

    if (url) {
        preview.innerHTML = `<img src="${url}" alt="preview"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <span class="sem-img" style="display:none">erro</span>`;
    } else {
        preview.innerHTML = '<span class="sem-img">sem imagem</span>';
    }
}

<?php if ($editando): ?>
document.getElementById('formMvp').scrollIntoView({ behavior: 'smooth' });
<?php endif; ?>
</script>
</body>
</html>