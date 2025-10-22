Cupcake Shop - Projeto Integrador (PIT 2)

Este repositório contém o código-fonte de um sistema de e-commerce completo para uma loja de cupcakes, desenvolvido como parte do Projeto Integrador Transdisciplinar (PIT 2) em Engenharia de Software.

Sobre o Projeto

O objetivo deste projeto é construir uma aplicação web funcional que permita a clientes navegar por um catálogo de produtos, adicionar itens a um carrinho, criar uma conta de usuário e finalizar um pedido simulado. Além disso, o sistema conta com um painel administrativo protegido para gerenciamento de pedidos e futuras implementações de estoque e usuários.

O site foi projetado para ser hospedado no domínio cupcake-pit2-cruzeiro.com.br.

Tecnologias Utilizadas

Front-end: HTML5, Tailwind CSS, JavaScript (para interações dinâmicas como o carrinho de compras).

Back-end: PHP (para lógica do servidor, autenticação, gerenciamento de sessão e processamento de pedidos).

Banco de Dados: MySQL (para armazenamento de usuários, produtos, pedidos, endereços, etc.).

Estrutura de Pastas

O projeto é organizado de forma a separar claramente as responsabilidades (front-end, back-end, lógica de administração):

/
├── admin/                    # Contém todas as páginas do painel administrativo
│   ├── index.php             # Dashboard principal do admin
│   ├── admin_pedidos.php     # Lista de todos os pedidos
│   └── admin_pedido_detalhes.php # Detalhes e atualização de status de um pedido
│
├── backend/                  # Contém a lógica de negócio principal
│   ├── auth.php              # Funções de login, registro e controle de acesso
│   ├── carrinho_handler.php  # Lógica AJAX para adicionar ao carrinho
│   └── db.php                # Configuração e conexão com o banco de dados
│
├── imagens/                  # Imagens estáticas dos produtos
│   ├── chocolate_belga.png
│   └── ...
│
├── js/                       # Arquivos JavaScript do lado do cliente
│   └── main.js               # Scripts globais (ex: adicionar ao carrinho)
│
├── templates/                # Partes reutilizáveis do layout
│   ├── header.php            # Cabeçalho e menu de navegação
│   └── footer.php            # Rodapé
│
├── adicionar_endereco.php    # Formulário para adicionar novo endereço
├── carrinho.php              # Página do carrinho de compras
├── checkout.php              # Página de finalização de compra (endereço e pagamento)
├── editar_endereco.php       # Formulário para editar um endereço
├── index.php                 # Página inicial (vitrine de produtos)
├── login.php                 # Página de login
├── logout.php                # Script para encerrar a sessão do usuário
├── meus_pedidos.php          # Histórico de pedidos do cliente
├── pedido_confirmado.php     # Página de sucesso após finalizar a compra
├── pedido_detalhes.php       # Detalhes de um pedido específico (visão do cliente)
├── perfil.php                # Painel do usuário (dados e gerenciamento de endereços)
├── produto.php               # Página de detalhes de um produto específico
├── README.md                 # Este arquivo
├── register.php              # Página de cadastro de novo usuário
└── remover_endereco.php      # Script para remover um endereço


Como Executar

Banco de Dados: O banco de dados deve ser criado para o seu servidor MySQL (ex: via phpMyAdmin) para criar todas as tabelas e popular os produtos iniciais.

Configuração: Edite o arquivo backend/db.php e insira as suas credenciais de conexão com o banco de dados (host, nome do banco, usuário e senha). Neste projeto os dados sanitizados para evitar invasões.

Hospedagem: Envie todos os arquivos e pastas para o diretório raiz do seu servidor web (ex: public_html ).
