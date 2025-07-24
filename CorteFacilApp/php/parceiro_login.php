<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se é uma requisição de logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../parceiro_login.html');
    exit;
}

require_once 'conexao.php';
header('Content-Type: application/json');

// Recebe os dados do POST
$cpf = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
$senha = isset($_POST['senha']) ? $_POST['senha'] : '';

// Função para formatar CPF
function formatarCPF($cpf) {
    // Remove tudo que não é dígito
    $cpf = preg_replace('/\D/', '', $cpf);
    
    // Aplica a formatação
    if (strlen($cpf) == 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    
    return $cpf;
}

// Formatar o CPF para busca no banco
$cpf_formatado = formatarCPF($cpf);

// Validação básica
if (empty($cpf) || empty($senha)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Por favor, preencha todos os campos'
    ]);
    exit;
}

try {
    // Busca o usuário pelo CPF e tipo 'salao'
    $stmt = $conn->prepare("
        SELECT u.id, u.senha, u.nome, s.id as salao_id, s.nome_fantasia
        FROM usuarios u
        JOIN saloes s ON s.usuario_id = u.id
        WHERE u.tipo = 'salao' 
        AND u.cpf = :cpf
        AND s.ativo = 1
    ");
    
    $stmt->execute(['cpf' => $cpf_formatado]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Login bem sucedido
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['tipo'] = 'salao';
        $_SESSION['salao_id'] = $usuario['salao_id'];
        $_SESSION['nome'] = $usuario['nome_fantasia'];

        echo json_encode([
            'status' => 'success',
            'mensagem' => 'Login realizado com sucesso'
        ]);
    } else {
        // Login falhou
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'mensagem' => 'CPF ou senha incorretos'
        ]);
    }

} catch (PDOException $e) {
    error_log("Erro no login do parceiro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Erro ao realizar login. Tente novamente.'
    ]);
}
?>