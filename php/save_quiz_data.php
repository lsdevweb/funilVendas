<?php
error_reporting(E_ALL); // Exibe todos os erros
ini_set('display_errors', 1); // Exibe erros no navegador (para localhost)

// Inclui o arquivo de configuração
require_once 'config.php'; // Garanta que o caminho para config.php esteja correto

header("Access-Control-Allow-Origin: *"); // Permite requisições de qualquer origem (CORS)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type"); // Headers permitidos
header("Content-Type: application/json"); // Define o tipo de conteúdo da resposta como JSON

// Conexão com o Banco de Dados usando as constantes do config.php
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica a conexão
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Falha na conexão com o banco de dados: " . $conn->connect_error]);
    exit();
}

// Garante que a conexão com o banco de dados usa o charset correto (utf8mb4)
$conn->set_charset("utf8mb4");

// Recebe os dados JSON do frontend
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// *** LOGS DE DEBUG PARA VER O QUE O PHP ESTÁ RECEBENDO ***
error_log("DEBUG - JSON recebido: " . $json_data);
error_log("DEBUG - Dados decodificados: " . print_r($data, true));
// ******************************************************

// Validação básica dos dados
if (!isset($data['userEmail'])) {
    echo json_encode(["success" => false, "message" => "Email é obrigatório."]);
    exit();
}

// Escapando dados para segurança (prevenção de SQL Injection)
$email = $conn->real_escape_string($data['userEmail']);
// Se 'trafficSource' não existir em $data, usa 'diagnostico-web'
$origem = isset($data['trafficSource']) ? $conn->real_escape_string($data['trafficSource']) : 'diagnostico-web';

// Convertendo para tipos corretos e tratando valores ausentes como null
$score = isset($data['quizScore']) ? (int)$data['quizScore'] : null;
$qualificacao = isset($data['quizDiagnosis']) ? $conn->real_escape_string($data['quizDiagnosis']) : null;

// Capturando porte_negocio da resposta 'q1' dentro de 'answers'
$porte_negocio = isset($data['answers']['q1']) ? (int)$data['answers']['q1'] : null;
$tempo_operacao = isset($data['answers']['q2']) ? (int)$data['answers']['q2'] : null;
$setor_negocio = isset($data['answers']['q3']) ? $conn->real_escape_string($data['answers']['q3']) : null;

// Armazenar todas as respostas do quiz em um array para conversão em JSON
$todas_respostas = [
    'q1' => isset($data['answers']['q1']) ? $data['answers']['q1'] : null,
    'q2' => isset($data['answers']['q2']) ? $data['answers']['q2'] : null,
    'q3' => isset($data['answers']['q3']) ? $data['answers']['q3'] : null,
    'q4' => isset($data['answers']['q4']) ? $data['answers']['q4'] : null,
    'q5' => isset($data['answers']['q5']) ? $data['answers']['q5'] : null,
    'companyName' => isset($data['companyName']) ? $data['companyName'] : null,
    'quizScore' => $score,
    'quizDiagnosis' => $qualificacao
];

// Converte o array em string JSON
$todas_respostas_json = json_encode($todas_respostas);

// DEBUG: Verifique se json_encode falhou
if ($todas_respostas_json === false) {
    error_log("DEBUG: Erro de json_encode: " . json_last_error_msg());
    $todas_respostas_json = null; // Se falhar, define como NULL para o banco de dados
}
// Loga o conteúdo final do JSON que será inserido
error_log("DEBUG: Conteúdo de todas_respostas_json: " . ($todas_respostas_json ?? 'NULL (json_encode failed)'));

// Escapa a string JSON para inserção direta na query SQL
// Se $todas_respostas_json for NULL, a string será "NULL" (sem aspas)
$escaped_todas_respostas = ($todas_respostas_json !== null) ? "'" . $conn->real_escape_string($todas_respostas_json) . "'" : "NULL";

error_log("DEBUG - Valor final de todas_respostas_json antes da inserção (escapado): " . ($escaped_todas_respostas ?? 'NULL'));

// Prepara a query SQL para inserção - ATENÇÃO: todas_respostas está no meio, não é um '?'
$stmt = $conn->prepare("INSERT INTO respostas_quiz (email, origem, porte_negocio, tempo_operacao, setor_negocio, todas_respostas, score, qualificacao, data_submissao) VALUES (?, ?, ?, ?, ?, " . $escaped_todas_respostas . ", ?, ?, NOW())");

// VERIFICAR SE O PREPARE FALHOU
if ($stmt === false) {
    error_log("Erro ao preparar a query SQL: " . $conn->error);
    echo json_encode(["success" => false, "message" => "Erro interno do servidor ao preparar a query."]);
    exit();
}

// Linha 98 (ou próximo a ela, dependendo do editor)
// Vincula os parâmetros restantes à query.
// 'todas_respostas_json' e seu tipo 's' foram removidos daqui.
// Ordem: email, origem, porte_negocio, tempo_operacao, setor_negocio, score, qualificacao
// Tipos:  s       s         i               i           s           i        s
$stmt->bind_param("ssiisis", $email, $origem, $porte_negocio, $tempo_operacao, $setor_negocio, $score, $qualificacao); // ESTA LINHA ESTÁ CORRETA NESTE CÓDIGO!

// Executa a query
if ($stmt->execute()) {
    error_log("DEBUG - Inserção bem-sucedida!"); // Log para confirmar o sucesso
    echo json_encode(["success" => true, "message" => "Dados do quiz salvos com sucesso!"]);
    exit(); // <<< ADICIONE ESTA LINHA AQUI
} else {
    // Loga o erro específico retornado pelo MySQL durante a execução
    error_log("Erro ao executar a query: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Erro ao salvar os dados do quiz: " . $stmt->error]);
    exit(); // <<< E TAMBÉM ADICIONE ESTA LINHA AQUI
}

// Fecha a conexão com o banco de dados e o statement
$stmt->close();
$conn->close();

// Não há tag de fechamento PHP ?> para evitar problemas com espaços em branco após o script
