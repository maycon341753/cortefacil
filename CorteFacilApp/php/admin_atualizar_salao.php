<?php
session_start();
include 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

// Recebe os dados do POST
$dados = json_decode(file_get_contents('php://input'), true);

try {
    // Atualiza o salão
    $stmt = $conn->prepare("UPDATE saloes SET 
        nome_fantasia = :nome_fantasia,
        documento = :documento,
        cidade = :cidade,
        endereco = :endereco,
        whatsapp = :whatsapp,
        num_funcionarios = :num_funcionarios,
        media_diaria = :media_diaria,
        media_semanal = :media_semanal,
        pix_chave = :pix_chave,
        horario_abertura = :horario_abertura,
        horario_fechamento = :horario_fechamento,
        dias_funcionamento = :dias_funcionamento,
        intervalo_agendamento = :intervalo_agendamento
        WHERE id = :id");

    $success = $stmt->execute([
        'nome_fantasia' => $dados['nome_fantasia'],
        'documento' => $dados['documento'],
        'cidade' => $dados['cidade'],
        'endereco' => $dados['endereco'],
        'whatsapp' => $dados['whatsapp'],
        'num_funcionarios' => $dados['num_funcionarios'],
        'media_diaria' => $dados['media_diaria'],
        'media_semanal' => $dados['media_semanal'],
        'pix_chave' => $dados['pix_chave'],
        'horario_abertura' => $dados['horario_abertura'],
        'horario_fechamento' => $dados['horario_fechamento'],
        'dias_funcionamento' => implode(',', $dados['dias_funcionamento']),
        'intervalo_agendamento' => $dados['intervalo_agendamento'],
        'id' => $dados['id']
    ]);

    if ($success) {
        // Atualiza também o nome do usuário
        $stmt = $conn->prepare("UPDATE usuarios SET nome = :nome WHERE id = (SELECT usuario_id FROM saloes WHERE id = :id)");
        $stmt->execute([
            'nome' => $dados['nome_fantasia'],
            'id' => $dados['id']
        ]);

        echo json_encode(['status' => 'ok', 'mensagem' => 'Salão atualizado com sucesso']);
    } else {
        throw new Exception("Erro ao atualizar salão");
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
?>
