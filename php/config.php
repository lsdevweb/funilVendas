<?php
// config.php

// --- Configurações do Banco de Dados ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'lu4825');
define('DB_PASSWORD', 'pti@2018'); // Sua senha real
define('DB_NAME', 'funilVendas');
define('DB_TABLE', 'leads_pdf');   // O nome da sua tabela de leads

// --- Configurações do PHPMailer (E-mail) ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465); // Porta 465 para ENCRYPTION_SMTPS
define('SMTP_ENCRYPTION', 'ssl'); // ou 'tls' dependendo do caso, mas 'ssl' para 465
define('EMAIL_FROM_ADDRESS', 'lucimarcontas@gmail.com');
define('EMAIL_FROM_NAME', 'Lucimar Santos - Assistente Financeira');
define('SMTP_USERNAME', 'lucimarcontas@gmail.com'); // Geralmente o mesmo que EMAIL_FROM_ADDRESS
define('SMTP_PASSWORD', 'psnolddusihrjuhb');       // Sua senha de app do Gmail
// NOTA: 'psnolddusihrjuhb' é uma senha de aplicativo. NUNCA use sua senha principal do Gmail aqui!

// A parte de conexão com o banco de dados foi removida daqui,
// pois ela será tratada no arquivo processa_lead.php usando as constantes acima.
?>
