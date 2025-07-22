<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

try {
    $pdo = getConexao();
    
    // Primeiro, verificar se há salões na base de dados
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM saloes WHERE ativo = 1");
    $countStmt->execute();
    $count = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Total de salões ativos: " . $count['total']);
    
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.nome_fantasia as nome,
            s.cidade,
            s.endereco,
            s.horario_abertura,
            s.horario_fechamento,
            s.dias_funcionamento,
            s.intervalo_agendamento,
            COALESCE(AVG(a.nota), 0) as avaliacao
        FROM saloes s
        LEFT JOIN avaliacoes a ON a.salao_id = s.id
        WHERE s.ativo = 1
        GROUP BY s.id, s.nome_fantasia, s.cidade, s.endereco, s.horario_abertura, s.horario_fechamento, s.dias_funcionamento, s.intervalo_agendamento
        ORDER BY s.nome_fantasia
    ");
    
    $stmt->execute();
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Salões encontrados: " . count($saloes));
    
    // Busca os profissionais ativos de cada salão
    $stmtProfissionais = $pdo->prepare("
        SELECT 
            id,
            nome,
            especialidade,
            ativo
        FROM profissionais
        WHERE salao_id = ? AND ativo = 1
    ");
    
    // Busca os serviços de cada salão
    $stmtServicos = $pdo->prepare("
        SELECT 
            id,
            nome,
            preco,
            duracao_minutos as duracao
        FROM servicos
        WHERE salao_id = ? AND ativo = 1
        ORDER BY nome ASC
    ");

    // Format the response
    $saloesFormatados = array_map(function($salao) use ($stmtProfissionais, $stmtServicos, $pdo) {
        // Busca profissionais do salão
        $stmtProfissionais->execute([$salao['id']]);
        $salao['profissionais'] = $stmtProfissionais->fetchAll(PDO::FETCH_ASSOC);
        
        // Busca serviços do salão
        $stmtServicos->execute([$salao['id']]);
        $salao['servicos'] = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert dias_funcionamento string to array
        if ($salao['dias_funcionamento']) {
            $salao['dias_funcionamento'] = array_map('intval', explode(',', $salao['dias_funcionamento']));
        } else {
            $salao['dias_funcionamento'] = [1,2,3,4,5,6];
        }
        
        // Format times for better display
        $salao['horario_abertura'] = substr($salao['horario_abertura'], 0, 5);
        $salao['horario_fechamento'] = substr($salao['horario_fechamento'], 0, 5);
        
        // Garantir que avaliacao seja um número
        $salao['avaliacao'] = floatval($salao['avaliacao']);
        
        return $salao;
    }, $saloes);
    
    echo json_encode([
        'status' => 'success', 
        'saloes' => $saloesFormatados,
        'total' => count($saloesFormatados),
        'debug' => [
            'total_db' => $count['total'],
            'found' => count($saloes)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao listar salões: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao buscar salões: ' . $e->getMessage(),
        'saloes' => [],
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno: ' . $e->getMessage(),
        'saloes' => []
    ]);
}
?>
