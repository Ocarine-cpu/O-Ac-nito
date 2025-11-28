<?php
// admin/template_pdf.php
// Espera: $compra (array) e $items (array)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Compra <?= intval($compra['id']) ?></title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size:12px; color:#111; }
    header { text-align:center; margin-bottom:10px; }
    h2 { margin:0; }
    .meta { margin-top:8px; }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:left; }
    th { background:#f7f7f7; }
    .tot { text-align:right; font-weight:700; margin-top:8px; }
</style>
</head>
<body>
<header>
    <h2>O Acônito — Compra #<?= intval($compra['id']) ?></h2>
    <div class="meta">
        <div>Cliente: <?= htmlspecialchars($compra['login'] . ' — ' . ($compra['nome'] ?? '')) ?></div>
        <div>Data: <?= date('d/m/Y H:i', strtotime($compra['data_compra'])) ?></div>
    </div>
</header>

<table>
<thead>
<tr><th>Produto</th><th>Quantidade</th><th>Preço unit.</th><th>Subtotal</th></tr>
</thead>
<tbody>
<?php foreach ($items as $it): 
    $sub = ($it['quantidade'] * $it['preco_unitario']);
?>
<tr>
    <td><?= htmlspecialchars($it['nome']) ?></td>
    <td><?= intval($it['quantidade']) ?></td>
    <td>R$ <?= number_format($it['preco_unitario'],2,',','.') ?></td>
    <td>R$ <?= number_format($sub,2,',','.') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="tot">Total: R$ <?= number_format($compra['total'],2,',','.') ?></p>
<?php include '../includes/footer.php'; ?>

</body>
</html>
