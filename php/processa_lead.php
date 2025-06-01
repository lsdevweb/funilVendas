<?php
// processa_lead.php
// Configurações de exibição de erros (útil para desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir o cabeçalho para JSON para que o JS possa interpretar a resposta
header('Content-Type: application/json');

// --- INCLUSÃO MANUAL DO PHPMailer ---
require '../vendor/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailer-master/src/SMTP.php';
// ----------------------------------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclui o arquivo de configuração que agora contém todas as constantes
require_once 'config.php'; // Este caminho está correto

// Resposta padrão para o JavaScript
$response = ['success' => false, 'message' => 'Ocorreu um erro desconhecido.'];

// 1. Verifica se a requisição é do tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Conexão com o Banco de Dados (agora usando as constantes do config.php)
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Verifica a conexão
    if ($conn->connect_error) {
        error_log("Erro de conexão com o banco de dados: " . $conn->connect_error);
        $response['message'] = "Não foi possível processar sua solicitação no momento. Por favor, tente novamente mais tarde."; // Mensagem mais genérica para o usuário
        echo json_encode($response);
        exit;
    }

    // 3. Recebendo e Sanitizando os Dados do Formulário
    $nome        = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $email      = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone   = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $objetivo   = filter_input(INPUT_POST, 'objetivo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $name_formatted = ucwords(strtolower($nome));
    $origem = "Formulário Principal - Download PDF";

    // 4. Validação Adicional no Servidor (PHP)
    if (empty($nome) || empty($email) || empty(trim($telefone)) || empty($objetivo)) {
        $response['message'] = "Todos os campos obrigatórios devem ser preenchidos.";
        echo json_encode($response);
        $conn->close();
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "O formato do e-mail é inválido.";
        echo json_encode($response);
        $conn->close();
        exit;
    }
    if (str_word_count($nome) < 2) {
        $response['message'] = 'Por favor, digite seu nome completo (nome e sobrenome).';
        echo json_encode($response);
        $conn->close();
        exit;
    }
    $telefone_limpo = preg_replace('/\D/', '', $telefone);
    if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
        $response['message'] = 'Por favor, digite um número de telefone válido com 10 ou 11 dígitos (incluindo DDD).';
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // 5. Verificação de E-mail Duplicado
    $stmt_check = $conn->prepare("SELECT id FROM " . DB_TABLE . " WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    $email_existente = ($stmt_check->num_rows > 0);
    $stmt_check->close();

    // 6. Preparar e Executar a Inserção dos Dados (SE NÃO FOR DUPLICADO)
    if (!$email_existente) {
        $stmt_insert = $conn->prepare("INSERT INTO " . DB_TABLE . " (nome, email, telefone, objetivo, origem, data_cadastro) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt_insert->bind_param("sssss", $nome, $email, $telefone_limpo, $objetivo, $origem);

        if (!$stmt_insert->execute()) {
            $response['message'] = "Erro ao cadastrar lead: " . $stmt_insert->error;
            error_log("Erro no DB (insert): " . $stmt_insert->error);
            echo json_encode($response);
            $stmt_insert->close();
            $conn->close();
            exit;
        }
        $stmt_insert->close();
    }

    // 7. Fechar a conexão com o banco de dados
    $conn->close();

    // --- Lógica de Envio de E-mail com PHPMailer ---
    $mail = new PHPMailer(true);

    try {
        // Configurações do Servidor SMTP (AGORA USANDO CONSTANTES DO CONFIG.PHP)
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        // PHPMailer::ENCRYPTION_SMTPS é para 'ssl' na porta 465
        $mail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Remetente e Destinatário (AGORA USANDO CONSTANTES DO CONFIG.PHP)
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($email, $name_formatted);
        $mail->addReplyTo(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);

        // Anexo do PDF
        $pdf_path = '../pdf/5-erros-financeiros.pdf';
        if (file_exists($pdf_path)) {
            $mail->addAttachment($pdf_path, '5-erros-finaceiros-lucimar-santos.pdf');
        } else {
            error_log("Erro: PDF não encontrado em: " . $pdf_path);
        }

        // Conteúdo do E-mail
        $mail->isHTML(true);
        $mail->Subject = 'Seu Guia Gratuito: 5 Erros Financeiros que Travam Seu Negócio!';

        // Carrega o template HTML do e-mail
        $template_path = '../templates/email.html';
        if (file_exists($template_path)) {
            $email_template = file_get_contents($template_path);
            $mail->Body = str_replace('[Nome do Lead]', $name_formatted, $email_template);
        } else {
            error_log("Erro: Template de e-mail não encontrado em: " . $template_path);
            $mail->Body = 'Olá, ' . $name_formatted . '! Seu guia "5 Erros Financeiros que Travam Seu Negócio" está em anexo. Obrigado pelo seu interesse!';
        }

        $mail->send();

        $response['success'] = true;
        $response['message'] = "Seu guia foi enviado para o seu e-mail! Redirecionando...";
        if ($email_existente) {
             $response['message'] = "Este e-mail já está cadastrado em nossa base. Reenviamos o guia para você! Redirecionando...";
        }

    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: {$mail->ErrorInfo} - Detalhes: " . $e->getMessage());
        $response['message'] = "Houve um problema ao enviar o e-mail. Por favor, verifique sua caixa de spam ou tente novamente mais tarde.";
    }

} else {
    $response['message'] = "Método de requisição inválido.";
}

// Retorna a resposta em JSON para o JavaScript
echo json_encode($response);
?>
