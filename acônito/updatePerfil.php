<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

$id = $_SESSION['usuario_id'];
$nome = trim($_POST['nome'] ?? '');
$login = trim($_POST['login'] ?? '');
$email = trim($_POST['email'] ?? '');
$nova_senha = $_POST['nova_senha'] ?? '';
$confirma_senha = $_POST['confirma_senha'] ?? '';

// 1. Validação básica
if (empty($login) || empty($email)) {
    $_SESSION['mensagem_erro'] = "Login e e-mail são campos obrigatórios.";
    header("Location: editPerfil.php");
    exit();
}

if ($nova_senha !== $confirma_senha) {
    $_SESSION['mensagem_erro'] = "A nova senha e a confirmação de senha não coincidem.";
    header("Location: editPerfil.php");
    exit();
}

// 2. Verifica se o login ou email já existem (em outro usuário)
$sql_check = "SELECT id FROM usuarios WHERE (login = ? OR email = ?) AND id != ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ssi", $login, $email, $id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $_SESSION['mensagem_erro'] = "O login ou e-mail fornecido já está em uso por outra conta.";
    header("Location: editPerfil.php");
    exit();
}

// 3. Monta a query de atualização
$campos = [];
$tipos = '';
$valores = [];

$campos[] = "nome = ?";
$tipos .= 's';
$valores[] = $nome;

$campos[] = "login = ?";
$tipos .= 's';
$valores[] = $login;

$campos[] = "email = ?";
$tipos .= 's';
$valores[] = $email;

// Se a senha foi fornecida, hash e adicione à query
if (!empty($nova_senha)) {
    $hashed_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
    $campos[] = "senha = ?";
    $tipos .= 's';
    $valores[] = $hashed_senha;
}

// Adiciona o ID ao final para a cláusula WHERE
$valores[] = $id;
$tipos .= 'i';

$sql_update = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);

// Executa o bind_param dinamicamente
$stmt_update->bind_param($tipos, ...$valores);

if ($stmt_update->execute()) {
    // Atualiza a sessão, caso o login ou nome tenha mudado
    $_SESSION['usuario_login'] = $login;
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['mensagem_sucesso'] = "Seus dados foram atualizados com sucesso!";
    header("Location: perfil.php"); // Redireciona de volta ao perfil
} else {
    $_SESSION['mensagem_erro'] = "Erro ao atualizar os dados: " . $conn->error;
    header("Location: editPerfil.php");
}

$conn->close();
?>