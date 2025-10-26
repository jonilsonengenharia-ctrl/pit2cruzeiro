<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
requireAdmin();

$statusFiltro = $_GET['status'] ?? '';
$query = "SELECT p.pedidoID, u.nome AS clienteNome, p.dataPedido, p.valorTotal, p.statusPedido 
          FROM Pedidos p 
          JOIN Usuarios u ON p.usuarioID = u.usuarioID";

if (!empty($statusFiltro)) {
    $query .= " WHERE p.statusPedido = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$statusFiltro]);
} else {
    $stmt = $pdo->query($query);
}
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Gerenciamento de Pedidos</h1>

    <!-- Filtro de Status -->
    <div class="mb-4">
        <form action="" method="get" class="flex items-center space-x-2">
            <select name="status" class="p-2 border rounded-md">
                <option value="">Todos os Status</option>
                <option value="Recebido" <?= $statusFiltro == 'Recebido' ? 'selected' : '' ?>>Recebido</option>
                <option value="Em Preparo" <?= $statusFiltro == 'Em Preparo' ? 'selected' : '' ?>>Em Preparo</option>
                <option value="Saiu para Entrega" <?= $statusFiltro == 'Saiu para Entrega' ? 'selected' : '' ?>>Saiu para Entrega</option>
                <option value="Entregue" <?= $statusFiltro == 'Entregue' ? 'selected' : '' ?>>Entregue</option>
                <option value="Cancelado" <?= $statusFiltro == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
            </select>
            <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded-md hover:bg-pink-700">Filtrar</button>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="py-2">Pedido ID</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2"><?= htmlspecialchars($pedido['pedidoID']) ?></td>
                    <td><?= htmlspecialchars($pedido['clienteNome']) ?></td>
                    <td><?= date("d/m/Y", strtotime($pedido['dataPedido'])) ?></td>
                    <td>R$ <?= number_format($pedido['valorTotal'], 2, ',', '.') ?></td>
                    <td><span class="px-2 py-1 text-sm rounded-full bg-gray-200"><?= htmlspecialchars($pedido['statusPedido']) ?></span></td>
                    <td>
                        <a href="/admin/admin_pedido_detalhes.php?id=<?= $pedido['pedidoID'] ?>" class="text-blue-500 hover:underline">Ver Detalhes</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>

