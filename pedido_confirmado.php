<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/templates/header.php';
requireLogin();

$pedidoID = $_GET['id'] ?? null;
if (!$pedidoID) {
    header('Location: /');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Pedidos WHERE pedidoID = ? AND usuarioID = ?");
$stmt->execute([$pedidoID, $_SESSION['usuarioID']]);
$pedido = $stmt->fetch();

if (!$pedido) {
    echo "<p>Pedido não encontrado.</p>";
    require_once __DIR__ . '/templates/footer.php';
    exit();
}
?>

<div class="max-w-2xl mx-auto text-center">
    <div class="bg-white p-8 rounded-lg shadow-md">
        <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <h1 class="text-3xl font-bold text-gray-800 mt-4">Pedido Realizado com Sucesso!</h1>
        <p class="text-gray-600 mt-2">Obrigado pela sua compra, <?= htmlspecialchars($_SESSION['nome']) ?>!</p>
        
        <div class="mt-6 text-left bg-gray-50 p-4 rounded-lg border">
            <p class="text-lg"><strong>Número do Pedido:</strong> #<?= htmlspecialchars($pedido['pedidoID']) ?></p>
            <p class="mt-2"><strong>Data:</strong> <?= date('d/m/Y', strtotime($pedido['dataPedido'])) ?></p>
            <p><strong>Valor Total:</strong> R$ <?= number_format($pedido['valorTotal'], 2, ',', '.') ?></p>
            <p><strong>Status:</strong> <span class="font-semibold text-blue-600"><?= htmlspecialchars($pedido['statusPedido']) ?></span></p>
        </div>

        <p class="mt-6 text-gray-500 text-sm">
            Você pode acompanhar o status do seu pedido na seção "Meus Pedidos" em seu perfil.
        </p>

        <a href="/meus_pedidos.php" class="mt-8 inline-block bg-pink-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-pink-600 transition-all duration-300">Ver Meus Pedidos</a>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
