<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
  header("Location: index.php");
  exit();
}

$id = intval($_POST['id']);
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT usuario_id FROM bebidas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
  header("Location: index.php");
  exit();
}
$row = $res->fetch_assoc();
if ($row['usuario_id'] != $usuario_id) {
  header("Location: index.php?erro=nao_autorizado");
  exit();
}

$stmt2 = $conn->prepare("DELETE FROM bebidas WHERE id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();

header("Location: index.php?excluido=1");
exit();
