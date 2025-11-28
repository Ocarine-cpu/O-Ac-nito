<?php
include 'includes/toast.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

$id = $_SESSION['usuario_id'];
$mensagem = '';
$erro = '';


$sql = "SELECT id, login, email, nome FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {

    header("Location: perfil.php");
    exit();
}


if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}
if (isset($_SESSION['mensagem_erro'])) {
    $erro = $_SESSION['mensagem_erro'];
    unset($_SESSION['mensagem_erro']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Editar Perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include('includes/header.php'); ?>

<main class="container">
    <div class="edit-perfil-container">
        <h2>Editar Meus Dados</h2>

        <?php if ($mensagem): ?>
            <p class="mensagem-sucesso" style="color:#ffd700; text-align:center; margin-bottom:15px;"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>
        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST" action="updatePerfil.php">

            <input type="text" id="login" name="login" value="<?= htmlspecialchars($usuario['login']) ?>" required placeholder="Nome de usuário">

            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required placeholder="Seu melhor e-mail">
            
            <p style="color: #f5dfc9; font-size: 0.85rem; margin-top: 15px; margin-bottom: 5px; justify-content: center;">
                Digite a nova senha nos campos abaixo.
            </p>
            
            <div class="input-group">
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Nova Senha">
                <button type="button" class="toggle-senha" onclick="togglePassword('nova_senha', this)">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
            </div>

            <div class="input-group">
                <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Confirmar Nova Senha">
                <button type="button" class="toggle-senha" onclick="togglePassword('confirma_senha', this)">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
            </div>
            
            <button type="submit" class="btn-principal btn-full-width">Salvar Alterações</button>
            
            <a href="perfil.php" class="link-discreto">Voltar ao Perfil</a>
        </form>
    </div>
</main>

<script src="js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>