<?php
session_start();
include 'includes/db_connect.php';
include 'includes/toast.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$id = intval($_GET['id']);

$sql = "SELECT b.*, u.login AS dono_login FROM bebidas b LEFT JOIN usuarios u ON b.usuario_id = u.id WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$bebida = $res->fetch_assoc();

if (!$bebida) {
    echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='utf-8'><title>Bebida não encontrada</title><link href='https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined' rel='stylesheet'><link rel='stylesheet' href='css/style.css'></head><body>";
    include 'includes/header.php';
    echo "<main class='container'><p class='vazio'>Bebida não encontrada.</p></main><script src='js/main.js'></script></body></html>";
    exit();
}

$usuarioLogado = $_SESSION['usuario_id'] ?? null;
$ehDono = $usuarioLogado && $usuarioLogado == $bebida['usuario_id'];
$usuario_tipo = $_SESSION['usuario_tipo'] ?? null;

// Adicionar ao carrinho (apenas clientes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario_tipo === 'cliente') {
    $quantidade = intval($_POST['quantidade'] ?? 1);
    if ($quantidade < 1) $quantidade = 1;

    // Verifica estoque
    if ($bebida['estoque'] < $quantidade) {
        $msg = "Estoque insuficiente.";
    } else {
        // insere/atualiza item no carrinho
        $sqlUp = "INSERT INTO carrinho (usuario_id, bebida_id, quantidade) VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE quantidade = quantidade + VALUES(quantidade)";
        $stmtUp = $conn->prepare($sqlUp);
        $stmtUp->bind_param("iii", $usuarioLogado, $id, $quantidade);
        if ($stmtUp->execute()) {
            $msg = "Adicionado ao carrinho!";
        } else {
            $msg = "Erro ao adicionar ao carrinho: " . $stmtUp->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($bebida['nome']) ?> - Detalhes</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="detalhe-bebida">
            
            <div class="bloco-imagem">
                <img src="<?= htmlspecialchars($bebida['imagem'] ?: 'img/default.png') ?>" alt="<?= htmlspecialchars($bebida['nome']) ?>" class="img-detalhe">
                
                <div class="acoes-detalhe">
                    <a href="index.php" class="btn-detalhes">Voltar</a>
                    <?php if ($ehDono): ?>
                        <a href="editBebida.php?id=<?= $bebida['id'] ?>" class="btn-principal">Editar</a>
                        <form method="POST" action="deleteBebida.php" onsubmit="return confirmDelete();" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $bebida['id'] ?>">
                            <button type="submit" class="btn-danger">Excluir</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="info-bebida">
                <h2><?= htmlspecialchars($bebida['nome']) ?></h2>
                
                <?php if (!empty($bebida['litragem'])): ?>
                    <p class="litragem"><?= htmlspecialchars($bebida['litragem']) ?></p>
                <?php endif; ?>
                
                <div class="conteudo-detalhes">
                    
                    <div class="descricao-box">
                        <div class="descricao-conteudo">
                             <p><strong><u>Descrição:</u></strong></p>
                             <p style="text-align:justify;"><?= nl2br(htmlspecialchars($bebida['descricao'])) ?></p>
                        </div>
                    </div>

                    <div class="lateral-info">
                        <div class="dados-box">
                            <div class="info-card">
                                <p class="preco">Preço:</p>
                                <p class="valor-info">R$ <?= number_format($bebida['preco'], 2, ',', '.') ?></p>
                            </div>
                            <div class="info-card">
                                <p>Em estoque:</p>
                                <p class="valor-info"><?= intval($bebida['estoque']) ?></p>
                            </div>
                        </div>

                        <?php if (!empty($bebida['ingredientes'])): ?>
                            <div class="dados-box ingredientes-box">
                                <p style="font-weight: 700; color: #b8860b; margin-bottom: 8px;">Ingredientes:</p>
                                
                                <ul class="lista-ingredientes-ul">
                                    <?php 
                                    $ingredientes_array = explode("\n", $bebida['ingredientes']);
                                    foreach ($ingredientes_array as $ingrediente) {
                                        $ingrediente_limpo = trim($ingrediente);
                                        if (!empty($ingrediente_limpo)) {
                                            echo '<li>' . htmlspecialchars($ingrediente_limpo) . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($usuario_tipo === 'cliente'): ?>
                            <div class="dados-box">
                                <?php if (!empty($msg)): ?><p class="mensagem-sucesso"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
                                <form method="POST">
                                    <label>Quantidade:</label>
                                    <input type="number" name="quantidade" min="1" value="1" style="width:80px; margin-bottom:8px;">
                                    <?php if (intval($bebida['estoque']) > 0): ?>
                                        <button type="submit" class="btn-principal">Adicionar ao carrinho</button>
                                    <?php else: ?>
                                        <button type="button" class="btn-principal" disabled>Sem estoque</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
    <?php include 'includes/footer.php'; ?>

</body>
</html>

<?php $conn->close(); ?>
