<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$logado = isset($_SESSION['usuario_id']);
$usuario_nome = $_SESSION['usuario_nome'] ?? null;
$usuario_tipo = $_SESSION['usuario_tipo'] ?? null;

// Detecta se está dentro de /admin/
$prefix = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '';
?>
<!-- LINKS NECESSÁRIOS -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
<link rel="stylesheet" href="<?php echo $prefix; ?>css/style.css">

<header class="header">
    <a href="<?php echo $prefix; ?>index.php" class="logo">
        <img src="<?php echo $prefix; ?>img/aconiitologo-Photoroom.png" alt="Logo O Acônito">
        <h1>O Acônito</h1>
    </a>

    <nav class="menu">
        <?php if ($logado): ?>
            <?php if ($usuario_tipo === 'admin'): ?>
                <a href="<?php echo $prefix; ?>admin/painel.php">
                    <span class="material-symbols-outlined">dashboard</span> Painel
                </a>
            <?php else: ?>
                <a href="<?php echo $prefix; ?>perfil.php">
                    <span class="material-symbols-outlined">person</span> Perfil
                </a>
            <?php endif; ?>

            <?php if ($usuario_tipo === 'admin'): ?>
                <a href="<?php echo $prefix; ?>addBebidas.php">
                    <span class="material-symbols-outlined">inventory_2</span> Adicionar
                </a>
            <?php else: ?>
                <a href="<?php echo $prefix; ?>carrinho.php">
                    <span class="material-symbols-outlined">shopping_cart</span> Carrinho
                </a>
            <?php endif; ?>

            <a href="<?php echo $prefix; ?>logout.php">
                <span class="material-symbols-outlined">logout</span> Sair
            </a>
        <?php else: ?>
            <a href="<?php echo $prefix; ?>login.php">
                <span class="material-symbols-outlined">login</span> Login
            </a>
        <?php endif; ?>
    </nav>
</header>
