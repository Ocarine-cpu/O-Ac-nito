<?php
session_start();
include 'includes/db_connect.php';

// buscar bebidas (ordenar por id desc para ver os mais recentes no topo)
$sql = "SELECT * FROM bebidas ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>O Acônito - Catálogo de Bebidas</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Material+Symbols+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container">
    <h2 class="titulo">Catálogo de Bebidas</h2>

    <?php if ($result && $result->num_rows > 0): ?>
      <div class="grid">
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $isDono = isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $row['usuario_id'];
            $img = htmlspecialchars($row['imagem'] ?: 'img/default.png');
            $nome = htmlspecialchars($row['nome']);
            $preco = number_format($row['preco'], 2, ',', '.');
          ?>
          <div class="card">
            <div class="card-icons">
              <?php if ($isDono): ?>
                <!-- editar -->
                <a href="editBebida.php?id=<?= $row['id'] ?>" class="icon-btn" title="Editar" onclick="event.stopPropagation();">
                  <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                </a>

                <!-- excluir: POST via form -->
                <form method="POST" action="deleteBebida.php" onsubmit="return confirmDelete();" style="display:inline;" onClick="event.stopPropagation();">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" class="icon-btn" title="Excluir" style="border:none; background:transparent;">
                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                  </button>
                </form>
              <?php endif; ?>
            </div>

            <!-- card link cobre o conteúdo (imagem, nome, preço) -->
            <a href="detalhes.php?id=<?= $row['id'] ?>" class="card-link" onclick="">
              <img src="<?= $img ?>" alt="<?= $nome ?>" class="img-bebida">
              <h3><?= $nome ?></h3>
              <p class="preco">R$ <?= $preco ?></p>
            </a>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="vazio">Nenhuma bebida cadastrada ainda.</p>
    <?php endif; ?>
  </main>

  <script src="js/main.js"></script>
</body>
</html>

<?php $conn->close(); ?>
