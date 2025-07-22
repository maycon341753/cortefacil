<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Definir uma senha padrão para o usuário
    $senha = '123456';
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        UPDATE usuarios u
        JOIN saloes s ON s.usuario_id = u.id
        SET u.senha = :senha
        WHERE s.documento = :documento
    ");
    
    $stmt->execute([
        'senha' => $senha_hash,
        'documento' => '741.353.586-67'
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'sucesso',
            'mensagem' => 'Senha do usuário atualizada com sucesso',
            'dados' => [
                'email' => 'lais.pires@exemplo.com',
                'senha' => '123456'
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Nenhum usuário foi atualizado'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar senha: " . $e->getMessage());
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao atualizar senha do usuário',
        'erro' => $e->getMessage()
    ]);
}