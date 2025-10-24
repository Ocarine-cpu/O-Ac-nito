<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$logado = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>O Acônito</title>

  <!-- Link CSS Global -->
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- Ícones Material -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body>
<header class="header">
  <a href="index.php" class="logo">
    <img src="img/aconiitologo-Photoroom.png" alt="Logo O Acônito">
    <h1>O Acônito</h1>
  </a>

  <nav class="menu">
    <?php if ($logado): ?>
      <a href="perfil.php"><span class="material-symbols-outlined">person</span> Perfil</a>
      <a href="addBebidas.php"><span class="material-symbols-outlined">inventory_2</span> Adicionar</a>
      <a href="logout.php"><span class="material-symbols-outlined">logout</span> Sair</a>
    <?php else: ?>
      <a href="login.php"><span class="material-symbols-outlined">login</span> Login</a>
    <?php endif; ?>
  </nav>
</header>
