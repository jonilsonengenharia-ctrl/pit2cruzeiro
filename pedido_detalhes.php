<?php
// Debug: Linhas para depuração: mostram erros detalhados em vez do genérico "Erro 500"
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

$pedidoID = $_GET['id'] ?? null;
if (!$pedidoID) {
    header("Location: /meus_pedidos.php");
    exit();
}

$stmtPedido = $pdo->prepare(
    "SELECT 
        p.pedidoID, p.dataPedido, p.valorSubtotal, p.valorFrete, p.valorTotal, p.statusPedido,
        e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep 
     FROM Pedidos p
     LEFT JOIN Enderecos e ON p.enderecoID = e.enderecoID
     WHERE p.pedidoID = ? AND p.usuarioID = ?"
);
$stmtPedido->execute([$pedidoID, $_SESSION['usuarioID']]);
$pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header("Location: /meus_pedidos.php");
    exit();
}

$stmtItens = $pdo->prepare(
    "SELECT ip.quantidade, ip.precoUnitario, pr.nome, pr.urlImagem 
     FROM ItensPedido ip
     JOIN Cupcakes pr ON ip.cupcakeID = pr.cupcakeID
     WHERE ip.pedidoID = ?"
);
$stmtItens->execute([$pedidoID]);
$itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/templates/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Detalhes do Pedido #<?= htmlspecialchars($pedido['pedidoID']) ?></h1>
        
        <p class="text-gray-500 mb-6">
            Realizado em: <?= !empty($pedido['dataPedido']) ? date("d/m/Y", strtotime($pedido['dataPedido'])) : 'Data não disponível' ?>
        </p>

        <div class="bg-white p-6 rounded-lg shadow-md space-y-6">
            
            <div>
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Itens do Pedido</h2>
                <div class="space-y-4">
                    <?php foreach ($itens as $item): ?>
                    <div class="flex items-center">
                        <img src="<?= htmlspecialchars($item['urlImagem'] ?? 'imagens/placeholder.png') ?>" alt="<?= htmlspecialchars($item['nome'] ?? 'Produto indisponível') ?>" class="w-16 h-16 rounded-md object-cover mr-4">
                        <div class="flex-grow">
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nome'] ?? 'Produto indisponível') ?></p>
                            <p class="text-sm text-gray-500">Qtd: <?= htmlspecialchars($item['quantidade'] ?? 0) ?> x R$ <?= number_format($item['precoUnitario'] ?? 0, 2, ',', '.') ?></p>
                        </div>
                        <p class="font-semibold text-gray-700">R$ <?= number_format(($item['quantidade'] ?? 0) * ($item['precoUnitario'] ?? 0), 2, ',', '.') ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="border-t pt-4">
                 <div class="space-y-2 text-right">
                    <p class="text-gray-600">Subtotal: <span class="font-semibold text-gray-800">R$ <?= number_format($pedido['valorSubtotal'] ?? 0, 2, ',', '.') ?></span></p>
                    <p class="text-gray-600">Frete: <span class="font-semibold text-gray-800">R$ <?= number_format($pedido['valorFrete'] ?? 0, 2, ',', '.') ?></span></p>
                    <p class="text-xl font-bold text-gray-900">Total: <span class="text-pink-600">R$ <?= number_format($pedido['valorTotal'] ?? 0, 2, ',', '.') ?></span></p>
                 </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Endereço de Entrega</h2>
                <?php if (!empty($pedido['logradouro'])): ?>
                    <div class="text-gray-700">
                        <p><?= htmlspecialchars($pedido['logradouro']) ?>, <?= htmlspecialchars($pedido['numero']) ?></p>
                        <p><?= htmlspecialchars($pedido['bairro']) ?>, <?= htmlspecialchars($pedido['cidade']) ?> - <?= htmlspecialchars($pedido['estado']) ?></p>
                        <p>CEP: <?= htmlspecialchars($pedido['cep']) ?></p>
                        <?php if (!empty($pedido['complemento'])): ?>
                            <p>Complemento: <?= htmlspecialchars($pedido['complemento']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 italic">Endereço não encontrado ou removido.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="/meus_pedidos.php" class="text-pink-600 hover:underline font-medium">&larr; Voltar para Meus Pedidos</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

