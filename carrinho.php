<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/templates/header.php'; // Inicia a sessão

// Lógica para atualizar/remover itens do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $produtoID = $_POST['produtoID'] ?? 0;

        if ($_POST['action'] === 'update' && isset($_POST['quantidade'])) {
            $quantidade = (int)$_POST['quantidade'];
            if ($quantidade > 0) {
                $_SESSION['carrinho'][$produtoID] = $quantidade;
            } else {
                unset($_SESSION['carrinho'][$produtoID]); // Remove se a quantidade for 0 ou menor
            }
        } elseif ($_POST['action'] === 'remove') {
            unset($_SESSION['carrinho'][$produtoID]);
        }
    }
    // Redireciona para evitar reenvio do formulário
    header('Location: /carrinho.php');
    exit;
}

$carrinho = $_SESSION['carrinho'] ?? [];
$itensDoCarrinho = [];
$subtotal = 0;

if (!empty($carrinho)) {
    $produtoIDs = array_keys($carrinho);
    $placeholders = implode(',', array_fill(0, count($produtoIDs), '?'));

    $stmt = $pdo->prepare("SELECT cupcakeID, nome, preco, urlImagem FROM Cupcakes WHERE cupcakeID IN ($placeholders)");
    $stmt->execute($produtoIDs);
    
    // --- CORREÇÃO INICIA AQUI ---
    // 1. Buscamos os produtos como um array associativo padrão.
    $produtos_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $produtos = [];
    // 2. Reorganizamos o array para que a chave seja o ID do produto, facilitando a busca.
    foreach ($produtos_from_db as $p) {
        $produtos[$p['cupcakeID']] = $p;
    }
    // --- FIM DA CORREÇÃO ---

    foreach ($carrinho as $id => $quantidade) {
        if(isset($produtos[$id])) {
            // 3. Acessamos o produto diretamente, sem o índice [0] que causava o problema.
            $produto = $produtos[$id]; 
            $totalItem = $produto['preco'] * $quantidade;
            $subtotal += $totalItem;
            $itensDoCarrinho[] = [
                'produto' => $produto,
                'quantidade' => $quantidade,
                'total' => $totalItem
            ];
        } else {
            // Se o produto não existe mais no banco de dados, remove do carrinho.
            unset($_SESSION['carrinho'][$id]);
        }
    }
}

$frete = 5.00; // Frete fixo
$totalGeral = $subtotal + $frete;
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Seu Carrinho</h1>

<?php if (empty($itensDoCarrinho)): ?>
    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        <p class="text-gray-600">Seu carrinho está vazio.</p>
        <a href="/" class="mt-4 inline-block bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300">Voltar para a Loja</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Itens no carrinho</h2>
            <div class="space-y-4">
                <?php foreach($itensDoCarrinho as $item): ?>
                <div class="flex items-center justify-between border-b pb-4">
                    <div class="flex items-center">
                        <img src="<?= htmlspecialchars($item['produto']['urlImagem']) ?>" alt="<?= htmlspecialchars($item['produto']['nome']) ?>" class="h-20 w-20 object-cover rounded-md">
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($item['produto']['nome']) ?></h3>
                            <p class="text-sm text-gray-600">R$ <?= number_format($item['produto']['preco'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <form method="POST" class="flex items-center">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="produtoID" value="<?= $item['produto']['cupcakeID'] ?>">
                            <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" class="w-16 text-center border rounded-md py-1">
                            <button type="submit" class="text-xs text-blue-500 hover:underline ml-2">Atualizar</button>
                        </form>
                        <form method="POST">
                           <input type="hidden" name="action" value="remove">
                           <input type="hidden" name="produtoID" value="<?= $item['produto']['cupcakeID'] ?>">
                           <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                           </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Resumo do Pedido</h2>
                <div class="space-y-2">
                    <div class="flex justify-between"><span>Subtotal</span><span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span></div>
                    <div class="flex justify-between"><span>Frete</span><span>R$ <?= number_format($frete, 2, ',', '.') ?></span></div>
                    <div class="flex justify-between text-xl font-bold pt-2 border-t mt-2"><span>Total</span><span>R$ <?= number_format($totalGeral, 2, ',', '.') ?></span></div>
                </div>
                <a href="/checkout.php" class="mt-6 w-full text-center block bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300">Finalizar Compra</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

