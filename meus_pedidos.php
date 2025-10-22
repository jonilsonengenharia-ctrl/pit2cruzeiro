<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

// Busca todos os pedidos do usuário logado, do mais recente para o mais antigo
$stmt = $pdo->prepare(
    "SELECT pedidoID, dataPedido, valorTotal, statusPedido 
     FROM Pedidos 
     WHERE usuarioID = ? 
     ORDER BY dataPedido DESC"
);
$stmt->execute([$_SESSION['usuarioID']]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/templates/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Meus Pedidos</h1>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="space-y-6">
                <?php if (empty($pedidos)): ?>
                    <p class="text-gray-500 italic text-center py-4">Você ainda não fez nenhum pedido.</p>
                <?php else: ?>
                    <?php foreach($pedidos as $pedido): ?>
                        <div class="border rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center hover:bg-gray-50 transition-colors">
                            <div class="mb-3 sm:mb-0">
                                <p class="font-bold text-lg text-gray-800">Pedido #<?= htmlspecialchars($pedido['pedidoID']) ?></p>
                                <p class="text-sm text-gray-500">Realizado em: <?= date("d/m/Y", strtotime($pedido['dataPedido'])) ?></p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <span class="font-semibold">Status:</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?= $pedido['statusPedido'] === 'Entregue' ? 'bg-green-100 text-green-800' : ($pedido['statusPedido'] === 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                        <?= htmlspecialchars($pedido['statusPedido']) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="flex items-center w-full sm:w-auto">
                                <p class="text-lg font-semibold text-gray-700 mr-6">R$ <?= number_format($pedido['valorTotal'], 2, ',', '.') ?></p>
                                <a href="/pedido_detalhes.php?id=<?= $pedido['pedidoID'] ?>" class="bg-pink-500 text-white text-sm font-bold py-2 px-4 rounded-lg hover:bg-pink-600 shadow transition-transform transform hover:scale-105">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-6 text-center">
            <a href="/perfil.php" class="text-pink-600 hover:underline font-medium">&larr; Voltar para o Perfil</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
