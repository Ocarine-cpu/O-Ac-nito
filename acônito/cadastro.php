<?php
include 'includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nome = trim($_POST['nome'] ?? '');
  $login = trim($_POST['login'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $senha = $_POST['senha'] ?? '';
  $confirmar = $_POST['confirmar'] ?? '';

  if ($senha !== $confirmar) {
    $erro = "As senhas não coincidem!";
  } else {
    $sql = "SELECT id FROM usuarios WHERE login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
      $erro = "Este login já está em uso. Escolha outro.";
    } else {
      $hash = password_hash($senha, PASSWORD_DEFAULT);
      $sql2 = "INSERT INTO usuarios (login, email, senha, nome) VALUES (?, ?, ?, ?)";
      $stmt2 = $conn->prepare($sql2);
      $stmt2->bind_param("ssss", $login, $email, $hash, $nome);
      if ($stmt2->execute()) {
        header("Location: login.php?sucesso=1");
        exit;
      } else {
        $erro = "Erro ao cadastrar: " . $stmt2->error;
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cadastro - O Acônito</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <div class="cadastro-container">
  <h2 style="text-align:center; color:#ffd700;">Criar Conta</h2><br>
  <?php if (!empty($erro)): ?><p class="erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

  <form method="POST">
    <input type="text" name="login" placeholder="Login" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
    <input type="email" name="email" placeholder="E-mail" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    <input type="text" name="nome" placeholder="Nome completo" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">

    <div class="input-group">
      <input type="password" id="senha" name="senha" placeholder="Senha" required>
      <button type="button" class="toggle-senha" onclick="toggleSenha('senha', this)">
        <span class="material-symbols-outlined">visibility</span>
      </button>
    </div>

    <button type="submit" class="btn-principal">Cadastrar</button>

    <div class="login-links">
      <a href="login.php" class="link-direita">Já tenho conta</a>
    </div>
  </form>
</div>


  <script src="js/main.js"></script>
</body>
</html>
