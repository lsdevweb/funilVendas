<?php
// config.php

// --- Configurações do Banco de Dados ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', '');
define('DB_PASSWORD', ''); // Sua senha real
define('DB_NAME', '');
define('DB_TABLE', '');   // O nome da sua tabela de leads

// --- Configurações do PHPMailer (E-mail) ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465); // Porta 465 para ENCRYPTION_SMTPS
define('SMTP_ENCRYPTION', 'ssl'); // ou 'tls' dependendo do caso, mas 'ssl' para 465
define('EMAIL_FROM_ADDRESS', '');
define('EMAIL_FROM_NAME', '');
define('SMTP_USERNAME', ''); // Geralmente o mesmo que EMAIL_FROM_ADDRESS
define('SMTP_PASSWORD', '');       // Sua senha de app do Gmail


// A parte de conexão com o banco de dados foi removida daqui,
// pois ela será tratada no arquivo processa_lead.php usando as constantes acima.
?>
