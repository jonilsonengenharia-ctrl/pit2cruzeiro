<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enderecoID = $_POST['enderecoID'] ?? null;

    if ($enderecoID) {
        try {
            // Tenta deletar o endereço
            $stmt = $pdo->prepare("DELETE FROM Enderecos WHERE enderecoID = ? AND usuarioID = ?");
            $stmt->execute([$enderecoID, $_SESSION['usuarioID']]);

            // Se for bem-sucedido, define uma mensagem de sucesso
            $_SESSION['success_message'] = "Endereço removido com sucesso!";

        } catch (PDOException $e) {
            // Verifica se o erro é de restrição de chave estrangeira
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = "Não é possível remover este endereço, pois ele já está vinculado a um pedido.";
            } else {
                // Para outros erros de banco de dados
                $_SESSION['error_message'] = "Ocorreu um erro ao tentar remover o endereço.";
                
            }
        }
    }
}

// Redireciona de volta para a página de perfil
header('Location: /perfil.php');
exit();
?>

