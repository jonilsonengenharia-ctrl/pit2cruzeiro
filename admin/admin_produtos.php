<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

// SUGESTÃO 3: Proteção da página de Admin
$niveisPermitidos = ['admin', 'gerente'];
if (!in_array(get_user_role(), $niveisPermitidos)) {
    // Se não for admin ou gerente, redireciona para o perfil
    $_SESSION['error_message'] = "Você não tem permissão para acessar esta página.";
    header('Location: /perfil.php');
    exit;
}

$categorias_stmt = $pdo->query("SELECT * FROM Categorias ORDER BY nome");
$categorias = $categorias_stmt->fetchAll(PDO::FETCH_ASSOC);

$mensagem = '';
$tipo_mensagem = '';

// Lógica para Adicionar Produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_produto') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $preco = $_POST['preco'] ?? 0;
    $estoque = $_POST['estoque'] ?? 0;
    $categoriaID = $_POST['categoriaID'] ?? null;
    $urlImagem = $_POST['urlImagem'] ?? '';
    $ingredientes = $_POST['ingredientes'] ?? '';

    if (!empty($nome) && $preco > 0 && !empty($urlImagem)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO Cupcakes (nome, descricao, preco, urlImagem, ingredientes, estoque, isAtivo, categoriaID) 
                 VALUES (?, ?, ?, ?, ?, ?, 1, ?)"
            );
            $stmt->execute([$nome, $descricao, $preco, $urlImagem, $ingredientes, $estoque, $categoriaID]);
            $tipo_mensagem = 'sucesso';
            $mensagem = "Produto '".htmlspecialchars($nome)."' cadastrado com sucesso!";
        } catch (Exception $e) {
            $tipo_mensagem = 'erro';
            $mensagem = "Erro ao cadastrar produto: " . $e->getMessage();
        }
    } else {
        $tipo_mensagem = 'erro';
        $mensagem = "Nome, Preço e URL da Imagem são obrigatórios.";
    }
}

// LÓGICA DE REMOÇÃO: Implementada
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $produtoID = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Cupcakes WHERE cupcakeID = ?");
        $stmt->execute([$produtoID]);
        $tipo_mensagem = 'sucesso';
        $mensagem = "Produto removido com sucesso!";
    } catch (Exception $e) {
        $tipo_mensagem = 'erro';
        // Captura erro de chave estrangeira (produto em um pedido)
        if ($e->getCode() == '23000') {
             $mensagem = "Não é possível remover este produto, pois ele já está associado a pedidos existentes. Considere desativá-lo (Editando).";
        } else {
             $mensagem = "Erro ao remover produto: " . $e->getMessage();
        }
    }
}


// Busca todos os produtos para listar
$produtos_stmt = $pdo->query("SELECT C.*, Cat.nome as categoriaNome FROM Cupcakes C LEFT JOIN Categorias Cat ON C.categoriaID = Cat.categoriaID ORDER BY C.nome");
$produtos = $produtos_stmt->fetchAll(PDO::FETCH_ASSOC);

// Inclui o header
require_once __DIR__ . '/../templates/header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestão de Produtos (Cupcakes)</h1>

    <div class="flex space-x-4 mb-6">
         <a href="/admin/admin_produtos.php" class="bg-pink-600 text-white font-semibold py-2 px-4 rounded-lg shadow">Gerir Produtos</a>
         <a href="/admin/admin_pedidos.php" class="bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow hover:bg-gray-50">Gerir Pedidos</a>
    </div>

     <!-- Exibe Mensagens de Feedback -->
    <?php if ($mensagem): ?>
        <div class="<?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' ?> border px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $mensagem ?></span>
        </div>
    <?php endif; ?>

    <!-- Formulário de Cadastro -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Cadastrar Novo Produto</h2>
        <form action="/admin/admin_produtos.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <input type="hidden" name="action" value="add_produto">
            
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700">Nome do Produto</label>
                <input type="text" name="nome" id="nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
             <div>
                <label for="preco" class="block text-sm font-medium text-gray-700">Preço (ex: 8.50)</label>
                <input type="number" step="0.01" name="preco" id="preco" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
             <div>
                <label for="estoque" class="block text-sm font-medium text-gray-700">Estoque Inicial</label>
                <input type="number" step="1" name="estoque" id="estoque" required value="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
             <div>
                <label for="categoriaID" class="block text-sm font-medium text-gray-700">Categoria</label>
                <select name="categoriaID" id="categoriaID" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Nenhuma</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['categoriaID'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="urlImagem" class="block text-sm font-medium text-gray-700">URL da Imagem (ex: /imagens/meu-cupcake.png)</label>
                <input type="text" name="urlImagem" id="urlImagem" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="md:col-span-2 lg:col-span-3">
                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição Curta</label>
                <input type="text" name="descricao" id="descricao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
             <div class="md:col-span-2 lg:col-span-3">
                <label for="ingredientes" class="block text-sm font-medium text-gray-700">Ingredientes</label>
                <textarea name="ingredientes" id="ingredientes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>
            <div class="lg:col-span-3">
                 <button type="submit" class="bg-pink-500 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">
                    Cadastrar Produto
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Produtos Existentes -->
    <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
         <h2 class="text-2xl font-semibold text-gray-700 mb-4">Produtos Cadastrados</h2>
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($produtos as $prod): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full object-cover" src="<?= htmlspecialchars($prod['urlImagem']) ?>" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($prod['nome']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm <?= $prod['estoque'] < 10 ? 'text-red-600 font-bold' : 'text-gray-600' ?>">
                        <?= $prod['estoque'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($prod['isAtivo']): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($prod['categoriaNome'] ?: 'N/A') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <!-- Link de Edição -->
                        <a href="/admin/admin_editar_produto.php?id=<?= $prod['cupcakeID'] ?>" class="text-pink-600 hover:text-pink-900 mr-3">Editar</a>
                        <!-- Link de Remoção -->
                        <a href="/admin/admin_produtos.php?action=delete&id=<?= $prod['cupcakeID'] ?>" 
                           class="text-red-600 hover:text-red-900" 
                           onclick="return confirm('Tem certeza que deseja remover este produto? Esta ação não pode ser desfeita.');">
                           Remover
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
         </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

