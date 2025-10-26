<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';

$erros = [];
$nome = '';
$email = '';
$telefone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    // --- Validações ---
    if (empty($nome)) {
        $erros[] = "O campo nome é obrigatório.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Por favor, insira um e-mail válido.";
    } else {
        // Verificar se o e-mail já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $erros[] = "Este e-mail já está cadastrado.";
        }
    }
    if (strlen($senha) < 6) {
        $erros[] = "A senha deve ter no mínimo 6 caracteres.";
    }
    if ($senha !== $confirmarSenha) {
        $erros[] = "As senhas não coincidem.";
    }

    // Se não houver erros, prossiga com o cadastro
    if (empty($erros)) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO Usuarios (nome, email, telefone, senhaHash, nivelAcesso) VALUES (?, ?, ?, ?, 'cliente')");
            $stmt->execute([$nome, $email, $telefone, $senhaHash]);
            
            // Login automático após o cadastro
            $novoUsuarioID = $pdo->lastInsertId();
            $_SESSION['usuarioID'] = $novoUsuarioID;
            $_SESSION['nome'] = $nome;
            $_SESSION['nivelAcesso'] = 'cliente';

            // Redireciona para a página que o usuário tentava acessar ou para o perfil
            if (isset($_SESSION['redirect_url'])) {
                $redirect_url = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                header('Location: ' . $redirect_url);
            } else {
                header('Location: /perfil.php');
            }
            exit();

        } catch (PDOException $e) {
            $erros[] = "Ocorreu um erro ao realizar o cadastro. Tente novamente.";
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-center mb-6">Criar Conta</h1>

    <?php if (!empty($erros)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Oops!</strong>
            <ul class="mt-2 list-disc list-inside">
                <?php foreach ($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label for="nome" class="block text-gray-700">Nome Completo</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700">E-mail</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        <div class="mb-4">
            <label for="telefone" class="block text-gray-700">Telefone (Opcional)</label>
            <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($telefone) ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        <div class="mb-4">
            <label for="senha" class="block text-gray-700">Senha</label>
            <input type="password" id="senha" name="senha" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        <div class="mb-6">
            <label for="confirmar_senha" class="block text-gray-700">Confirmar Senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500">
        </div>
        <button type="submit" class="w-full bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300">Cadastrar</button>
    </form>
    <p class="text-center mt-4">
        Já tem uma conta? <a href="/login.php" class="text-pink-500 hover:underline">Faça login</a>
    </p>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
