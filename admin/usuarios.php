<?php
session_start();
include '../includes/db_connect.php';

// proteção: apenas admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// busca com pesquisa
$q = trim($_GET['q'] ?? '');
$usuarios = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare("
        SELECT id, login, nome, tipo, data_cadastro 
        FROM usuarios 
        WHERE tipo <> 'admin' 
        AND (login LIKE ? OR nome LIKE ?) 
        ORDER BY id DESC 
        LIMIT 500
    ");
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("
        SELECT id, login, nome, tipo, data_cadastro 
        FROM usuarios 
        WHERE tipo <> 'admin' 
        ORDER BY id DESC 
        LIMIT 500
    ");
}

if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $usuarios[] = $r;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Consulta Usuário - O Acônito</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        .wrap { max-width:1100px; margin:32px auto; padding:0 14px; }
        .table-box { background:#131216; padding:12px; border-radius:10px; border:1px solid #2b1640; } /* ESTILO PADRÃO */
        table.full { width:100%; border-collapse:collapse; color:#f5dfc9; } /* ESTILO PADRÃO */
        table.full th, table.full td { padding:10px; border-bottom:1px dashed rgba(255,255,255,0.04); } /* ESTILO PADRÃO */
        table.full th { color:#ffd700; } /* ESTILO PADRÃO */
        
        .search-row { display:flex; gap:12px; margin-bottom:18px; }
        .search-row input {
            flex:1; padding:10px; border-radius:6px;
            border:1px solid #3d2660; background:#0f0f0f; color:#e5e5e5;
        }
        .search-row button {
            padding:10px 18px; border-radius:6px; border:none;
            background:#b8860b; color:white; cursor:pointer; font-weight:bold;
        }
        
        /* Estilos de Alinhamento para usuários.php */
        .td-id { text-align:center; width:1%; white-space:nowrap; } /* ID centralizado */
        .td-login { text-align:left; } /* Login alinhado à esquerda */
        .td-nome { text-align:left; } /* Nome alinhado à esquerda */
        .td-tipo { text-align:center; width:1%; white-space:nowrap; } /* Tipo centralizado */
        .td-data { text-align:right; width:1%; white-space:nowrap; } /* Data alinhada à direita */
        .td-ac { text-align:center; padding:5px 8px; width:1%; white-space:nowrap; } /* Ações centralizado */

        .btn-excluir {
            background:#8b0000; color:#fff; border:none; padding:6px 10px;
            border-radius:4px; cursor:pointer; font-weight:bold;
            display:inline-block; /* Garante que o botão use as regras de padding */
            text-decoration:none;
        }
        tr:hover { background: rgba(255,255,255,0.02); } /* Efeito hover mais sutil */
    </style>
</head>

<body>
<?php include '../includes/header.php'; ?>
<?php include '../includes/toast.php'; ?>

<main class="wrap">
    <h2 class="titulo">Consulta de Usuários</h2>

    <form class="search-row" method="GET">
        <input type="text" name="q" placeholder="Buscar por login ou nome..." value="<?= htmlspecialchars($q) ?>">
        <button type="submit">Buscar</button>
    </form>

    <div class="table-box"> 
        <table class="full"> 
            <thead>
                <tr>
                    <th class="td-id">ID</th>
                    <th class="td-login">Login</th>
                    <th class="td-nome">Nome</th>
                    <th class="td-tipo">Tipo</th>
                    <th class="td-data">Data de Cadastro</th>
                    <th class="td-ac">Ações</th> 
                </tr>
            </thead>

            <tbody>
            <?php if (empty($usuarios)): ?>
                <tr><td colspan="6" class="vazio">Nenhum usuário encontrado.</td></tr>

            <?php else: ?>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td class="td-id"><?= $u['id'] ?></td>
                    <td class="td-login"><?= htmlspecialchars($u['login']) ?></td>
                    <td class="td-nome"><?= htmlspecialchars($u['nome']) ?></td>
                    <td class="td-tipo"><?= htmlspecialchars($u['tipo']) ?></td>
                    <td class="td-data"><?= date('d/m/Y H:i', strtotime($u['data_cadastro'])) ?></td>

                    <td class="td-ac"> 
                        <form method="POST" action="excluir_usuario.php" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn-excluir">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div> 
</main>
<?php include '../includes/footer.php'; ?>

</body>
</html>