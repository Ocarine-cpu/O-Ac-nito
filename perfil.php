<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';
include 'includes/toast.php';

$id = $_SESSION['usuario_id'];
$sql = "SELECT id, login, nome, data_cadastro FROM usuarios WHERE id = ?"; // sem e-mail
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();


$sql2 = "SELECT COUNT(*) AS total_bebidas, SUM(estoque) AS estoque_total FROM bebidas WHERE usuario_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$dados = $stmt2->get_result()->fetch_assoc();


$data_cadastro = new DateTime($usuario['data_cadastro'] ?? 'now'); 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Perfil - <?= htmlspecialchars($usuario['login']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include('includes/header.php'); ?>

<main class="container">
    <div class="perfil-card">
        <h2 class="perfil-titulo"><span class="material-symbols-outlined icon-person-h2">person</span> Perfil do Usuário</h2>

        <?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
            <p class="mensagem-sucesso" style="text-align:center; color:#8ef08e; margin-bottom:12px;"><?= htmlspecialchars($_SESSION['mensagem_sucesso']) ?></p>
            <?php unset($_SESSION['mensagem_sucesso']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['mensagem_erro'])): ?>
            <p class="erro" style="text-align:center; margin-bottom:12px;"><?= htmlspecialchars($_SESSION['mensagem_erro']) ?></p>
            <?php unset($_SESSION['mensagem_erro']); ?>
        <?php endif; ?>
        
        <div class="perfil-info-box">
            <div class="info-item">
                <p class="label">Nome:</p>
                <p class="valor"><?= htmlspecialchars($usuario['nome'] ?? '') ?></p>
            </div>

            <div class="info-item">
                <p class="label">Usuário (Login):</p>
                <p class="valor"><?= htmlspecialchars($usuario['login']) ?></p>
            </div>
            
            <div class="info-item">
                <p class="label">Membro desde:</p>
                <p class="valor"><?= $data_cadastro->format('d/m/Y') ?></p>
            </div>
            
        </div>
        
        <h3 class="estatisticas-titulo">Estatísticas de Inventário</h3>
        
        <div class="estatisticas-box">
            
            <div class="estatistica-item">
                <p class="label"><span class="material-symbols-outlined">liquor</span> Bebidas Cadastradas</p>
                <p class="valor-estatistica"><?= intval($dados['total_bebidas'] ?? 0) ?></p>
            </div>
            
            <div class="estatistica-item">
                <p class="label"><span class="material-symbols-outlined">inventory_2</span> Estoque Total</p>
                <p class="valor-estatistica"><?= intval($dados['estoque_total'] ?? 0) ?></p>
            </div>
            
        </div>
        
        <div class="acoes-perfil">
            <a href="editPerfil.php" class="btn-principal">Editar Dados</a>
            </div>

    </div>
</main>

<script src="js/main.js"></script>
<?php include 'includes/footer.php'; ?>

</body>
</html>

<?php $conn->close(); ?>
