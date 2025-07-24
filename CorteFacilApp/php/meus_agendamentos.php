<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

try {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['id']) || !isset($_SESSION['tipo'])) {
        throw new Exception('Usuário não autenticado');
    }
    
    // Verificar se é um cliente
    if ($_SESSION['tipo'] !== 'cliente') {
        throw new Exception('Acesso negado');
    }

    $pdo = getConexao();
    
    // LIMPEZA AUTOMÁTICA: Remover agendamentos com mais de 30 dias
    $dataLimite = date('Y-m-d H:i:s', strtotime('-30 days'));
    $sqlLimpeza = "DELETE FROM agendamentos WHERE criado_em < :data_limite";
    $stmtLimpeza = $pdo->prepare($sqlLimpeza);
    $stmtLimpeza->execute(['data_limite' => $dataLimite]);
    
    // Buscar TODOS os agendamentos do cliente (confirmados, pagos, realizados e cancelados)
    $sql = "SELECT 
                a.id,
                a.data,
                a.hora,
                a.status,
                a.criado_em,
                s.nome_fantasia as salao,
                p.nome as profissional,
                serv.nome as servico,
                serv.preco
            FROM agendamentos a
            JOIN saloes s ON a.salao_id = s.id
            JOIN profissionais p ON a.profissional_id = p.id
            JOIN servicos serv ON a.servico_id = serv.id
            WHERE a.cliente_id = :cliente_id 
            ORDER BY 
                CASE 
                    WHEN a.status IN ('confirmado', 'pago') THEN 1
                    WHEN a.status = 'realizado' THEN 2
                    WHEN a.status = 'cancelado' THEN 3
                    ELSE 4
                END,
                a.criado_em DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cliente_id' => $_SESSION['id']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formata as datas e valores para exibição
    $agendamentosFormatados = array_map(function($agendamento) {
        // Formata a data para o padrão brasileiro
        $data = new DateTime($agendamento['data']);
        $agendamento['data'] = $data->format('d/m/Y');
        
        // Formata a hora (remove os segundos)
        $agendamento['hora'] = substr($agendamento['hora'], 0, 5);
        
        // Formata o preço
        $agendamento['preco'] = 'R$ ' . number_format($agendamento['preco'], 2, ',', '.');
        
        return $agendamento;
    }, $agendamentos);

    echo json_encode([
        'status' => 'success',
        'data' => $agendamentosFormatados
    ]);

} catch (Exception $e) {
    error_log("Erro ao buscar agendamentos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Erro ao buscar agendamentos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao buscar agendamentos'
    ]);
}
?>
