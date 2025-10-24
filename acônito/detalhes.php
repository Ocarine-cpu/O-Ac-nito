<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM bebidas WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$bebida = $result->fetch_assoc();
?>

<?php include('includes/header.php'); ?>

 <link rel="stylesheet" href="css/style.css">
<div class="add-bebida-container">
  <h2 style="text-align:center; color:#ffd700;"><?= htmlspecialchars($bebida['nome']) ?></h2>
  <img src="<?= htmlspecialchars($bebida['imagem']) ?>" alt="<?= htmlspecialchars($bebida['nome']) ?>" style="max-width:250px; display:block; margin:20px auto; border-radius:10px;">
  <p style="text-align:justify;"><?= htmlspecialchars($bebida['descricao']) ?></p>
  <p><strong>Preço:</strong> R$ <?= number_format($bebida['preco'], 2, ',', '.') ?></p>
  <p><strong>Estoque:</strong> <?= htmlspecialchars($bebida['estoque']) ?></p>
</div>

<script src="js/main.js"></script>