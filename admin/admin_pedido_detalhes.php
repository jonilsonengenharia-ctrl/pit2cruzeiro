<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
requireAdmin();

$pedidoID = $_GET['id'] ?? null;
if (!$pedidoID) {
    header("Location: /admin/admin_pedidos.php");
    exit();
}

// Lidar com a atualização de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_status'])) {
    $novoStatus = $_POST['novo_status'];
    $stmtUpdate = $pdo->prepare("UPDATE Pedidos SET statusPedido = ? WHERE pedidoID = ?");
    $stmtUpdate->execute([$novoStatus, $pedidoID]);
    // Redireciona para evitar reenvio do formulário
    header("Location: /admin/admin_pedido_detalhes.php?id=$pedidoID&status=success");
    exit();
}


$stmtPedido = $pdo->prepare(
    "SELECT p.*, u.nome as clienteNome, e.* FROM Pedidos p 
     JOIN Usuarios u ON p.usuarioID = u.usuarioID
     LEFT JOIN Enderecos e ON p.enderecoID = e.enderecoID
     WHERE p.pedidoID = ?"
);
$stmtPedido->execute([$pedidoID]);
$pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header("Location: /admin/admin_pedidos.php");
    exit();
}

$stmtItens = $pdo->prepare(
    "SELECT ip.quantidade, ip.precoUnitario, c.nome, c.urlImagem 
     FROM ItensPedido ip
     JOIN Cupcakes c ON ip.cupcakeID = c.cupcakeID
     WHERE ip.pedidoID = ?"
);
$stmtItens->execute([$pedidoID]);
$itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

$statusDisponiveis = ['Recebido', 'Em Preparo', 'Saiu para Entrega', 'Entregue', 'Cancelado'];

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Detalhes do Pedido #<?= htmlspecialchars($pedido['pedidoID']) ?></h1>
        <p class="text-gray-500 mb-6">Cliente: <?= htmlspecialchars($pedido['clienteNome']) ?></p>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">O status do pedido foi atualizado.</span>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Coluna de Itens e Total -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Itens do Pedido</h2>
                    <?php foreach ($itens as $item): ?>
                        <div class="flex items-center mb-2">
                            <img src="/<?= htmlspecialchars($item['urlImagem']) ?>" class="w-12 h-12 rounded-md mr-3">
                            <div>
                                <p class="font-semibold"><?= htmlspecialchars($item['nome']) ?></p>
                                <p class="text-sm text-gray-500">Qtd: <?= $item['quantidade'] ?> x R$ <?= number_format($item['precoUnitario'], 2, ',', '.') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                 <div class="border-t pt-4 text-right">
                    <p>Subtotal: R$ <?= number_format($pedido['valorSubtotal'], 2, ',', '.') ?></p>
                    <p>Frete: R$ <?= number_format($pedido['valorFrete'], 2, ',', '.') ?></p>
                    <p class="font-bold text-lg">Total: R$ <?= number_format($pedido['valorTotal'], 2, ',', '.') ?></p>
                 </div>
            </div>

            <!-- Coluna de Status e Endereço -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Atualizar Status</h2>
                    <p class="mb-2">Status Atual: <span class="font-bold text-pink-600"><?= htmlspecialchars($pedido['statusPedido']) ?></span></p>
                    <form action="/admin/admin_pedido_detalhes.php?id=<?= $pedidoID ?>" method="post">
                        <select name="novo_status" class="p-2 border rounded-md w-full">
                            <?php foreach ($statusDisponiveis as $status): ?>
                                <option value="<?= $status ?>" <?= $pedido['statusPedido'] == $status ? 'selected' : '' ?>><?= $status ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="mt-2 w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Atualizar Status</button>
                    </form>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Endereço de Entrega</h2>
                     <div class="text-gray-700">
                        <p><?= htmlspecialchars($pedido['logradouro']) ?>, <?= htmlspecialchars($pedido['numero']) ?></p>
                        <p><?= htmlspecialchars($pedido['bairro']) ?>, <?= htmlspecialchars($pedido['cidade']) ?> - <?= htmlspecialchars($pedido['estado']) ?></p>
                        <p>CEP: <?= htmlspecialchars($pedido['cep']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="/admin/admin_pedidos.php" class="text-pink-600 hover:underline font-medium">&larr; Voltar para a lista de pedidos</a>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
