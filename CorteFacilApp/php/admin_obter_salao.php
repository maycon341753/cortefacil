<?php
session_start();
include 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

// Recebe o ID do salão
$id = $_GET['id'] ?? 0;

try {
    $stmt = $conn->prepare("SELECT 
        id,
        nome_fantasia,
        documento,
        cidade,
        endereco,
        whatsapp,
        num_funcionarios,
        media_diaria,
        media_semanal,
        pix_chave,
        horario_abertura,
        horario_fechamento,
        intervalo_agendamento,
        dias_funcionamento
        FROM saloes 
        WHERE id = :id");
        
    $stmt->execute(['id' => $id]);
    
    if ($salao = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($salao);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Salão não encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
?> 