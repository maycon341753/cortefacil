<?php
// Iniciar a sessão antes de qualquer saída
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Depurar a sessão
    $debug_session = [
        'session_id' => session_id(),
        'session_data' => []
    ];
    
    foreach ($_SESSION as $key => $value) {
        $debug_session['session_data'][$key] = $value;
    }
    
    // Verificar se o usuário está logado e tem salao_id
    if (!isset($_SESSION['salao_id'])) {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Usuário não autenticado ou salao_id não definido',
            'debug_session' => $debug_session
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("
        SELECT 
            id,
            nome,
            especialidade,
            valor_servico
        FROM profissionais 
        WHERE salao_id = :salao_id
        AND ativo = 1
        ORDER BY nome
    ");
    
    $stmt->execute(['salao_id' => $_SESSION['salao_id']]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'profissionais' => $profissionais,
        'debug_session' => $debug_session
    ]);

} catch (PDOException $e) {
    error_log("Erro ao listar profissionais: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao listar profissionais',
        'debug' => $e->getMessage()
    ]);
}
?>