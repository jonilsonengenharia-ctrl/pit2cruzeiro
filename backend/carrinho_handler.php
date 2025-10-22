<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$action = $_POST['action'] ?? '';
$produtoID = $_POST['produtoID'] ?? 0;

if ($action === 'add' && $produtoID > 0) {
    // Se o produto já existe no carrinho, incrementa a quantidade
    if (isset($_SESSION['carrinho'][$produtoID])) {
        $_SESSION['carrinho'][$produtoID]++;
    } else {
        // Se não, adiciona com quantidade 1
        $_SESSION['carrinho'][$produtoID] = 1;
    }
}

// Calcula a quantidade total de itens no carrinho
$totalItens = 0;
foreach ($_SESSION['carrinho'] as $quantidade) {
    $totalItens += $quantidade;
}

echo json_encode(['success' => true, 'totalItens' => $totalItens]);
?>
