<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Busca o usuário pelo email
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se o usuário existe e se a senha está correta
    if ($usuario && password_verify($senha, $usuario['senhaHash'])) {
        // Login bem-sucedido, armazena dados na sessão
        $_SESSION['usuarioID'] = $usuario['usuarioID'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['nivelAcesso'] = $usuario['nivelAcesso'];

        // Redireciona para a página que o usuário tentava acessar ou para o perfil
        if (isset($_SESSION['redirect_url'])) {
            $redirect_url = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']);
            header('Location: ' . $redirect_url);
        } else {
            header('Location: /perfil.php');
        }
        exit();
    } else {
        $erro = "E-mail ou senha inválidos.";
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-center mb-6">Login</h1>
    <?php if ($erro): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-4">
            <label for="email" class="block text-gray-700">E-mail</label>
            <input type="email" id="email" name="email" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        <div class="mb-6">
            <label for="senha" class="block text-gray-700">Senha</label>
            <input type="password" id="senha" name="senha" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        <button type="submit" class="w-full bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300">Entrar</button>
    </form>
    <p class="text-center mt-4">
        Não tem uma conta? <a href="/register.php" class="text-pink-500 hover:underline">Cadastre-se</a>
    </p>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

