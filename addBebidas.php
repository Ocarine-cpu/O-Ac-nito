<?php
session_start();
include 'includes/db_connect.php';
include 'includes/toast.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $nome = trim($_POST['nome'] ?? '');
    $litragem = trim($_POST['litragem'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $preco = floatval($_POST['preco'] ?? 0);
    $estoque = intval($_POST['estoque'] ?? 0);
    $imagem = trim($_POST['imagem'] ?? '');

    $sql = "INSERT INTO bebidas (usuario_id, nome, litragem, descricao, ingredientes, preco, estoque, imagem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssdis", $usuario_id, $nome, $litragem, $descricao, $ingredientes, $preco, $estoque, $imagem);
    
    if ($stmt->execute()) {
        header("Location: index.php?sucesso=1");
        exit();
    } else {
        $erro = "Erro ao salvar: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Adicionar Bebida - O Acônito</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="add-bebida-container">
            <h2 style="text-align:center; color:#ffd700;">Adicionar Nova Bebida</h2>
            <?php if (!empty($erro)): ?><p class="erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

            <form method="POST">
                <input type="text" name="nome" placeholder="Nome da bebida" required>
                <input type="text" name="litragem" placeholder="Litragem (ex: 355ml, 1L)">
                <textarea name="descricao" placeholder="Descrição" required></textarea>
                <textarea name="ingredientes" placeholder="Ingredientes (Liste um por linha)"></textarea>
                <input type="number" step="0.01" name="preco" placeholder="Preço (R$)" required>
                <input type="number" name="estoque" placeholder="Estoque" required>
                <input type="text" name="imagem" placeholder="URL da imagem (ex: img/pocao.png)">
                <button type="submit" class="btn-principal btn-full-width">Salvar</button>
            </form>
        </div>
    </main>

    <script src="js/main.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
