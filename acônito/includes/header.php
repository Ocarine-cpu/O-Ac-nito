<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$logado = isset($_SESSION['usuario_id']);
$usuario_nome = $_SESSION['usuario_nome'] ?? null;
?>
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