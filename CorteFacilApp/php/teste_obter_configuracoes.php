<?php
// Iniciar a sessão antes de qualquer saída
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Simular login de parceiro
$_SESSION['id'] = 1;
$_SESSION['tipo'] = 'salao';
$_SESSION['salao_id'] = 1;
$_SESSION['nome'] = 'Salão Teste';

// Depurar a sessão
$debug_session = [
    'session_id' => session_id(),
    'session_data' => []
];

foreach ($_SESSION as $key => $value) {
    $debug_session['session_data'][$key] = $value;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            nome_fantasia,
            documento,
            horario_abertura,
            horario_fechamento,
            dias_funcionamento
        FROM saloes 
        WHERE id = :salao_id
    ");
    
    $stmt->execute(['salao_id' => $_SESSION['salao_id']]);
    $configuracoes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($configuracoes) {
        // Formata os horários para o formato HH:mm
        if (isset($configuracoes['horario_abertura'])) {
            $configuracoes['horario_abertura'] = substr($configuracoes['horario_abertura'], 0, 5);
        }
        
        if (isset($configuracoes['horario_fechamento'])) {
            $configuracoes['horario_fechamento'] = substr($configuracoes['horario_fechamento'], 0, 5);
        }
        
        echo json_encode([
            'status' => 'sucesso',
            'dados' => $configuracoes,
            'debug_session' => $debug_session
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Salão não encontrado',
            'debug_session' => $debug_session
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao obter configurações: ' . $e->getMessage(),
        'debug_session' => $debug_session
    ]);
}