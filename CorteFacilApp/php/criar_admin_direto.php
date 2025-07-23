<?php
// Script para criar um usuário administrador diretamente no banco
include 'conexao.php';

header('Content-Type: application/json');

try {
    // Dados do administrador
    $nome = 'Administrador';
    $email = 'mayconreis2030@gmail.com';
    $senha = 'Brava1997';
    $cpf = '00000000000'; // CPF padrão para admin

    // Verifica se o email já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Este email já está cadastrado'
        ]);
        exit;
    }

    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere o administrador
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, cpf, senha, tipo, data_cadastro) VALUES (:nome, :email, :cpf, :senha, 'admin', NOW())");
    
    $success = $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'cpf' => $cpf,
        'senha' => $senha_hash
    ]);

    if ($success) {
        $admin_id = $conn->lastInsertId();
        
        echo json_encode([
            'status' => 'ok',
            'mensagem' => 'Administrador criado com sucesso',
            'admin_id' => $admin_id,
            'email' => $email
        ]);
        
        // Log da criação
        error_log("Administrador criado - ID: $admin_id, Email: $email");
    } else {
        throw new Exception("Erro ao inserir administrador no banco");
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao criar administrador: ' . $e->getMessage()
    ]);
    error_log("Erro ao criar admin: " . $e->getMessage());
}
?>