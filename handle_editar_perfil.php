<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Apenas POST é permitido
    header('Location: /editar_perfil.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$usuarioID = $_SESSION['usuarioID'];

// Validação simples
if (empty($nome)) {
    $_SESSION['error_message'] = "O campo 'Nome' é obrigatório.";
    header('Location: /editar_perfil.php');
    exit;
}

try {
    // Atualiza os dados no banco
    $stmt = $pdo->prepare("UPDATE Usuarios SET nome = ?, telefone = ? WHERE usuarioID = ?");
    $stmt->execute([$nome, $telefone, $usuarioID]);

    // Atualiza o nome na sessão para refletir imediatamente no site
    $_SESSION['nome'] = $nome;

    // Define mensagem de sucesso e redireciona
    $_SESSION['success_message'] = "Perfil atualizado com sucesso!";
    header('Location: /perfil.php');
    exit;

} catch (Exception $e) {
    // Em caso de erro
    $_SESSION['error_message'] = "Erro ao atualizar o perfil. Tente novamente.";
    // error_log($e->getMessage()); // Para debug
    header('Location: /editar_perfil.php');
    exit;
}
