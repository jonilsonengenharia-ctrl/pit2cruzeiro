<?php
// Inicia a sessão para poder destruí-la
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão.
session_destroy();

// Redireciona o usuário para a página inicial
header("Location: /");
exit();
?>
