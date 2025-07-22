<?php
header('Content-Type: application/json');
require_once 'conexao.php';

try {
    if (!isset($_GET['salao_id']) || !isset($_GET['profissional_id']) || !isset($_GET['data'])) {
        throw new Exception('Parâmetros inválidos');
    }

    $salao_id = filter_input(INPUT_GET, 'salao_id', FILTER_VALIDATE_INT);
    $profissional_id = filter_input(INPUT_GET, 'profissional_id', FILTER_VALIDATE_INT);
    $data = htmlspecialchars(trim($_GET['data']));

    if (!$salao_id || !$profissional_id || !$data) {
        throw new Exception('ID do salão, profissional ou data inválidos');
    }

    // Valida o formato da data
    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
    if (!$dataObj || $dataObj->format('Y-m-d') !== $data) {
        throw new Exception('Formato de data inválido');
    }

    $pdo = getConexao();

    // Busca informações do salão
    $stmt = $pdo->prepare("
        SELECT horario_abertura, horario_fechamento, intervalo_agendamento
        FROM saloes 
        WHERE id = ?
    ");
    $stmt->execute([$salao_id]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$salao) {
        throw new Exception('Salão não encontrado');
    }

    // Converte horários para objetos DateTime
    $abertura = new DateTime($data . ' ' . $salao['horario_abertura']);
    $fechamento = new DateTime($data . ' ' . $salao['horario_fechamento']);
    $intervalo = $salao['intervalo_agendamento'] ?? 30; // Intervalo padrão de 30 minutos

    // Busca horários já agendados
    $stmt = $pdo->prepare("
        SELECT TIME_FORMAT(hora, '%H:%i') as horario
        FROM agendamentos 
        WHERE profissional_id = ? 
        AND data = ?
        AND status NOT IN ('CANCELADO')
    ");
    $stmt->execute([$profissional_id, $data]);
    $horariosOcupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Gera lista de horários disponíveis
    $horariosDisponiveis = [];
    $horarioAtual = clone $abertura;

    while ($horarioAtual <= $fechamento) {
        $horarioStr = $horarioAtual->format('H:i');
        
        if (!in_array($horarioStr, $horariosOcupados)) {
            $horariosDisponiveis[] = $horarioStr;
        }
        
        $horarioAtual->modify("+{$intervalo} minutes");
    }

    echo json_encode([
        'status' => 'success',
        'data' => $horariosDisponiveis
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao consultar o banco de dados'
    ]);
} 