<?php
session_start();
include 'conexao.php';

// Função removida - Não usamos mais a API do WhatsApp

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

// Recebe os dados do POST
$dados = json_decode(file_get_contents('php://input'), true);

try {
    // Cria um usuário para o salão
    $senha = bin2hex(random_bytes(8)); // Gera senha aleatória
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Insere o usuário do salão
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, 'salao')");
    $stmt->execute([
        'nome' => $dados['nome_fantasia'],
        'email' => $dados['documento'],
        'senha' => $senha_hash
    ]);
    $usuario_id = $conn->lastInsertId();

    // Cadastra o salão
    $stmt = $conn->prepare("INSERT INTO saloes (
        nome_fantasia,
        documento,
        cidade,
        endereco,
        whatsapp,
        num_funcionarios,
        media_diaria,
        media_semanal,
        pix_chave,
        usuario_id,
        horario_abertura,
        horario_fechamento,
        dias_funcionamento,
        intervalo_agendamento
    ) VALUES (
        :nome_fantasia,
        :documento,
        :cidade,
        :endereco,
        :whatsapp,
        :num_funcionarios,
        :media_diaria,
        :media_semanal,
        :pix_chave,
        :usuario_id,
        :horario_abertura,
        :horario_fechamento,
        :dias_funcionamento,
        :intervalo_agendamento
    )");

    // Converte o array de dias em string
    $dias_funcionamento = '';
    if (isset($dados['dias_funcionamento'])) {
        $dias_funcionamento = is_array($dados['dias_funcionamento']) ? 
            implode(',', $dados['dias_funcionamento']) : 
            $dados['dias_funcionamento'];
    }

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
        'usuario_id' => $usuario_id,
        'horario_abertura' => $dados['horario_abertura'],
        'horario_fechamento' => $dados['horario_fechamento'],
        'dias_funcionamento' => $dias_funcionamento,
        'intervalo_agendamento' => $dados['intervalo_agendamento']
    ]);

    if ($success) {
        // Certifique-se de definir o cabeçalho Content-Type para application/json
        header('Content-Type: application/json');
        
        // Retorna as credenciais para exibição na interface
        echo json_encode([
            'status' => 'ok',
            'mensagem' => 'Salão cadastrado com sucesso',
            'credenciais' => [
                'email' => $dados['documento'],
                'senha' => $senha
            ]
        ]);
    } else {
        // Se der erro, exclui o usuário criado
        $conn->prepare("DELETE FROM usuarios WHERE id = :id")->execute(['id' => $usuario_id]);
        throw new Exception("Erro ao cadastrar salão");
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
?>
