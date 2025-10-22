<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
requireAdmin();

// Lógica para buscar dados para o dashboard
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Pedidos WHERE statusPedido = 'Recebido'");
$stmt->execute();
$novosPedidos = $stmt->fetchColumn();

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Painel Administrativo</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card de Novos Pedidos -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700">Novos Pedidos</h2>
            <p class="text-4xl font-bold text-pink-600 mt-2"><?= $novosPedidos ?></p>
            <a href="/admin/admin_pedidos.php" class="text-blue-500 hover:underline mt-4 inline-block">Ver todos os pedidos &rarr;</a>
        </div>

        <!-- Outros cards podem ser adicionados aqui -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700">Gerenciamento</h2>
             <ul class="mt-4 space-y-2">
                <li><a href="/admin/admin_pedidos.php" class="text-blue-500 hover:underline">Gerenciar Pedidos</a></li>
            </ul>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
