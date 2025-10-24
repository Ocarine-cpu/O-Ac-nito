<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

// verificar propriedade
$stmt = $conn->prepare("SELECT * FROM bebidas WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$bebida = $result->fetch_assoc();
if ($bebida['usuario_id'] != $usuario_id) {
    header("Location: index.php?erro=nao_autorizado");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];
    $imagem = $_POST['imagem'];

    $stmt2 = $conn->prepare("UPDATE bebidas SET nome=?, descricao=?, preco=?, estoque=?, imagem=? WHERE id=? AND usuario_id=?");
    $stmt2->bind_param("ssdssii", $nome, $descricao, $preco, $estoque, $imagem, $id, $usuario_id);
    $stmt2->execute();

    header("Location: index.php?editado=1");
    exit();
}
?>
 <link rel="stylesheet" href="css/style.css">
<div class="add-bebida-container">
  <h2 style="text-align:center; color:#ffd700;">Adicionar Nova Bebida</h2>
  <?php if (!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>

  <form method="POST">
    <input type="text" name="nome" placeholder="Nome da bebida" required>
    <textarea name="descricao" placeholder="Descrição" required></textarea>
    <input type="number" step="0.01" name="preco" placeholder="Preço (R$)" required>
    <input type="number" name="estoque" placeholder="Estoque" required>
    <input type="text" name="imagem" placeholder="URL da imagem (ex: img/pocao.png)">
    <button type="submit" class="btn-principal">Salvar</button>
  </form>
</div>

<script src="js/main.js"></script>