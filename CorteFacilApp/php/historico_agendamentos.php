<?php
require_once 'conexao.php';
require_once 'verificar_sessao.php';

try {
    // Ativar exibição de erros
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Obter o ID do usuário da sessão
    if (!isset($_SESSION['id'])) {
        throw new Exception('Sessão inválida');
    }
    $usuario_id = $_SESSION['id'];

    // Buscar agendamentos do usuário
    $sql = "SELECT a.*, s.nome_fantasia as nome_salao, p.nome as nome_profissional, sv.nome as nome_servico 
    FROM agendamentos a 
    JOIN saloes s ON a.salao_id = s.id 
    JOIN profissionais p ON a.profissional_id = p.id 
    JOIN servicos sv ON a.servico_id = sv.id 
    WHERE a.cliente_id = ? 
    ORDER BY a.data DESC, a.hora DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializar resposta
    $response = array(
        'status' => 'success',
        'agendamentos' => array()
    );

    // Verificar se há agendamentos
    if (empty($agendamentos)) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Formatar os agendamentos existentes
    foreach ($agendamentos as $agendamento) {
        // Formatar data e hora
        $data = date('d/m/Y', strtotime($agendamento['data']));
        $hora = date('H:i', strtotime($agendamento['hora']));
        
        // Verificar se o valor existe e não é nulo antes de formatar
        $valor = isset($agendamento['valor']) && $agendamento['valor'] !== null ? 
                 number_format($agendamento['valor'], 2, ',', '.') : '0,99';
                 
        $response['agendamentos'][] = array(
            'id' => $agendamento['id'],
            'salao' => $agendamento['nome_salao'],
            'profissional' => $agendamento['nome_profissional'],
            'servico' => $agendamento['nome_servico'],
            'data' => $data,
            'hora' => $hora,
            'status' => $agendamento['status'],
            'valor' => $valor
        );
    }

    // Retornar JSON
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Erro em historico_agendamentos.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Erro ao carregar agendamentos: ' . $e->getMessage()
    ));
}