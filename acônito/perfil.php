<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

$id = $_SESSION['usuario_id'];
$sql = "SELECT id, login, email, nome FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

$sql2 = "SELECT COUNT(*) AS total_bebidas, SUM(estoque) AS estoque_total FROM bebidas WHERE usuario_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$dados = $stmt2->get_result()->fetch_assoc();
?>

<?php include('includes/header.php'); ?>

 <link rel="stylesheet" href="css/style.css">
<div class="perfil-container" style="max-width:900px; margin:40px auto; padding:20px;">
  <h2 style="color:#ffd700;">Bem-vindo, <?= htmlspecialchars($usuario['login']) ?>!</h2>
  <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome'] ?? '-') ?></p>
  <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>

  <div style="margin-top:20px; background:#1a1a1a; padding:12px; border-radius:8px; border:1px solid #b8860b;">
    <p><strong>Total de bebidas cadastradas:</strong> <?= intval($dados['total_bebidas'] ?? 0) ?></p>
    <p><strong>Estoque total:</strong> <?= intval($dados['estoque_total'] ?? 0) ?></p>
  </div>
</div>
<script src="js/main.js"></script>