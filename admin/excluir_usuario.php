<?php
// admin/usuarios_excluir.php
session_start();
include '../includes/db_connect.php';

// apenas admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "PERMITIDO APENAS PARA ADMIN";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método não permitido";
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo "ID inválido";
    exit;
}

// Evitar exclusão do próprio admin por engano (opcional)
if ($id === intval($_SESSION['usuario_id'])) {
    echo "Você não pode excluir sua própria conta.";
    exit;
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? LIMIT 1");
if (!$stmt) {
    echo "Erro no servidor";
    exit;
}
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    $stmt->close();
    // Redireciona de volta ao admins/usuarios
    header("Location: usuarios.php?msg=deleted");
    exit;
} else {
    echo "Erro ao excluir: " . $stmt->error;
    $stmt->close();
    exit;
}
