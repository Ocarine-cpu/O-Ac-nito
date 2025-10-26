<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

// Seleção de dados (já incluía todos os campos, incluindo litragem após o ALTER TABLE)
$stmt = $conn->prepare("SELECT * FROM bebidas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$bebida = $res->fetch_assoc();
if (!$bebida) { header("Location: index.php"); exit(); }
if ($bebida['usuario_id'] != $usuario_id) { header("Location: index.php?erro=nao_autorizado"); exit(); }

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    // NOVO CAMPO: Litragem
    $litragem = trim($_POST['litragem'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $preco = floatval($_POST['preco'] ?? 0);
    $estoque = intval($_POST['estoque'] ?? 0);
    $imagem = trim($_POST['imagem'] ?? '');

    // SQL ATUALIZADO: inclui 'litragem'
    $stmt2 = $conn->prepare("UPDATE bebidas SET nome=?, litragem=?, descricao=?, ingredientes=?, preco=?, estoque=?, imagem=? WHERE id=? AND usuario_id=?");
    // bind_param ATUALIZADO: 'ssssdisii' (s para nome, litragem, descricao, ingredientes, e imagem)
    $stmt2->bind_param("ssssdisii", $nome, $litragem, $descricao, $ingredientes, $preco, $estoque, $imagem, $id, $usuario_id);
    if ($stmt2->execute()) {
        header("Location: detalhes.php?id=" . $id . "&editado=1");
        exit();
    } else {
        $erro = "Erro ao atualizar: " . $stmt2->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Editar Bebida - O Acônito</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="edit-bebida-container">
            <h2 style="text-align:center; color:#ffd700;">Editar Bebida</h2>
            <?php if (!empty($erro)): ?><p class="erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

            <form method="POST">
                <input type="text" name="nome" placeholder="Nome da bebida" value="<?= htmlspecialchars($bebida['nome']) ?>" required>
                <input type="text" name="litragem" placeholder="Litragem (ex: 355ml, 1L)" value="<?= htmlspecialchars($bebida['litragem'] ?? '') ?>">
                <textarea name="descricao" placeholder="Descrição" required><?= htmlspecialchars($bebida['descricao']) ?></textarea>
                <textarea name="ingredientes" placeholder="Ingredientes (Liste um por linha)"><?= htmlspecialchars($bebida['ingredientes'] ?? '') ?></textarea>
                <input type="number" step="0.01" name="preco" placeholder="Preço (R$)" value="<?= htmlspecialchars($bebida['preco']) ?>" required>
                <input type="number" name="estoque" placeholder="Estoque" value="<?= htmlspecialchars($bebida['estoque']) ?>" required>
                <input type="text" name="imagem" placeholder="URL da imagem (ex: img/pocao.png)" value="<?= htmlspecialchars($bebida['imagem']) ?>">
                <button type="submit" class="btn-principal btn-full-width">Salvar Alterações</button>
            </form>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>

<?php $conn->close(); ?>