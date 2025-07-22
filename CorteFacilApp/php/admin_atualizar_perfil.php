<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
    exit;
}

try {
    require_once 'conexao.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
    } else {
        $nome = $input['nome'] ?? '';
        $email = $input['email'] ?? '';
        $senhaAtual = $input['senha_atual'] ?? '';
        $novaSenha = $input['nova_senha'] ?? '';
    }
    
    // Validações básicas
    if (empty($nome) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Nome e email são obrigatórios']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Email inválido']);
        exit;
    }
    
    // Verificar se o email já existe para outro usuário
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
    $stmt->execute(['email' => $email, 'id' => $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Este email já está sendo usado por outro usuário']);
        exit;
    }
    
    // Se foi fornecida uma nova senha, validar a senha atual
    if (!empty($novaSenha)) {
        if (empty($senhaAtual)) {
            echo json_encode(['status' => 'error', 'message' => 'Senha atual é obrigatória para alterar a senha']);
            exit;
        }
        
        if (strlen($novaSenha) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'A nova senha deve ter pelo menos 6 caracteres']);
            exit;
        }
        
        // Verificar senha atual
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin || !password_verify($senhaAtual, $admin['senha'])) {
            echo json_encode(['status' => 'error', 'message' => 'Senha atual incorreta']);
            exit;
        }
        
        // Atualizar com nova senha
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha WHERE id = :id");
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senhaHash,
            'id' => $_SESSION['user_id']
        ]);
    } else {
        // Atualizar apenas nome e email
        $stmt = $conn->prepare("UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id");
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'id' => $_SESSION['user_id']
        ]);
    }
    
    // Atualizar sessão
    $_SESSION['user_name'] = $nome;
    
    echo json_encode([
        'status' => 'ok',
        'message' => 'Perfil atualizado com sucesso',
        'admin' => [
            'nome' => $nome,
            'email' => $email
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>