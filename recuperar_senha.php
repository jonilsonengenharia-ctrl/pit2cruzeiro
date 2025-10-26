<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php'; // Para hash de senha
require_once __DIR__ . '/backend/email_sender.php'; // Para simular envio

$token = $_GET['token'] ?? null;
$mensagem = '';
$tipo_mensagem = ''; // 'sucesso' ou 'erro'
$mostrar_form_email = true;
$mostrar_form_reset = false;

$usuario_token = null;

if ($token) {
    // 2. Etapa: Validar o Token
    $mostrar_form_email = false;
    
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE resetToken = ? AND resetTokenExpiry > NOW()");
    $stmt->execute([$token]);
    $usuario_token = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario_token) {
        // Token válido, mostra o formulário de nova senha
        $mostrar_form_reset = true;
    } else {
        // Token inválido ou expirado
        $tipo_mensagem = 'erro';
        $mensagem = "Token de redefinição inválido ou expirado. Por favor, solicite um novo link.";
        $mostrar_form_email = true; // Mostra o form de email novamente
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // 1. Etapa: Solicitar redefinição
        $email = $_POST['email'];
        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Gera um token seguro
            $token_reset = bin2hex(random_bytes(32));
            // Define expiração (ex: 1 hora)
            $expiry = date('Y-m-d H:i:s', time() + 3600); 

            try {
                $stmt_update = $pdo->prepare("UPDATE Usuarios SET resetToken = ?, resetTokenExpiry = ? WHERE usuarioID = ?");
                $stmt_update->execute([$token_reset, $expiry, $usuario['usuarioID']]);
                
                // Simula o envio do e-mail
                $link_reset = "http://" . $_SERVER['HTTP_HOST'] . "/recuperar_senha.php?token=" . $token_reset;
                
                // (Opcional: chamar a função de envio real se configurada)
                // enviarEmailRecuperacao($usuario['email'], $usuario['nome'], $link_reset);

                $tipo_mensagem = 'sucesso';
                // Mensagem para o usuário (em um site real, apenas "Email enviado")
                $mensagem = "Se o email estiver cadastrado, um link de recuperação foi enviado. (Simulação: <a href='$link_reset' class='font-bold underline'>Clique aqui para redefinir</a>)";

            } catch (Exception $e) {
                $tipo_mensagem = 'erro';
                $mensagem = "Erro ao gerar link. Tente novamente.";
                 // error_log($e->getMessage());
            }
        } else {
             $tipo_mensagem = 'sucesso'; // Não informa ao usuário se o email existe ou não
             $mensagem = "Se o email estiver cadastrado, um link de recuperação foi enviado.";
        }
        $mostrar_form_email = true;
        
    } elseif (isset($_POST['token'], $_POST['senha'], $_POST['confirmar_senha']) && $usuario_token) {
        // 3. Etapa: Processar a nova senha
        $mostrar_form_reset = true;
        $senha = $_POST['senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        if (strlen($senha) < 6) {
            $tipo_mensagem = 'erro';
            $mensagem = "A senha deve ter no mínimo 6 caracteres.";
        } elseif ($senha !== $confirmar_senha) {
            $tipo_mensagem = 'erro';
            $mensagem = "As senhas não coincidem.";
        } else {
            // Tudo OK, atualiza a senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            try {
                // Atualiza a senha e limpa o token
                $stmt_final = $pdo->prepare("UPDATE Usuarios SET senhaHash = ?, resetToken = NULL, resetTokenExpiry = NULL WHERE usuarioID = ?");
                $stmt_final->execute([$senhaHash, $usuario_token['usuarioID']]);
                
                $tipo_mensagem = 'sucesso';
                $mensagem = "Senha redefinida com sucesso! Você já pode fazer login.";
                $mostrar_form_reset = false; // Esconde o form após sucesso

            } catch (Exception $e) {
                 $tipo_mensagem = 'erro';
                 $mensagem = "Erro ao atualizar a senha. Tente novamente.";
                 // error_log($e->getMessage());
            }
        }
    }
}

// Inclui o header
require_once __DIR__ . '/templates/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">

    <!-- Exibe Mensagens de Feedback -->
    <?php if ($mensagem): ?>
        <div class="<?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' ?> border px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?= $mensagem // Permite HTML para o link de simulação ?></span>
        </div>
    <?php endif; ?>


    <?php if ($mostrar_form_email): ?>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Recuperar Senha</h1>
        <p class="text-gray-600 mb-4">Digite seu e-mail cadastrado. Enviaremos um link para você redefinir sua senha.</p>
        <form action="/recuperar_senha.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500" 
                       required>
            </div>
            <div>
                <button type="submit" 
                        class="w-full bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">
                    Enviar Link de Recuperação
                </button>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($mostrar_form_reset): ?>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Definir Nova Senha</h1>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="mb-4">
                <label for="senha" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                <input type="password" id="senha" name="senha" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500" 
                       required>
            </div>
             <div class="mb-6">
                <label for="confirmar_senha" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500" 
                       required>
            </div>
            <div>
                <button type="submit" 
                        class="w-full bg-pink-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-pink-600 transition-all duration-300 shadow-md">
                    Redefinir Senha
                </button>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!$mostrar_form_email && !$mostrar_form_reset && !$mensagem): ?>
        <!-- Caso de token inválido já tratado no início -->
         <p class="text-gray-600">Retorne ao <a href="/login.php" class="text-pink-500 hover:underline">login</a>.</p>
    <?php endif; ?>
    
    <?php if ($mensagem && $tipo_mensagem == 'sucesso' && !$mostrar_form_reset): ?>
         <p class="text-gray-600 mt-4 text-center">Retorne ao <a href="/login.php" class="text-pink-500 hover:underline">login</a>.</p>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
