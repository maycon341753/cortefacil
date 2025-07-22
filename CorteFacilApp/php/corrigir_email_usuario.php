<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Atualizar o email do usuário para um formato válido
    $stmt = $conn->prepare("
        UPDATE usuarios u
        JOIN saloes s ON s.usuario_id = u.id
        SET u.email = 'lais.pires@exemplo.com'
        WHERE s.documento = :documento
    ");
    
    $stmt->execute(['documento' => '741.353.586-67']);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'sucesso',
            'mensagem' => 'Email do usuário atualizado com sucesso'
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Nenhum usuário foi atualizado'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar email: " . $e->getMessage());
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao atualizar email do usuário',
        'erro' => $e->getMessage()
    ]);
}