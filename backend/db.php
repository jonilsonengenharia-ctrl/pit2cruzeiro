<?php
$host = 'localhost';
$dbname = 'xxxxxxxxxxxxx';
$username = 'xxxxxxxxxxxxxxx';
$password = 'xxxxxxxxxxxxxxxxx';

// String de conexão (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // Cria a instância do PDO
    $pdo = new PDO($dsn, $username, $password);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Em caso de falha na conexão, exibe o erro e encerra o script
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
?>
