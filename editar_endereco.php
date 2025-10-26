<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

$enderecoID = $_GET['id'] ?? null;
if (!$enderecoID) {
    header('Location: /perfil.php');
    exit();
}

// Busca o endereço e verifica se pertence ao usuário logado
$stmt = $pdo->prepare("SELECT * FROM Enderecos WHERE enderecoID = ? AND usuarioID = ?");
$stmt->execute([$enderecoID, $_SESSION['usuarioID']]);
$endereco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$endereco) {
    // Endereço não encontrado ou não pertence ao usuário
    header('Location: /perfil.php');
    exit();
}

$erros = [];
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
            $stmtUpdate = $pdo->prepare(
                "UPDATE Enderecos SET logradouro = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, cep = ? WHERE enderecoID = ? AND usuarioID = ?"
            );
            $stmtUpdate->execute([$logradouro, $numero, $complemento, $bairro, $cidade, $estado, $cep, $enderecoID, $_SESSION['usuarioID']]);
            
            header('Location: /perfil.php');
            exit();
        } catch (PDOException $e) {
            $erros[] = "Erro ao atualizar o endereço. Tente novamente.";
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Editar Endereço</h1>

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
            <input type="text" id="cep" name="cep" value="<?= htmlspecialchars($endereco['cep']) ?>" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="mb-4">
            <label for="logradouro" class="block text-gray-700">Logradouro (Rua, Av.)</label>
            <input type="text" id="logradouro" name="logradouro" value="<?= htmlspecialchars($endereco['logradouro']) ?>" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="numero" class="block text-gray-700">Número</label>
                <input type="text" id="numero" name="numero" value="<?= htmlspecialchars($endereco['numero']) ?>" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label for="complemento" class="block text-gray-700">Complemento</label>
                <input type="text" id="complemento" name="complemento" value="<?= htmlspecialchars($endereco['complemento']) ?>" class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>
        <div class="mb-4">
            <label for="bairro" class="block text-gray-700">Bairro</label>
            <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($endereco['bairro']) ?>" required class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="cidade" class="block text-gray-700">Cidade</label>
                <input type="text" id="cidade" name="cidade" value="<?= htmlspecialchars($endereco['cidade']) ?>" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label for="estado" class="block text-gray-700">Estado</label>
                <input type="text" id="estado" name="estado" value="<?= htmlspecialchars($endereco['estado']) ?>" required class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="/perfil.php" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 mr-3">Cancelar</a>
            <button type="submit" class="bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600">Salvar Alterações</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

