<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

$erros = [];
$logradouro = $numero = $complemento = $bairro = $cidade = $estado = $cep = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização e validação dos dados
    $logradouro = trim($_POST['logradouro'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = trim($_POST['cep'] ?? '');

    if (empty($logradouro) || empty($numero) || empty($bairro) || empty($cidade) || empty($estado) || empty($cep)) {
        $erros[] = "Todos os campos, exceto complemento, são obrigatórios.";
    }

    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Enderecos (usuarioID, logradouro, numero, complemento, bairro, cidade, estado, cep, isPadrao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$_SESSION['usuarioID'], $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $cep]);
            
            // Redireciona de volta para a página de checkout
            header('Location: /checkout.php');
            exit();
        } catch (PDOException $e) {
            $erros[] = "Erro ao salvar o endereço. Tente novamente.";
            // error_log($e->getMessage()); // Logar o erro
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Adicionar Novo Endereço</h1>

    <?php if (!empty($erros)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label for="cep" class="block text-gray-700">CEP</label>
            <input type="text" id="cep" name="cep" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="mb-4">
            <label for="logradouro" class="block text-gray-700">Logradouro (Rua, Av.)</label>
            <input type="text" id="logradouro" name="logradouro" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="numero" class="block text-gray-700">Número</label>
                <input type="text" id="numero" name="numero" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label for="complemento" class="block text-gray-700">Complemento</label>
                <input type="text" id="complemento" name="complemento" class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>
        <div class="mb-4">
            <label for="bairro" class="block text-gray-700">Bairro</label>
            <input type="text" id="bairro" name="bairro" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="cidade" class="block text-gray-700">Cidade</label>
                <input type="text" id="cidade" name="cidade" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label for="estado" class="block text-gray-700">Estado</label>
                <input type="text" id="estado" name="estado" required class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="/checkout.php" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 mr-3">Cancelar</a>
            <button type="submit" class="bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600">Salvar Endereço</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
