<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['usuario_id'];

// Atualiza quantidades (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
    if (isset($_POST['quantidade']) && is_array($_POST['quantidade'])) {
        foreach ($_POST['quantidade'] as $idItem => $qtd) {
            $qtd = intval($qtd);
            $idItem = intval($idItem);

            if ($qtd <= 0) {
                $del = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
                $del->bind_param("ii", $idItem, $usuario_id);
                $del->execute();
                $del->close();
            } else {
                $up = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?");
                $up->bind_param("iii", $qtd, $idItem, $usuario_id);
                $up->execute();
                $up->close();
            }
        }
    }
    header("Location: carrinho.php");
    exit;
}

// Buscar itens do carrinho
$sql = "
    SELECT c.id AS cart_id, c.quantidade, b.id AS bebida_id, b.nome, b.descricao, b.preco, b.estoque, b.imagem
    FROM carrinho c
    JOIN bebidas b ON c.bebida_id = b.id
    WHERE c.usuario_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Carrinho - O Acônito</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/carrinho.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container">
    <h2 class="titulo">Seu Carrinho</h2>

    <?php if ($result->num_rows === 0): ?>
        <p class="vazio">Seu carrinho está vazio.</p>
    <?php else: ?>
        <form method="POST" id="form-atualizar">
            <input type="hidden" name="acao" value="atualizar">
            <div class="cart-grid">
                <div class="cart-items">
                    <?php
                    $total = 0.0;
                    while ($row = $result->fetch_assoc()):
                        $subtotal = floatval($row['preco']) * intval($row['quantidade']);
                        $total += $subtotal;
                        $img = htmlspecialchars($row['imagem'] ? $row['imagem'] : 'img/default.png');
                    ?>
                    <div class="cart-item">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($row['nome']) ?>">
                        <div class="item-info">
                            <h3><?= htmlspecialchars($row['nome']) ?></h3>
                            <p class="descricao"><?= htmlspecialchars($row['descricao']) ?></p>
                            <div class="meta-line">
                                <span class="preco">R$ <?= number_format($row['preco'], 2, ',', '.') ?></span>
                                <span class="subtotal">Subtotal: R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                            </div>

                            <div class="qty-row">
                                <label for="q_<?= $row['cart_id'] ?>">Qtd</label>
                                <input type="number" id="q_<?= $row['cart_id'] ?>" name="quantidade[<?= $row['cart_id'] ?>]" min="0" value="<?= intval($row['quantidade']) ?>">
                                <button type="button" class="btn remove-small" data-cart-id="<?= $row['cart_id'] ?>">Remover</button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <aside class="cart-summary">
                    <h3>Resumo do pedido</h3>
                    <p class="resumo-line"><span>Valor total</span><strong id="cart-total">R$ <?= number_format($total, 2, ',', '.') ?></strong></p>
                    <p class="obs">Ao confirmar, você permanecerá nesta página até o processamento terminar.</p>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                        <button type="button" id="btn-finalizar" class="btn btn-success">Finalizar compra</button>
                    </div>
                </aside>
            </div>
        </form>
    <?php endif; ?>
</main>

<!-- Modal de confirmação -->
<div id="confirm-modal" class="modal hidden" aria-hidden="true">
    <div class="modal-card">
        <h3>Confirmar compra</h3>
        <p>Tem certeza que deseja confirmar a compra dos itens no carrinho?</p>
        <div class="modal-actions">
            <button id="confirm-yes" class="btn btn-success">Confirmar</button>
            <button id="confirm-no" class="btn btn-ghost">Cancelar</button>
        </div>
    </div>
</div>

<!-- Card central de feedback (aparecerá após sucesso) -->
<div id="center-card" class="center-card hidden"></div>

<!-- Container de toasts (posicionado via CSS var de header height) -->
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

<script src="js/toast.js"></script>
<script src="js/cardConfirmacao.js"></script>
<script src="js/carrinho.js"></script>
<?php include 'includes/footer.php'; ?>

</body>
</html>
