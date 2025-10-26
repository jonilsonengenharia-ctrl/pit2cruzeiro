<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

// Busca os dados atuais do usuário
$stmtUsuario = $pdo->prepare("SELECT nome, email, telefone FROM Usuarios WHERE usuarioID = ?");
$stmtUsuario->execute([$_SESSION['usuarioID']]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    // Se não encontrar o usuário (improvável, mas seguro verificar)
    header('Location: /logout.php');
    exit;
}

require_once __DIR__ . '/templates/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar Perfil</h1>

    <form action="/handle_editar_perfil.php" method="POST">
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <!-- Email não pode ser alterado -->
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:ring-pink-500 focus:border-pink-500" 
                   disabled>
            <p class="text-xs text-gray-500 mt-1">O email não pode ser alterado.</p>
        </div>

        <div class="mb-4">
            <label for="nome" class="block text-sm font-medium text-gray-700">Nome Completo</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500" 
                   required>
        </div>

        <div class="mb-6">
            <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
            <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" 
                   placeholder="(XX) XXXXX-XXXX"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500">
        </div>

        <div>
            <button type="submit" 
                    class="w-full bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">
                Salvar Alterações
            </button>
            <a href="/perfil.php" class="block text-center mt-4 text-sm text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
