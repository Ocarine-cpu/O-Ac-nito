<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include 'includes/db_connect.php';

// usuário logado?
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}
$usuario_id = intval($_SESSION['usuario_id']);

try {
    // Inicia transação
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    // Buscar itens do carrinho do usuário
    $stmt = $conn->prepare("
        SELECT c.id AS cart_id, c.quantidade, b.id AS bebida_id, b.nome, b.preco, b.estoque
        FROM carrinho c
        JOIN bebidas b ON c.bebida_id = b.id
        WHERE c.usuario_id = ?
        FOR UPDATE
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res || $res->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Carrinho vazio.']);
        exit;
    }

    $itens = [];
    $total = 0.0;
    while ($r = $res->fetch_assoc()) {
        $itens[] = $r;
        $total += floatval($r['preco']) * intval($r['quantidade']);
    }

    // inserir compra na tabela `compras`
    $insCompra = $conn->prepare("INSERT INTO compras (usuario_id, total, data_compra) VALUES (?, ?, NOW())");
    $insCompra->bind_param("id", $usuario_id, $total);
    if (!$insCompra->execute()) throw new Exception("Erro ao criar compra.");
    $compra_id = $conn->insert_id;

    // preparar statements para inserir itens e atualizar estoque
    $insItem = $conn->prepare("INSERT INTO itens_compra (compra_id, bebida_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    if (!$insItem) throw new Exception("Erro prepare itens_compra: " . $conn->error);

    $updEstoque = $conn->prepare("UPDATE bebidas SET estoque = estoque - ? WHERE id = ?");
    if (!$updEstoque) throw new Exception("Erro prepare update estoque: " . $conn->error);

    foreach ($itens as $it) {
        $pid = intval($it['bebida_id']);
        $qtd = intval($it['quantidade']);
        $preco = floatval($it['preco']);

        // verificar estoque atual com FOR UPDATE (garantia)
        $stmtCheck = $conn->prepare("SELECT estoque FROM bebidas WHERE id = ? FOR UPDATE");
        $stmtCheck->bind_param("i", $pid);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        $rowCheck = $resCheck->fetch_assoc();
        $stmtCheck->close();

        if (!$rowCheck || intval($rowCheck['estoque']) < $qtd) {
            throw new Exception("Produto '{$it['nome']}' sem estoque suficiente.");
        }

        // inserir item
        $insItem->bind_param("iiid", $compra_id, $pid, $qtd, $preco);
        if (!$insItem->execute()) {
            throw new Exception("Erro ao inserir item no pedido.");
        }

        // atualizar estoque
        $updEstoque->bind_param("ii", $qtd, $pid);
        if (!$updEstoque->execute()) {
            throw new Exception("Erro ao atualizar estoque.");
        }
    }

    // limpar carrinho do usuário
    $delCart = $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
    $delCart->bind_param("i", $usuario_id);
    if (!$delCart->execute()) {
        throw new Exception("Erro ao limpar carrinho.");
    }

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Compra confirmada com sucesso.']);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    exit;
}
