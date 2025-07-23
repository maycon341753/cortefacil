<?php
session_start();
include 'conexao.php';

header('Content-Type: application/json');

// Credenciais do administrador
$email = 'admin@cortefacil.com';
$senha = 'admin123';

try {
    // Verifica se o usuário existe na tabela usuarios com tipo 'admin'
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email AND tipo = 'admin'");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        if (password_verify($senha, $admin['senha'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_name'] = $admin['nome'];
            
            // Mantendo compatibilidade com código existente
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            $_SESSION['id'] = $admin['id'];
            $_SESSION['tipo'] = 'admin';
            $_SESSION['nome'] = $admin['nome'];
            
            echo json_encode([
                'status' => 'ok',
                'mensagem' => 'Login realizado com sucesso',
                'session_id' => session_id(),
                'session_data' => $_SESSION
            ]);
        } else {
            echo json_encode([
                'status' => 'erro', 
                'mensagem' => 'Senha inválida',
                'senha_fornecida' => $senha,
                'hash_armazenado' => $admin['senha']
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'erro', 
            'mensagem' => 'Administrador não encontrado'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro', 
        'mensagem' => 'Erro ao realizar login: ' . $e->getMessage()
    ]);
}
?>