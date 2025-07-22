<?php
session_start();
include 'conexao.php';

// Verifica se já existe uma sessão de admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso não autorizado']);
    exit;
}

// Recebe os dados do POST
$dados = json_decode(file_get_contents('php://input'), true);

try {
    // Validações básicas
    if (!isset($dados['nome']) || !isset($dados['email']) || !isset($dados['cpf']) || !isset($dados['senha'])) {
        throw new Exception("Todos os campos são obrigatórios");
    }

    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $cpf = trim($dados['cpf']);
    $senha = $dados['senha'];

    // Validações específicas
    if (strlen($nome) < 3) {
        throw new Exception("O nome deve ter pelo menos 3 caracteres");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }

    if (strlen($cpf) !== 11 || !is_numeric($cpf)) {
        throw new Exception("CPF inválido");
    }

    if (strlen($senha) < 6) {
        throw new Exception("A senha deve ter pelo menos 6 caracteres");
    }

    // Verifica se o email já está cadastrado
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception("Este email já está cadastrado");
    }

    // Verifica se o CPF já está cadastrado
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cpf = :cpf");
    $stmt->execute(['cpf' => $cpf]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception("Este CPF já está cadastrado");
    }

    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere o novo administrador
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, cpf, senha, tipo) VALUES (:nome, :email, :cpf, :senha, 'admin')");
    
    $success = $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'cpf' => $cpf,
        'senha' => $senha_hash
    ]);

    if ($success) {
        // Log da criação
        error_log("Novo administrador criado - Email: " . $email . " por Admin ID: " . $_SESSION['admin_id']);
        
        echo json_encode([
            'status' => 'ok',
            'mensagem' => 'Administrador criado com sucesso'
        ]);
    } else {
        throw new Exception("Erro ao criar administrador");
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
?>