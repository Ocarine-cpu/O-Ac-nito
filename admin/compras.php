<?php
session_start();
include '../includes/db_connect.php';

// proteção: apenas admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$limit = 200;
$compras = [];

$stmt = $conn->prepare("
    SELECT c.id, c.usuario_id, c.total, c.data_compra, u.login
    FROM compras c 
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    ORDER BY c.data_compra DESC 
    LIMIT ?
");

if ($stmt) {
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $compras[] = $r;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Últimas Compras - O Acônito</title>

    <link rel="stylesheet" href="../css/style.css">
    <style>
        .wrap { max-width:1100px; margin:32px auto; padding:0 14px; }
        .table-box { background:#131216; padding:12px; border-radius:10px; border:1px solid #2b1640; }
        table.full { width:100%; border-collapse:collapse; color:#f5dfc9; }
        table.full th, table.full td { padding:10px; border-bottom:1px dashed rgba(255,255,255,0.04); }
        table.full th { color:#ffd700; }
        .btn-ac { padding:8px 12px; background:#7b4eff; color:#fff; border-radius:8px; text-decoration:none; }
        .btn-ac.pdf { background:#333; }
        
        /* Estilos de Alinhamento e Espaçamento para compras.php */
        /* THs */
        table.full th.th-id, table.full td.td-id { text-align:center; width:5%; min-width:50px; white-space:nowrap; }
        table.full th.th-usuario, table.full td.td-usuario { text-align:left; }
        table.full th.th-total, table.full td.td-total { text-align:right; width:10%; min-width:90px; white-space:nowrap; }
        table.full th.th-data, table.full td.td-data { text-align:center; width:15%; min-width:140px; white-space:nowrap; }
        table.full th.th-ac, table.full td.td-ac { text-align:center; padding:5px 8px; width:1%; min-width:70px; white-space:nowrap; }
    </style>
</head>

<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/toast.php'; ?>

<main class="wrap">
    <h2 class="titulo">Últimas Compras</h2>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <p class="small-muted">Exibindo até <?= intval($limit) ?> compras.</p>
        <a class="btn-ac" href="gerar_pdf.php">Exportar Relatório (PDF/CSV)</a>
    </div>

    <div class="table-box">
        <table class="full">
            <thead>
                <tr>
                    <th class="th-id">ID</th>
                    <th class="th-usuario">Usuário</th>
                    <th class="th-total">Total</th>
                    <th class="th-data">Data</th>
                    <th class="th-ac">Ações</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($compras)): ?>
                <tr><td colspan="5" class="vazio">Nenhuma compra registrada.</td></tr>

            <?php else: ?>
                <?php foreach ($compras as $c): ?>
                <tr>
                    <td class="td-id"><?= intval($c['id']) ?></td>
                    <td class="td-usuario"><?= htmlspecialchars($c['login'] ?? '—') ?></td>
                    <td class="td-total">R$ <?= number_format($c['total'], 2, ',', '.') ?></td>
                    <td class="td-data"><?= date('d/m/Y H:i', strtotime($c['data_compra'])) ?></td>
                    <td class="td-ac">
                        <a class="btn-ac pdf" href="gerar_pdf.php?compra=<?= $c['id'] ?>">PDF</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

</body>
</html>