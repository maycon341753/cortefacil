<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Inserir usuário do salão
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'salao')");
    $nome = 'Salão Teste';
    $email = 'salao.teste@example.com';
    $senha = password_hash('123456', PASSWORD_DEFAULT);
    $stmt->bindValue(1, $nome, PDO::PARAM_STR);
    $stmt->bindValue(2, $email, PDO::PARAM_STR);
    $stmt->bindValue(3, $senha, PDO::PARAM_STR);
    $stmt->execute();
    $usuario_id = $conn->lastInsertId();
    
    // Inserir salão
    $stmt = $conn->prepare("INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
    $nome_fantasia = 'Barbearia Moderna';
    $documento = '12345678901';
    $cidade = 'São Paulo';
    $endereco = 'Rua Exemplo, 123';
    $whatsapp = '11999999999';
    $stmt->bindValue(1, $nome_fantasia, PDO::PARAM_STR);
    $stmt->bindValue(2, $documento, PDO::PARAM_STR);
    $stmt->bindValue(3, $cidade, PDO::PARAM_STR);
    $stmt->bindValue(4, $endereco, PDO::PARAM_STR);
    $stmt->bindValue(5, $whatsapp, PDO::PARAM_STR);
    $stmt->bindValue(6, $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $salao_id = $conn->lastInsertId();
    
    // Inserir profissionais
    $profissionais = [
        ['João Silva', 'Barbeiro'],
        ['Maria Santos', 'Cabeleireira'],
        ['Pedro Oliveira', 'Barbeiro']
    ];
    
    $stmt = $conn->prepare("INSERT INTO profissionais (nome, salao_id, especialidade) VALUES (?, ?, ?)");
    
    foreach ($profissionais as $prof) {
        $stmt->bindValue(1, $prof[0], PDO::PARAM_STR);
        $stmt->bindValue(2, $salao_id, PDO::PARAM_INT);
        $stmt->bindValue(3, $prof[1], PDO::PARAM_STR);
        $stmt->execute();
    }
    
    echo "Dados de teste inseridos com sucesso!";
    
} catch (Exception $e) {
    echo "Erro ao inserir dados de teste: " . $e->getMessage();
}
?>