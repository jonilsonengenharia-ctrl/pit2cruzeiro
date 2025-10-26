<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['usuarioID'])) {
        // Guarda a URL que o usuário tentou acessar
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit();
    }
}

// NOVA FUNÇÃO: Protege as páginas do painel administrativo
function requireAdmin() {
    // Primeiro, verifica se o usuário está logado
    if (!isset($_SESSION['usuarioID'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit();
    }
    // Depois, verifica se o nível de acesso é permitido
    $niveisPermitidos = ['admin', 'gerente'];
    if (!in_array($_SESSION['nivelAcesso'], $niveisPermitidos)) {
        // Se não tiver permissão, redireciona para a página inicial ou de perfil
        header('Location: /perfil.php');
        exit();
    }
}


function is_logged_in() {
    return isset($_SESSION['usuarioID']);
}

function get_user_id() {
    return $_SESSION['usuarioID'] ?? null;
}

function get_user_role() {
    return $_SESSION['nivelAcesso'] ?? null;
}
