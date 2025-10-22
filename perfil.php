<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/auth.php';
requireLogin();

// Busca os dados do usuário
$stmtUsuario = $pdo->prepare("SELECT nome, email, telefone FROM Usuarios WHERE usuarioID = ?");
$stmtUsuario->execute([$_SESSION['usuarioID']]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

// Busca os endereços do usuário, ordenando para que o padrão (se houver) apareça primeiro
$stmtEnderecos = $pdo->prepare("SELECT * FROM Enderecos WHERE usuarioID = ? ORDER BY isPadrao DESC, enderecoID ASC");
$stmtEnderecos->execute([$_SESSION['usuarioID']]);
$enderecos = $stmtEnderecos->fetchAll(PDO::FETCH_ASSOC);

// Debug: Pega as mensagens da sessão, se existirem, para exibir feedback
$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['error_message'], $_SESSION['success_message']); // Limpa as mensagens para não mostrar novamente

require_once __DIR__ . '/templates/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Minha Conta</h1>

        <!-- Debug: Bloco para exibir mensagens de feedback (erro ou sucesso) -->
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($success_message) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Menu Lateral de Navegação -->
            <aside class="md:col-span-1">
                <div class="bg-white p-4 rounded-lg shadow">
                    <nav class="space-y-2">
                        <a href="/perfil.php" class="flex items-center px-4 py-2 text-pink-600 bg-pink-50 rounded-lg font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Meu Perfil
                        </a>
                        <a href="/meus_pedidos.php" class="flex items-center px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">
                             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Meus Pedidos
                        </a>
                    </nav>
                </div>
            </aside>

            <!-- Conteúdo Principal -->
            <section class="md:col-span-2">
                <!-- Card: Detalhes do Perfil -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Detalhes do Perfil</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="font-semibold text-gray-600">Nome:</span>
                            <p class="text-gray-800"><?= htmlspecialchars($usuario['nome']) ?></p>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-600">Email:</span>
                            <p class="text-gray-800"><?= htmlspecialchars($usuario['email']) ?></p>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-600">Telefone:</span>
                            <p class="text-gray-800"><?= htmlspecialchars($usuario['telefone'] ?: 'Não informado') ?></p>
                        </div>
                    </div>
                     <a href="#" class="mt-4 inline-block text-sm text-pink-500 hover:underline font-medium">Editar Perfil</a>
                </div>

                <!-- Card: Meus Endereços -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Meus Endereços</h2>
                        <a href="/adicionar_endereco.php" class="bg-pink-500 text-white text-sm font-bold py-2 px-3 rounded-lg hover:bg-pink-600 shadow transition-transform transform hover:scale-105">+ Adicionar Novo</a>
                    </div>
                    <div class="space-y-4">
                        <?php if (empty($enderecos)): ?>
                            <p class="text-gray-500 italic">Nenhum endereço cadastrado ainda.</p>
                        <?php else: ?>
                            <?php foreach($enderecos as $endereco): ?>
                                <div class="border p-4 rounded-lg flex justify-between items-start hover:bg-gray-50">
                                    <div>
                                        <p class="font-bold text-gray-800"><?= htmlspecialchars($endereco['logradouro']) ?>, <?= htmlspecialchars($endereco['numero']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($endereco['bairro']) ?>, <?= htmlspecialchars($endereco['cidade']) ?> - <?= htmlspecialchars($endereco['estado']) ?></p>
                                        <p class="text-sm text-gray-500">CEP: <?= htmlspecialchars($endereco['cep']) ?></p>
                                        <?php if ($endereco['complemento']): ?>
                                            <p class="text-sm text-gray-500">Comp: <?= htmlspecialchars($endereco['complemento']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                        <a href="/editar_endereco.php?id=<?= $endereco['enderecoID'] ?>" class="text-blue-500 hover:underline text-sm font-medium">Editar</a>
                                        <form action="/remover_endereco.php" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este endereço?');">
                                            <input type="hidden" name="enderecoID" value="<?= $endereco['enderecoID'] ?>">
                                            <button type="submit" class="text-red-500 hover:underline text-sm font-medium">Remover</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>


