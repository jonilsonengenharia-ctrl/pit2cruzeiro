<?php
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/backend/db.php';

// Busca os produtos no banco de dados
$stmt = $pdo->query("SELECT * FROM Cupcakes WHERE isAtivo = TRUE ORDER BY nome ASC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Nossos Cupcakes</h1>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-8">
    <?php foreach ($produtos as $produto): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105">
            <a href="/produto.php?id=<?= $produto['cupcakeID'] ?>">
                <img src="<?= htmlspecialchars($produto['urlImagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="h-64 w-full object-cover">
            </a>
            <div class="p-4">
                <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($produto['nome']) ?></h3>
                <p class="text-gray-600 text-sm mt-1 h-10"><?= htmlspecialchars($produto['descricao']) ?></p>
                <div class="flex justify-between items-center mt-4">
                    <span class="font-bold text-xl text-pink-500">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                    <button data-id="<?= $produto['cupcakeID'] ?>" class="add-to-cart-btn bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">Adicionar</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
