<?php
// backend/email_sender.php
// SUGESTÃO 5: SIMULAÇÃO DE ENVIO DE E-MAIL

/**
 * (SIMULAÇÃO) Envia um e-mail de confirmação de cadastro.
 * Em um projeto real, esta função usaria uma biblioteca como PHPMailer
 * para se conectar a um servidor SMTP e enviar um e-mail HTML.
 *
 * @param string $email O email do destinatário.
 * @param string $nome O nome do destinatário.
 * @param string $linkConfirmacao O link para o usuário clicar.
 */
function enviarEmailConfirmacao($email, $nome, $linkConfirmacao) {
    // Lógica de simulação:
    // Em um ambiente de desenvolvimento, podemos salvar isso em um arquivo de log
    // para verificar se a função foi chamada corretamente.
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] SIMULAÇÃO DE EMAIL:\n";
    $logMessage .= "Para: $email ($nome)\n";
    $logMessage .= "Assunto: Confirme seu cadastro na Cupcake Shop\n";
    $logMessage .= "Corpo: Olá $nome, clique no link para confirmar seu cadastro: $linkConfirmacao\n";
    $logMessage .= "------------------------------------------------------\n";
    
    // __DIR__ aponta para a pasta 'backend'. ../logs/email_log.txt
    $logFilePath = __DIR__ . '/../logs/email_log.txt'; 
    
    // Tenta criar o diretório de logs se não existir
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }

    // Salva a simulação no arquivo de log
    @file_put_contents($logFilePath, $logMessage, FILE_APPEND);

    // Para fins de debug no navegador (REMOVER EM PRODUÇÃO)
    // echo "<!-- SIMULAÇÃO: Email de confirmação enviado para $email -->";
}

/**
 * (SIMULAÇÃO) Envia um e-mail de recuperação de senha.
 *
 * @param string $email O email do destinatário.
 * @param string $nome O nome do destinatário.
 * @param string $linkReset O link para o usuário redefinir a senha.
 */
function enviarEmailRecuperacao($email, $nome, $linkReset) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] SIMULAÇÃO DE EMAIL:\n";
    $logMessage .= "Para: $email ($nome)\n";
    $logMessage .= "Assunto: Recuperação de Senha - Cupcake Shop\n";
    $logMessage .= "Corpo: Olá $nome, clique no link para redefinir sua senha: $linkReset\n";
    $logMessage .= "------------------------------------------------------\n";
    
    $logFilePath = __DIR__ . '/../logs/email_log.txt'; 
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }
    
    @file_put_contents($logFilePath, $logMessage, FILE_APPEND);
}

?>
