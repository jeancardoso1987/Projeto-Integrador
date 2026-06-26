<?php


require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../db/conexao.php';

header('Content-Type: application/json');

if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$acao       = $_GET['acao'] ?? $_POST['acao'] ?? '';

switch ($acao) {

    
    case 'listar_mvps':
        $sid = (int)($_GET['servidor_id'] ?? 0);
        
        $stmt = $pdo->prepare(
            'SELECT id, nome, mapa, spawn_min,
                    item_id, imagem_url, nivel, elemento
             FROM mvps WHERE ativo = 1 ORDER BY nome'
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();

        
        foreach ($rows as &$r) {
            if (!empty($r['imagem_url'])) {
                $r['img'] = $r['imagem_url'];
            } elseif (!empty($r['item_id'])) {
                $r['img'] = 'https://static.divine-pride.net/images/items/cards/' . $r['item_id'] . '.png';
            } else {
                $r['img'] = '';
            }
        }
        unset($r);

        echo json_encode($rows);
        break;

   
    case 'iniciar':
        $mvp_id      = (int)($_POST['mvp_id']      ?? 0);
        $servidor_id = (int)($_POST['servidor_id'] ?? 0);
        $morto_em    = trim($_POST['morto_em']     ?? '');
        $spawn_est   = trim($_POST['spawn_est']    ?? '');

        if (!$mvp_id || !$servidor_id) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados inválidos']);
            exit;
        }

        
        $morto_em  = preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $morto_em)  ? $morto_em  : null;
        $spawn_est = preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $spawn_est) ? $spawn_est : null;

        
        $chk = $pdo->prepare(
            'SELECT id FROM monitoramentos
             WHERE usuario_id = ? AND mvp_id = ? AND servidor_id = ? AND finalizado = 0 LIMIT 1'
        );
        $chk->execute([$usuario_id, $mvp_id, $servidor_id]);
        if ($chk->fetch()) {
            echo json_encode(['erro' => 'MVP já está sendo monitorado']);
            exit;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO monitoramentos (usuario_id, mvp_id, servidor_id, morto_em, spawn_est)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$usuario_id, $mvp_id, $servidor_id, $morto_em, $spawn_est]);

        echo json_encode(['ok' => true, 'monitoramento_id' => $pdo->lastInsertId()]);
        break;

    
    case 'matar':
        $mid = (int)($_POST['monitoramento_id'] ?? 0);
        if (!$mid) { echo json_encode(['erro' => 'ID inválido']); exit; }

       
        $stmt = $pdo->prepare(
            'SELECT m.id, mv.spawn_min
             FROM monitoramentos m
             JOIN mvps mv ON mv.id = m.mvp_id
             WHERE m.id = ? AND m.usuario_id = ?'
        );
        $stmt->execute([$mid, $usuario_id]);
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(['erro' => 'Monitoramento não encontrado']);
            exit;
        }

        $spawn_est = date('Y-m-d H:i:s', time() + ($row['spawn_min'] * 60));

        $upd = $pdo->prepare(
            'UPDATE monitoramentos
             SET morto_em = NOW(), spawn_est = ?, finalizado = 0
             WHERE id = ? AND usuario_id = ?'
        );
        $upd->execute([$spawn_est, $mid, $usuario_id]);

        echo json_encode(['ok' => true, 'spawn_est' => $spawn_est]);
        break;

    
    case 'finalizar':
        $mid = (int)($_POST['monitoramento_id'] ?? 0);
        if (!$mid) { echo json_encode(['erro' => 'ID inválido']); exit; }

        $stmt = $pdo->prepare(
            'UPDATE monitoramentos SET finalizado = 1
             WHERE id = ? AND usuario_id = ?'
        );
        $stmt->execute([$mid, $usuario_id]);

        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Ação desconhecida']);
}