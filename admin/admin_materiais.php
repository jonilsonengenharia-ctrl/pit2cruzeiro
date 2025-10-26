<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

// SUGESTÃO 3: Proteção da página de Admin
$niveisPermitidos = ['admin', 'gerente'];
if (!in_array(get_user_role(), $niveisPermitidos)) {
    $_SESSION['error_message'] = "Você não tem permissão para acessar esta página.";
    header('Location: /perfil.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Lógica para Adicionar/Atualizar Estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Adicionar Novo Material ---
    if (isset($_POST['action']) && $_POST['action'] == 'add_material') {
        $nome = $_POST['nome'] ?? '';
        $unidade = $_POST['unidadeMedida'] ?? '';
        $estAtual = $_POST['estoqueAtual'] ?? 0;
        $estMinimo = $_POST['estoqueMinimo'] ?? 0;

        if (!empty($nome) && !empty($unidade)) {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO Materiais (nome, unidadeMedida, estoqueAtual, estoqueMinimo) 
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$nome, $unidade, $estAtual, $estMinimo]);
                $tipo_mensagem = 'sucesso';
                $mensagem = "Material '".htmlspecialchars($nome)."' cadastrado com sucesso!";
            } catch (Exception $e) {
                $tipo_mensagem = 'erro';
                $mensagem = "Erro ao cadastrar material: " . $e->getMessage();
            }
        } else {
            $tipo_mensagem = 'erro';
            $mensagem = "Nome e Unidade de Medida são obrigatórios.";
        }
    }

    // --- Atualizar Estoque Existente ---
    if (isset($_POST['action']) && $_POST['action'] == 'update_estoque') {
        $materialID = $_POST['materialID'] ?? 0;
        $adicionarEstoque = $_POST['adicionarEstoque'] ?? 0;

        if ($materialID > 0 && is_numeric($adicionarEstoque)) {
             try {
                $stmt = $pdo->prepare("UPDATE Materiais SET estoqueAtual = estoqueAtual + ? WHERE materialID = ?");
                $stmt->execute([$adicionarEstoque, $materialID]);
                $tipo_mensagem = 'sucesso';
                $mensagem = "Estoque atualizado com sucesso!";
            } catch (Exception $e) {
                $tipo_mensagem = 'erro';
                $mensagem = "Erro ao atualizar estoque: " . $e->getMessage();
            }
        }
    }
}

// Busca todos os materiais para listar
$materiais_stmt = $pdo->query("SELECT * FROM Materiais ORDER BY nome");
$materiais = $materiais_stmt->fetchAll(PDO::FETCH_ASSOC);

// Inclui o header
require_once __DIR__ . '/../templates/header.php';
?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestão de Estoque (Materiais)</h1>

    <div class="flex space-x-4 mb-6">
         <a href="/admin/admin_produtos.php" class="bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow hover:bg-gray-50">Gerir Produtos</a>
         <a href="/admin/admin_materiais.php" class="bg-pink-600 text-white font-semibold py-2 px-4 rounded-lg shadow">Gerir Materiais</a>
    </div>

     <!-- Exibe Mensagens de Feedback -->
    <?php if ($mensagem): ?>
        <div class="<?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' ?> border px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $mensagem ?></span>
        </div>
    <?php endif; ?>

    <!-- Formulário de Cadastro de Material -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Cadastrar Novo Material</h2>
        <form action="/admin/admin_materiais.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="add_material">
            
            <div class="md:col-span-2">
                <label for="nome" class="block text-sm font-medium text-gray-700">Nome do Material</label>
                <input type="text" name="nome" id="nome" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
             <div>
                <label for="unidadeMedida" class="block text-sm font-medium text-gray-700">Unidade (kg, lata, dúzia)</label>
                <input type="text" name="unidadeMedida" id="unidadeMedida" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
             <div>
                <label for="estoqueAtual" class="block text-sm font-medium text-gray-700">Estoque Inicial</label>
                <input type="number" step="0.01" name="estoqueAtual" id="estoqueAtual" value="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
             <div class="md:col-span-3">
                 <label for="estoqueMinimo" class="block text-sm font-medium text-gray-700">Estoque Mínimo (Alerta)</label>
                <input type="number" step="0.01" name="estoqueMinimo" id="estoqueMinimo" value="0" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="md:col-span-1 flex items-end">
                 <button type="submit" class="w-full bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">
                    Cadastrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Materiais Existentes -->
    <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
         <h2 class="text-2xl font-semibold text-gray-700 mb-4">Estoque de Materiais</h2>
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Atual</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque Mínimo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adicionar Estoque</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($materiais as $mat): ?>
                <?php $alerta_estoque = $mat['estoqueAtual'] <= $mat['estoqueMinimo']; ?>
                <tr class="<?= $alerta_estoque ? 'bg-red-50' : '' ?>">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($mat['nome']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm <?= $alerta_estoque ? 'text-red-600 font-bold' : 'text-gray-600' ?>">
                        <?= number_format($mat['estoqueAtual'], 2, ',', '.') ?> <?= htmlspecialchars($mat['unidadeMedida']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= number_format($mat['estoqueMinimo'], 2, ',', '.') ?> <?= htmlspecialchars($mat['unidadeMedida']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form action="/admin/admin_materiais.php" method="POST" class="flex items-center space-x-2">
                            <input type="hidden" name="action" value="update_estoque">
                            <input type="hidden" name="materialID" value="<?= $mat['materialID'] ?>">
                            <input type="number" step="0.01" name="adicionarEstoque" class="w-24 border-gray-300 rounded-md shadow-sm" placeholder="Qtd.">
                            <button type="submit" class="text-xs bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600">Adicionar</tton>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
         </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
