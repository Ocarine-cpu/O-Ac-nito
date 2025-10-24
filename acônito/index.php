<?php
session_start();
include 'includes/db_connect.php';

$sql = "SELECT b.*, u.login AS dono FROM bebidas b LEFT JOIN usuarios u ON b.usuario_id = u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>O Acônito</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container">
    <h2>Catálogo de Bebidas</h2>

    <?php
    if ($result->num_rows > 0) {
        echo '<div class="bebidas">';
          while ($row = $result->fetch_assoc()) {
            echo '<div class="card">';
            echo '<img src="' . htmlspecialchars($row['imagem']) . '" alt="' . htmlspecialchars($row['nome']) . '">';
            echo '<h3>' . htmlspecialchars($row['nome']) . '</h3>';
            echo '<p>' . nl2br(htmlspecialchars(substr($row['descricao'], 0, 120))) . '...</p>';
            echo '<span class="preco">R$ ' . number_format($row['preco'], 2, ',', '.') . '</span>';
            echo '<div class="actions">';
            echo '<a href="detalhes.php?id=' . $row['id'] . '" class="btn">Ver Detalhes</a>';

            // se logado e dono da bebida, mostra editar/excluir
            if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $row['usuario_id']) {
              echo ' <a href="editBebida.php?id=' . $row['id'] . '">✏️ Editar</a>';
              echo ' <form style="display:inline" method="POST" action="deleteBebida.php" onsubmit="return confirm(\'Confirma exclusão?\')">
                       <input type="hidden" name="id" value="' . $row['id'] . '">
                       <button type="submit">🗑️ Excluir</button>
                     </form>';
            }

            echo '</div>'; // actions
            echo '</div>';
        }
      echo '</div>';
    } else {
        echo '<p class="vazio">Nenhuma bebida cadastrada ainda.</p>';
    }
    $conn->close();
    ?>
  </main>

  

  <script src="js/main.js"></script>
</body>
</html>
