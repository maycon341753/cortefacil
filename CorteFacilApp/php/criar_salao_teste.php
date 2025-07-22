<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Criar usuário do tipo salão
    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'salao')";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['Salão Teste', 'salao@teste.com', password_hash('123456', PASSWORD_DEFAULT)]);
    
    $usuario_id = $conn->lastInsertId();
    
    // Criar salão
    $sql = "INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'Salão Teste',
        '12.345.678/0001-90',
        'São Paulo',
        'Rua Teste, 123',
        '(11) 99999-9999',
        $usuario_id
    ]);
    
    $salao_id = $conn->lastInsertId();
    
    // Criar funcionário
    $sql = "INSERT INTO funcionarios (nome, email, senha, telefone, salao_id) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'Funcionário Teste',
        'funcionario@teste.com',
        password_hash('123456', PASSWORD_DEFAULT),
        '(11) 88888-8888',
        $salao_id
    ]);
    
    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Dados de teste criados com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}