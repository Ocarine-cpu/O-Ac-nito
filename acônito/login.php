<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $login = trim($_POST['login']);
  $senha = $_POST['senha'];

  $sql = "SELECT * FROM usuarios WHERE login = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $login);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($senha, $user['senha'])) {
      $_SESSION['usuario_id'] = $user['id'];
      $_SESSION['usuario_nome'] = $user['login'];
      header("Location: index.php");
      exit;
    } else {
      $erro = "Senha incorreta!";
    }
  } else {
    $erro = "Usuário não encontrado!";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - O Acônito</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <div class="login-container">
  <h2 style="text-align:center; color:#ffd700;">Entrar no Acônito</h2>
  <?php if (!empty($erro)): ?><p class="erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

  <form method="POST">
    <input type="text" name="login" placeholder="Login" required>

    <div class="input-group">
      <input type="password" id="senha" name="senha" placeholder="Senha" required>
      <button type="button" class="toggle-senha" onclick="toggleSenha('senha', this)">
        <span class="material-symbols-outlined">visibility</span>
      </button>
    </div>

    <button type="submit" class="btn-principal">Entrar</button>

    <div class="login-links">
      <a href="#" class="link-esquerda">Esqueci minha senha</a>
      <a href="cadastro.php" class="link-direita">Cadastrar</a>
    </div>
  </form>
</div>


  <script src="js/main.js"></script>
</body>
</html>
