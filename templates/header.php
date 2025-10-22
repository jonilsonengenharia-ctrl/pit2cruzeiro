<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/auth.php';

$total_items = 0;
if (isset($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $item) {
        $total_items += $item['quantidade'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupcake Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-pink-50">
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold text-pink-600">Cupcake Shop</a>
            <div class="flex items-center space-x-4">
                <a href="/" class="text-gray-600 hover:text-pink-600">Loja</a>
                
                <?php if (is_logged_in()): ?>
                    <?php 
                    // Mostra o link do painel admin se o usuário tiver permissão
                    $niveisPermitidos = ['admin', 'gerente'];
                    if (in_array(get_user_role(), $niveisPermitidos)): 
                    ?>
                        <a href="/admin/index.php" class="text-gray-600 hover:text-pink-600 font-semibold">Painel Admin</a>
                    <?php endif; ?>
                    <a href="/perfil.php" class="text-gray-600 hover:text-pink-600">Meu Perfil</a>
                    <a href="/logout.php" class="text-gray-600 hover:text-pink-600">Sair</a>
                <?php else: ?>
                    <a href="/login.php" class="text-gray-600 hover:text-pink-600">Login</a>
                    <a href="/register.php" class="text-gray-600 hover:text-pink-600">Cadastro</a>
                <?php endif; ?>

                <a href="/carrinho.php" class="relative">
                    <svg class="w-6 h-6 text-gray-600 hover:text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-pink-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $total_items ?></span>
                </a>
            </div>
        </nav>
    </header>

