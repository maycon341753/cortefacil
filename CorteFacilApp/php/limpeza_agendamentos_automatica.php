<?php
/**
 * Script de Limpeza Automática de Agendamentos
 * Remove agendamentos com mais de 30 dias da data de criação
 * 
 * Este script deve ser executado periodicamente (diariamente via cron job ou task scheduler)
 */

require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getConexao();
    
    // Calcular data limite (30 dias atrás)
    $dataLimite = date('Y-m-d H:i:s', strtotime('-30 days'));
    
    // Primeiro, vamos contar quantos agendamentos serão removidos
    $sqlCount = "SELECT COUNT(*) as total FROM agendamentos WHERE criado_em < :data_limite";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute(['data_limite' => $dataLimite]);
    $totalParaRemover = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Se não há agendamentos para remover, retorna sucesso
    if ($totalParaRemover == 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Nenhum agendamento antigo encontrado para remoção',
            'removidos' => 0,
            'data_limite' => $dataLimite
        ]);
        exit;
    }
    
    // Buscar detalhes dos agendamentos que serão removidos (para log)
    $sqlDetalhes = "SELECT 
                        a.id, 
                        a.criado_em, 
                        a.data, 
                        a.status,
                        c.nome as cliente_nome,
                        s.nome_fantasia as salao_nome
                    FROM agendamentos a
                    JOIN clientes c ON a.cliente_id = c.id
                    JOIN saloes s ON a.salao_id = s.id
                    WHERE a.criado_em < :data_limite
                    ORDER BY a.criado_em ASC";
    
    $stmtDetalhes = $pdo->prepare($sqlDetalhes);
    $stmtDetalhes->execute(['data_limite' => $dataLimite]);
    $agendamentosParaRemover = $stmtDetalhes->fetchAll(PDO::FETCH_ASSOC);
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Remover os agendamentos antigos
    $sqlDelete = "DELETE FROM agendamentos WHERE criado_em < :data_limite";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute(['data_limite' => $dataLimite]);
    
    $agendamentosRemovidos = $stmtDelete->rowCount();
    
    // Registrar a limpeza em log (opcional - criar tabela de logs se necessário)
    $sqlLog = "INSERT INTO logs_limpeza_agendamentos 
               (data_execucao, agendamentos_removidos, data_limite_aplicada) 
               VALUES (NOW(), :removidos, :data_limite)";
    
    try {
        $stmtLog = $pdo->prepare($sqlLog);
        $stmtLog->execute([
            'removidos' => $agendamentosRemovidos,
            'data_limite' => $dataLimite
        ]);
    } catch (PDOException $e) {
        // Se a tabela de log não existir, criar ela
        $sqlCreateLog = "CREATE TABLE IF NOT EXISTS logs_limpeza_agendamentos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            data_execucao DATETIME NOT NULL,
            agendamentos_removidos INT(11) NOT NULL,
            data_limite_aplicada DATETIME NOT NULL,
            PRIMARY KEY (id)
        )";
        $pdo->exec($sqlCreateLog);
        
        // Tentar inserir o log novamente
        $stmtLog = $pdo->prepare($sqlLog);
        $stmtLog->execute([
            'removidos' => $agendamentosRemovidos,
            'data_limite' => $dataLimite
        ]);
    }
    
    // Confirmar transação
    $pdo->commit();
    
    // Log detalhado para arquivo (opcional)
    $logMessage = date('Y-m-d H:i:s') . " - Limpeza automática executada: {$agendamentosRemovidos} agendamentos removidos (criados antes de {$dataLimite})\n";
    error_log($logMessage, 3, __DIR__ . '/logs/limpeza_agendamentos.log');
    
    echo json_encode([
        'status' => 'success',
        'message' => "Limpeza automática executada com sucesso",
        'removidos' => $agendamentosRemovidos,
        'data_limite' => $dataLimite,
        'detalhes' => $agendamentosParaRemover
    ]);

} catch (Exception $e) {
    // Reverter transação em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Erro na limpeza automática de agendamentos: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao executar limpeza automática: ' . $e->getMessage()
    ]);
}
?>