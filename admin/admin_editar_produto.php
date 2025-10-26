<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

// Proteção da página de Admin
$niveisPermitidos = ['admin', 'gerente'];
if (!in_array(get_user_role(), $niveisPermitidos)) {
    $_SESSION['error_message'] = "Você não tem permissão para acessar esta página.";
    header('Location: /perfil.php');
    exit;
}

$produtoID = $_GET['id'] ?? null;
if (!$produtoID || !filter_var($produtoID, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "ID de produto inválido.";
    header('Location: /admin/admin_produtos.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Lógica para Atualizar Produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_produto') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $preco = $_POST['preco'] ?? 0;
    $estoque = $_POST['estoque'] ?? 0;
    $categoriaID = $_POST['categoriaID'] ?? null;
    $urlImagem = $_POST['urlImagem'] ?? '';
    $ingredientes = $_POST['ingredientes'] ?? '';
    $isAtivo = isset($_POST['isAtivo']) ? 1 : 0; // Checkbox

    // Se categoriaID estiver vazio, define como NULL
    $categoriaID = empty($categoriaID) ? null : $categoriaID;

    if (!empty($nome) && $preco > 0 && !empty($urlImagem)) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE Cupcakes SET 
                    nome = ?, descricao = ?, preco = ?, urlImagem = ?, 
                    ingredientes = ?, estoque = ?, isAtivo = ?, categoriaID = ?
                 WHERE cupcakeID = ?"
            );
            $stmt->execute([$nome, $descricao, $preco, $urlImagem, $ingredientes, $estoque, $isAtivo, $categoriaID, $produtoID]);
            $tipo_mensagem = 'sucesso';
            $mensagem = "Produto '".htmlspecialchars($nome)."' atualizado com sucesso!";
        } catch (Exception $e) {
            $tipo_mensagem = 'erro';
            $mensagem = "Erro ao atualizar produto: " . $e->getMessage();
        }
    } else {
        $tipo_mensagem = 'erro';
        $mensagem = "Nome, Preço e URL da Imagem são obrigatórios.";
    }
}

// Busca os dados atuais do produto (após a tentativa de update, para pegar os dados frescos)
$stmt_prod = $pdo->prepare("SELECT * FROM Cupcakes WHERE cupcakeID = ?");
$stmt_prod->execute([$produtoID]);
$produto = $stmt_prod->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    // Se o produto não for encontrado
    $_SESSION['error_message'] = "Produto não encontrado.";
    header('Location: /admin/admin_produtos.php');
    exit;
}

// Busca categorias
$categorias_stmt = $pdo->query("SELECT * FROM Categorias ORDER BY nome");
$categorias = $categorias_stmt->fetchAll(PDO::FETCH_ASSOC);

// Inclui o header
require_once __DIR__ . '/../templates/header.php';
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Editar Produto</h1>

    <!-- Botão de Voltar -->
    <div class="mb-4">
        <a href="/admin/admin_produtos.php" class="text-pink-500 hover:underline">&larr; Voltar para a lista de produtos</a>
    </div>

     <!-- Exibe Mensagens de Feedback -->
    <?php if ($mensagem): ?>
        <div class="<?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' ?> border px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $mensagem ?></span>
        </div>
    <?php endif; ?>

    <!-- Formulário de Edição -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form action="/admin/admin_editar_produto.php?id=<?= $produtoID ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <input type="hidden" name="action" value="update_produto">
            
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700">Nome do Produto</label>
                <input type="text" name="nome" id="nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($produto['nome']) ?>">
            </div>
             <div>
                <label for="preco" class="block text-sm font-medium text-gray-700">Preço (ex: 8.50)</label>
                <input type="number" step="0.01" name="preco" id="preco" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($produto['preco']) ?>">
            </div>
             <div>
                <label for="estoque" class="block text-sm font-medium text-gray-700">Estoque</label>
                <input type="number" step="1" name="estoque" id="estoque" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($produto['estoque']) ?>">
            </div>
             <div>
                <label for="categoriaID" class="block text-sm font-medium text-gray-700">Categoria</label>
                <select name="categoriaID" id="categoriaID" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Nenhuma</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['categoriaID'] ?>" <?= ($produto['categoriaID'] == $cat['categoriaID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="lg:col-span-2">
                <label for="urlImagem" class="block text-sm font-medium text-gray-700">URL da Imagem</label>
                <input type="text" name="urlImagem" id="urlImagem" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($produto['urlImagem']) ?>">
            </div>
            <div class="md:col-span-2 lg:col-span-3">
                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição Curta</label>
                <input type="text" name="descricao" id="descricao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($produto['descricao']) ?>">
            </div>
             <div class="md:col-span-2 lg:col-span-3">
                <label for="ingredientes" class="block text-sm font-medium text-gray-700">Ingredientes</label>
                <textarea name="ingredientes" id="ingredientes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"><?= htmlspecialchars($produto['ingredientes']) ?></textarea>
D
            </div>
            <div class="lg:col-span-3">
                 <label for="isAtivo" class="flex items-center">
                    <input type="checkbox" name="isAtivo" id="isAtivo" value="1" class="h-4 w-4 text-pink-600 border-gray-300 rounded" <?= $produto['isAtivo'] ? 'checked' : '' ?>>
                    <span class="ml-2 text-sm text-gray-700">Produto Ativo (visível na loja)</span>
                </label>
            </div>
            <div class="lg:col-span-3">
                 <button type="submit" class="bg-pink-500 text-white font-bold py-2 px-6 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
