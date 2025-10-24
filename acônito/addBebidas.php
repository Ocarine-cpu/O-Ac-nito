<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];
    $imagem = $_POST['imagem'] ?: 'img/default.png';

    $stmt = $conn->prepare("INSERT INTO bebidas (usuario_id, nome, descricao, preco, estoque, imagem) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdis", $usuario_id, $nome, $descricao, $preco, $estoque, $imagem);

    if ($stmt->execute()) {
        header("Location: index.php?sucesso=1");
        exit();
    } else {
        $erro = "Erro: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<?php include('includes/header.php'); ?>

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