<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/templates/header.php';

$produtoID = $_GET['id'] ?? 0;

if (!$produtoID) {
    echo "Produto não encontrado.";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Cupcakes WHERE cupcakeID = ?");
$stmt->execute([$produtoID]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    echo "Produto não encontrado.";
    exit();
}
?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <img src="<?= htmlspecialchars($produto['urlImagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="w-full h-auto rounded-lg">
        </div>
        <div>
            <h1 class="text-4xl font-bold text-gray-800"><?= htmlspecialchars($produto['nome']) ?></h1>
            <p class="text-gray-600 mt-4 text-lg"><?= htmlspecialchars($produto['descricao']) ?></p>
            
            <div class="mt-6">
                <h2 class="text-xl font-semibold">Ingredientes</h2>
                <p class="text-gray-600 mt-2"><?= htmlspecialchars($produto['ingredientes']) ?></p>
            </div>

            <div class="mt-8">
                <a href="/" class="text-pink-500 hover:underline">&larr; Voltar para a loja</a>
            </div>

            <div class="flex justify-between items-center mt-4">
                <span class="font-bold text-3xl text-pink-500">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                <button data-id="<?= $produto['cupcakeID'] ?>" class="add-to-cart-btn bg-pink-500 text-white font-bold py-3 px-6 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">Adicionar ao Carrinho</button>
            </div>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/templates/footer.php'; ?>

