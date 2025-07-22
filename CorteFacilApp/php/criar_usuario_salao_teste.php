<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Dados do usuário de teste
    $email = 'teste@salao.com';
    $senha = password_hash('123456', PASSWORD_DEFAULT);
    $nome = 'Usuário Teste';
    
    // Insere o usuário
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, 'salao')");
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'senha' => $senha
    ]);
    
    $usuario_id = $conn->lastInsertId();
    
    // Insere o salão
    $stmt = $conn->prepare("INSERT INTO saloes (usuario_id, nome_fantasia, ativo) VALUES (:usuario_id, :nome_fantasia, 1)");
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'nome_fantasia' => 'Salão Teste'
    ]);
    
    echo "Usuário e salão de teste criados com sucesso!\n";
    echo "Email: teste@salao.com\n";
    echo "Senha: 123456\n";
    
} catch (PDOException $e) {
    echo "Erro ao criar usuário de teste: " . $e->getMessage();
}
?>