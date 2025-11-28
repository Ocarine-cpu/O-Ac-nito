<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Mapeamento de meses para PT-BR
$meses_pt = [
    '01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', 
    '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', 
    '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
];

$stmt = $conn->prepare("
    SELECT DATE_FORMAT(data_compra, '%Y-%m') AS ym, COUNT(*) AS cnt, IFNULL(SUM(total),0) AS receita
    FROM compras
    GROUP BY ym
    ORDER BY ym DESC
    LIMIT 12
");
$rows = [];
if ($stmt) {
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Formata os rótulos
    foreach(array_reverse($data) as $r) {
        list($ano, $mes) = explode('-', $r['ym']);
        $r['ym_pt'] = ($meses_pt[$mes] ?? $mes) . '/' . $ano;
        $rows[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ver Gráfico - O Acônito</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        .wrap { max-width:900px; margin:32px auto; padding:0 14px; }
        .chart-wrap { background:#1a1a1a; padding:15px; border-radius:10px; border:1px solid #2b1640; } 
        .chart-actions { margin-top:12px; display:flex; gap:10px; align-items:center; }
        .btn { padding:8px 12px; border-radius:8px; border:none; cursor:pointer; }
        .btn.primary { background:#7b4eff; color:#fff; }
        .btn.ghost { background:transparent; color:#ddd; border:1px solid rgba(255,255,255,0.06); }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/toast.php'; ?>

<main class="wrap">
    <h2 class="titulo">Ver Gráfico</h2>
    <p class="small-muted" style="margin-bottom:12px;">Desempenho de Vendas e Receita nos Últimos 12 Meses</p>
    <div class="chart-wrap" style="margin-top:14px;">
        <canvas id="graficoMain" height="120"></canvas>
        <div class="chart-actions">
            <button id="downloadChart" class="btn primary">Baixar imagem</button>
            <button id="saveServer" class="btn ghost">Salvar no servidor (para PDF)</button>
            <span id="saveMsg" style="margin-left:8px;color:#bdbdbd;"></span>
        </div>
    </div>
</main>

<script>
const dataRows = <?= json_encode($rows, JSON_HEX_TAG) ?>;
const labels = dataRows.map(r => r.ym_pt);
const vendas = dataRows.map(r => parseInt(r.cnt,10) || 0);
const receita = dataRows.map(r => parseFloat(r.receita) || 0);

const ctx = document.getElementById('graficoMain').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar', 
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Número de Compras', 
                data: vendas,
                backgroundColor: 'rgba(123,78,255,0.85)',
                borderColor: 'rgba(123,78,255,0.95)',
                borderWidth: 1,
                yAxisID: 'y1',
                barPercentage: 0.8, 
                categoryPercentage: 0.8 
            },
            {
                label: 'Receita (R$)',
                data: receita,
                type: 'bar', 
                backgroundColor: 'rgba(184,134,11,0.9)', 
                borderColor: 'rgba(184,134,11,0.95)',
                borderWidth: 1,
                yAxisID: 'y1', 
                barPercentage: 0.8, 
                categoryPercentage: 0.8 
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true, // LIGADO: Força o uso da proporção
        aspectRatio: 16 / 9,      // PROPORÇÃO: 16:9
        interaction: { mode: 'index', intersect: false },
        stacked: false,
        plugins: {
            title: {
                display: true,
                text: 'Vendas vs. Receita Mensal',
                color: '#ffd700',
                font: { size: 16 }
            },
            legend: { position: 'top', labels: { color: '#bdbdbd' } },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (context.parsed.y !== undefined) {
                            if (context.dataset.label === 'Receita (R$)') {
                                // Formata para moeda (R$)
                                label += ': R$ ' + Number(context.parsed.y).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
                            } else {
                                label += ': ' + Number(context.parsed.y).toLocaleString('pt-BR');
                            }
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                title: { display: true, text: 'Mês/Ano', color: '#bdbdbd' }, 
                ticks: { color: '#bdbdbd' },
                grid: { color: 'rgba(255,255,255,0.05)' }
            },
            y1: {
                beginAtZero: true,
                position: 'left',
                title: { display: true, text: 'Valor/Contagem', color: 'rgba(255,255,255,0.95)' },
                ticks: { 
                    callback: function(v){ return v.toLocaleString('pt-BR'); },
                    color: 'rgba(255,255,255,0.95)'
                },
                grid: { color: 'rgba(255,255,255,0.08)' }
            }
        }
    }
});

// Baixar imagem localmente
document.getElementById('downloadChart').addEventListener('click', function () {
    const a = document.createElement('a');
    a.href = document.getElementById('graficoMain').toDataURL('image/png');
    a.download = 'grafico_aconiito.png';
    a.click();
});

// Salvar imagem no servidor para uso no PDF
document.getElementById('saveServer').addEventListener('click', function () {
    const imgData = document.getElementById('graficoMain').toDataURL('image/png');
    // envia para admin/save_chart.php
    fetch('save_chart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image: imgData })
    })
    .then(r => r.json())
    .then(res => {
        if (res.ok) {
            document.getElementById('saveMsg').textContent = 'Gráfico salvo com sucesso.';
        } else {
            document.getElementById('saveMsg').textContent = 'Erro ao salvar: ' + (res.msg || 'erro');
        }
    })
    .catch(err => {
        document.getElementById('saveMsg').textContent = 'Erro: ' + err.message;
    });
});
</script>
<?php include '../includes/footer.php'; ?>

</body>
</html>