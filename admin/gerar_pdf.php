<?php
// admin/gerar_pdf.php
session_start();
include '../includes/db_connect.php';

// caminho do DOMPDF
$dompdf_autoload = __DIR__ . '/../vendor/dompdf/autoload.inc.php';
$hasDompdf = file_exists($dompdf_autoload);
if ($hasDompdf) require_once $dompdf_autoload;

// proteção: apenas admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Se veio ?compra=ID (gera PDF/CSV da compra individual) -> manter comportamento antigo
$compraId = isset($_GET['compra']) ? intval($_GET['compra']) : 0;
if ($compraId > 0) {
    // busca compra
    $stmt = $conn->prepare("SELECT c.*, u.login, u.nome FROM compras c LEFT JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?");
    $stmt->bind_param("i", $compraId);
    $stmt->execute();
    $compra = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$compra) {
        die("Compra não encontrada.");
    }

    // itens da compra
    $stmtItems = $conn->prepare("
        SELECT ic.*, b.nome 
        FROM itens_compra ic 
        LEFT JOIN bebidas b ON ic.bebida_id = b.id 
        WHERE ic.compra_id = ?
    ");
    $stmtItems->bind_param("i", $compraId);
    $stmtItems->execute();
    $items = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtItems->close();

    // Gera template simples
    ob_start();
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><style>
    body{font-family:DejaVu Sans, sans-serif;font-size:13px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:6px}
    </style></head><body>
    <h2>Compra #<?= intval($compra['id']) ?></h2>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($compra['login'] . ' - ' . ($compra['nome'] ?? '')) ?></p>
    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($compra['data_compra'])) ?></p>
    <table><thead><tr><th>Produto</th><th>Qtd</th><th>Preço unit.</th><th>Subtotal</th></tr></thead><tbody>
    <?php foreach ($items as $it): 
        $sub = $it['quantidade'] * $it['preco_unitario'];
    ?>
        <tr>
            <td><?= htmlspecialchars($it['nome']) ?></td>
            <td><?= intval($it['quantidade']) ?></td>
            <td>R$ <?= number_format($it['preco_unitario'],2,',','.') ?></td>
            <td>R$ <?= number_format($sub,2,',','.') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table>
    <p><strong>Total:</strong> R$ <?= number_format($compra['total'],2,',','.') ?></p>
    </body></html>
    <?php
    $html = ob_get_clean();

    if ($hasDompdf && class_exists('Dompdf\Dompdf')) {
        $options = new Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("compra_{$compraId}.pdf", ["Attachment" => true]);
        exit;
    } else {
        // fallback CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=compra_' . $compraId . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Produto','Quantidade','Preço unit.','Subtotal']);
        foreach ($items as $it) {
            $sub = $it['quantidade'] * $it['preco_unitario'];
            fputcsv($out, [
                $it['nome'],
                $it['quantidade'],
                number_format($it['preco_unitario'], 2, ',', '.'),
                number_format($sub, 2, ',', '.')
            ]);
        }
        fclose($out);
        exit;
    }
}

// === Caso geral: exportar relatório completo (usuários + bebidas + gráfico se houver) ===

// BUSCA USUÁRIOS
$usuarios = [];
if ($res = $conn->query("SELECT id, login, nome, tipo, data_cadastro FROM usuarios ORDER BY id")) {
    while ($r = $res->fetch_assoc()) $usuarios[] = $r;
    $res->free();
}

// BUSCA BEBIDAS
$bebidas = [];
if ($res = $conn->query("SELECT id, nome, sabor, estoque, preco FROM bebidas ORDER BY nome ASC")) {
    while ($r = $res->fetch_assoc()) $bebidas[] = $r;
    $res->free();
}

// verifica gráfico salvo
$graficoPath = __DIR__ . '/../uploads/grafico.png';
$graficoBase64 = '';
if (file_exists($graficoPath)) {
    $graficoBase64 = base64_encode(file_get_contents($graficoPath));
}

// monta HTML
ob_start();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size:12px; color:#111; }
h1 { text-align:center; margin-bottom:14px; }
.section { margin-top:18px; }
table { width:100%; border-collapse:collapse; margin-top:8px; }
th, td { border:1px solid #ccc; padding:6px 8px; text-align:left; font-size:12px }
th { background:#f0f0f0; }
.small { font-size:11px; color:#444; }
</style>
</head>
<body>
<h1>Relatório Geral - O Acônito</h1>

<div class="section">
<h2>Usuários</h2>
<table>
<thead><tr><th>ID</th><th>Login</th><th>Nome</th><th>Tipo</th><th>Membro desde</th></tr></thead>
<tbody>
<?php foreach ($usuarios as $u): ?>
<tr>
<td><?= intval($u['id']) ?></td>
<td><?= htmlspecialchars($u['login']) ?></td>
<td><?= htmlspecialchars($u['nome']) ?></td>
<td><?= htmlspecialchars($u['tipo']) ?></td>
<td><?= date('d/m/Y', strtotime($u['data_cadastro'])) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="section">
<h2>Bebidas</h2>
<table>
<thead><tr><th>ID</th><th>Nome</th><th>Sabor</th><th>Estoque</th><th>Preço</th></tr></thead>
<tbody>
<?php foreach ($bebidas as $b): ?>
<tr>
<td><?= intval($b['id']) ?></td>
<td><?= htmlspecialchars($b['nome']) ?></td>
<td><?= htmlspecialchars($b['sabor']) ?></td>
<td><?= intval($b['estoque']) ?></td>
<td>R$ <?= number_format($b['preco'],2,',','.') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php if ($graficoBase64 !== ''): ?>
<div class="section">
<h2>Gráfico do sistema</h2>
<p class="small">Gráfico gerado no sistema (últimos 12 meses).</p>
<img src="data:image/png;base64,<?= $graficoBase64 ?>" style="width:100%; max-width:700px; margin-top:8px;">
</div>
<?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();

if ($hasDompdf && class_exists('Dompdf\Dompdf')) {
    $options = new Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream('relatorio_aconiito.pdf', ['Attachment' => true]);
    exit;
} else {
    // fallback CSV (exporta usuários e bebidas em arquivos separados via zip não implementado)
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}
