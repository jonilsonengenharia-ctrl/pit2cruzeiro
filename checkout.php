<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/templates/header.php';

// Redireciona se o carrinho estiver vazio
if (empty($_SESSION['carrinho'])) {
    header('Location: /carrinho.php');
    exit();
}

// Exige login para acessar
requireLogin();

$usuarioID = $_SESSION['usuarioID'];
$erros = [];

// --- Lógica para buscar os endereços do usuário ---
$stmtEnderecos = $pdo->prepare("SELECT * FROM Enderecos WHERE usuarioID = ?");
$stmtEnderecos->execute([$usuarioID]);
$enderecos = $stmtEnderecos->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para processar o pedido ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enderecoID = $_POST['enderecoID'] ?? null;

    if (!$enderecoID) {
        $erros[] = "Por favor, selecione um endereço de entrega.";
    }

    // Recalcular totais no backend para segurança
    $carrinho = $_SESSION['carrinho'] ?? [];
    $subtotal = 0;
    if (!empty($carrinho)) {
        $produtoIDs = array_keys($carrinho);
        $placeholders = implode(',', array_fill(0, count($produtoIDs), '?'));
        $stmt = $pdo->prepare("SELECT cupcakeID, preco FROM Cupcakes WHERE cupcakeID IN ($placeholders)");
        $stmt->execute($produtoIDs);
        $produtos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($carrinho as $id => $quantidade) {
            if (isset($produtos[$id])) {
                $subtotal += $produtos[$id] * $quantidade;
            }
        }
    }
    $frete = 5.00; // Recalcula o frete
    $valorTotal = $subtotal + $frete;
    
    // Se não houver erros, finalize o pedido
    if (empty($erros)) {
        try {
            $pdo->beginTransaction();

            // 1. Inserir na tabela Pedidos
            $stmtPedido = $pdo->prepare("INSERT INTO Pedidos (usuarioID, enderecoID, dataPedido, statusPedido, valorSubtotal, valorFrete, valorTotal, formaPagamento) VALUES (?, ?, CURDATE(), 'Recebido', ?, ?, ?, 'Pagamento Simulado')");
            $stmtPedido->execute([$usuarioID, $enderecoID, $subtotal, $frete, $valorTotal]);
            $pedidoID = $pdo->lastInsertId();

            // 2. Inserir na tabela ItensPedido
            $stmtItens = $pdo->prepare("INSERT INTO ItensPedido (pedidoID, cupcakeID, quantidade, precoUnitario) VALUES (?, ?, ?, ?)");
            foreach($carrinho as $id => $quantidade) {
                if (isset($produtos[$id])) {
                    $stmtItens->execute([$pedidoID, $id, $quantidade, $produtos[$id]]);
                }
            }

            $pdo->commit();
            
            // 3. Limpar o carrinho
            unset($_SESSION['carrinho']);

            // 4. Redirecionar para a página de confirmação
            header("Location: /pedido_confirmado.php?id=$pedidoID");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $erros[] = "Não foi possível finalizar seu pedido. Tente novamente.";
            // error_log($e->getMessage());
        }
    }
}

require_once __DIR__ . '/templates/header.php'; // Recarrega o header pra não dar erro
?>

<form method="POST">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6">Finalizar Compra</h1>
        
        <?php if (!empty($erros)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    <?php foreach ($erros as $erro): ?><li><?= htmlspecialchars($erro) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Seção de Endereço -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">1. Endereço de Entrega</h2>
            <div class="space-y-3">
                <?php if (empty($enderecos)): ?>
                    <p class="text-gray-600">Você ainda não tem endereços cadastrados.</p>
                <?php else: ?>
                    <?php foreach($enderecos as $endereco): ?>
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="enderecoID" value="<?= $endereco['enderecoID'] ?>" class="h-4 w-4 text-pink-600 border-gray-300 focus:ring-pink-500">
                            <span class="ml-3 text-sm">
                                <strong class="block text-gray-800"><?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?></strong>
                                <?= htmlspecialchars($endereco['bairro']) ?>, <?= htmlspecialchars($endereco['cidade']) ?> - <?= htmlspecialchars($endereco['estado']) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <a href="/adicionar_endereco.php" class="mt-4 inline-block text-pink-500 font-semibold hover:underline">+ Adicionar novo endereço</a>
        </div>

        <!-- Seção de Pagamento -->
        <div>
            <h2 class="text-xl font-semibold mb-4">2. Pagamento</h2>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded-md" role="alert">
                <p>Esta é uma etapa de <strong>simulação</strong>. Nenhum dado real é necessário.</p>
            </div>
            <div class="space-y-4">
                <div>
                    <label for="card-number" class="block text-sm font-medium text-gray-700">Número do Cartão (simulado)</label>
                    <input type="text" id="card-number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="**** **** **** 1234" disabled>
                </div>
                 <div>
                    <label for="card-name" class="block text-sm font-medium text-gray-700">Nome no Cartão</label>
                    <input type="text" id="card-name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($_SESSION['nome']) ?>" disabled>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo do Pedido -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-md sticky top-24">
            <h2 class="text-xl font-semibold mb-4">Resumo do Pedido</h2>
             <!-- O resumo do pedido pode ser adicionado aqui, mas já está no carrinho -->
            <button type="submit" class="mt-6 w-full text-center block bg-pink-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300">Finalizar Pedido Simulado</button>
        </div>
    </div>
</div>
</form>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

