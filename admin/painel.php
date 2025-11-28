<?php
// admin/painel.php
session_start();
include '../includes/db_connect.php';


// proteção: apenas admin (usa as mesmas sessões que seu projeto)
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// === Estatísticas leves ===
// total usuarios (exclui admins da contagem se desejar mostrar apenas clientes; aqui conta todos)
$totalUsuarios = 0;
if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM usuarios")) {
    $row = $res->fetch_assoc();
    $totalUsuarios = intval($row['cnt'] ?? 0);
    $res->free();
}

// total compras
$totalCompras = 0;
if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM compras")) {
    $row = $res->fetch_assoc();
    $totalCompras = intval($row['cnt'] ?? 0);
    $res->free();
}

// total faturamento
$totalFaturamento = 0.0;
if ($res = $conn->query("SELECT IFNULL(SUM(total),0) AS soma FROM compras")) {
    $row = $res->fetch_assoc();
    $totalFaturamento = floatval($row['soma'] ?? 0.0);
    $res->free();
}

// === Últimos 5 usuários (exclui contas admin da listagem) ===
$ultimosUsuarios = [];
$stmt = $conn->prepare("SELECT id, login, nome, data_cadastro FROM usuarios WHERE tipo <> 'admin' ORDER BY id DESC LIMIT 5");
if ($stmt) {
    $stmt->execute();
    $rs = $stmt->get_result();
    while ($r = $rs->fetch_assoc()) $ultimosUsuarios[] = $r;
    $stmt->close();
}

// === Últimas 5 compras ===
$ultimasCompras = [];
$stmt2 = $conn->prepare("SELECT c.id, c.usuario_id, c.total, c.data_compra, u.login AS cliente FROM compras c LEFT JOIN usuarios u ON c.usuario_id = u.id ORDER BY c.id DESC LIMIT 5");
if ($stmt2) {
    $stmt2->execute();
    $rs2 = $stmt2->get_result();
    while ($r = $rs2->fetch_assoc()) $ultimasCompras[] = $r;
    $stmt2->close();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Painel - O Acônito</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    /* Estilo Acônito Dark (leve) */
    .painel-wrap { max-width:1200px; margin:40px auto; padding:0 16px; }
    .cards { display:flex; gap:18px; margin-bottom:26px; flex-wrap:wrap; }
    .card { flex:1; min-width:180px; background:#141218; border:1px solid #2b1640; padding:18px; border-radius:10px; text-align:center; box-shadow:0 6px 20px rgba(0,0,0,0.5); }
    .card h3 { color:#bfa7ff; margin:0 0 8px; font-size:1rem; }
    .card .valor { color:#ffd700; font-weight:700; font-size:1.4rem; }

    .table-box { background:#131216; border:1px solid #2b1640; border-radius:10px; padding:14px; margin-bottom:18px; }
    .table-box h4 { color:#ccbbff; margin:0 0 12px; }
    table.small { width:100%; border-collapse:collapse; color:#f5dfc9; }
    table.small th, table.small td { padding:8px 10px; border-bottom:1px dashed rgba(255,255,255,0.04); font-size:0.95rem; }
    table.small th { color:#ffd700; text-align:left; }

    .toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:12px; flex-wrap:wrap; }
    .left-actions { display:flex; gap:12px; flex-wrap:wrap; }
    .btn-ac { padding:10px 16px; background:#7b4eff; color:#fff; text-decoration:none; border-radius:8px; box-shadow:0 6px 16px rgba(0,0,0,0.4); display:inline-block; }
    .btn-ac:hover { background:#9b72ff; }

    .btn-pdf { padding:10px 16px; background:#ffd700; color:#111; text-decoration:none; border-radius:8px; font-weight:700; }
    .btn-pdf:hover { opacity:0.95; }

    @media (max-width:800px) {
      .cards { flex-direction:column; }
      .table-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/toast.php'; ?>

<main class="painel-wrap">
    <h2 class="titulo">Painel Administrativo</h2>

    <div class="cards" aria-hidden="false">
        <div class="card">
            <h3>Total de Usuários</h3>
            <div class="valor"><?= $totalUsuarios ?></div>
        </div>
        <div class="card">
            <h3>Total de Compras</h3>
            <div class="valor"><?= $totalCompras ?></div>
        </div>
        <div class="card">
            <h3>Faturamento</h3>
            <div class="valor">R$ <?= number_format($totalFaturamento,2,',','.') ?></div>
        </div>
    </div>

    <div class="toolbar">
        <div class="left-actions">
            <a class="btn-ac" href="usuarios.php">Consulta Usuário</a>
            <a class="btn-ac" href="compras.php">Últimas Compras</a>
            <a class="btn-ac" href="grafico.php">Ver Gráfico</a>
        </div>

        <div class="right-actions">
            <form method="POST" action="gerar_pdf.php" style="display:inline;">
                <button class="btn-pdf" type="submit" style="border:none; cursor:pointer;">Exportar PDF</button>
            </form>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px; margin-top:18px; margin-bottom:18px;">
        <div class="table-box">
            <h4>Últimos 5 Usuários</h4>
            <table class="small">
                <thead><tr><th>ID</th><th>Login</th><th>Data</th></tr></thead>
                <tbody>
                <?php if (count($ultimosUsuarios) === 0): ?>
                    <tr><td colspan="3" class="vazio">Nenhum usuário encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($ultimosUsuarios as $u): ?>
                        <tr>
                            <td><?= intval($u['id']) ?></td>
                            <td><?= htmlspecialchars($u['login']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($u['data_cadastro'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-box">
            <h4>Últimas 5 Compras</h4>
            <table class="small">
                <thead><tr><th>ID</th><th>Cliente</th><th>Valor</th><th>Data</th></tr></thead>
                <tbody>
                <?php if (count($ultimasCompras) === 0): ?>
                    <tr><td colspan="4" class="vazio">Nenhuma compra registrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($ultimasCompras as $c): ?>
                        <tr>
                            <td><?= intval($c['id']) ?></td>
                            <td><?= htmlspecialchars($c['cliente'] ?? '—') ?></td>
                            <td>R$ <?= number_format($c['total'],2,',','.') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($c['data_compra'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>

</body>
</html>
