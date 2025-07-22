<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Buscar o usuário do salão existente
    $stmt = $conn->prepare("
        SELECT u.* 
        FROM usuarios u 
        JOIN saloes s ON s.usuario_id = u.id 
        WHERE s.documento = :documento
    ");
    
    $stmt->execute(['documento' => '741.353.586-67']);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo json_encode([
            'status' => 'sucesso',
            'mensagem' => 'Usuário encontrado',
            'dados' => [
                'id' => $usuario['id'],
                'email' => $usuario['email'],
                'tipo' => $usuario['tipo']
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Usuário não encontrado'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao buscar usuário: " . $e->getMessage());
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao buscar usuário',
        'erro' => $e->getMessage()
    ]);
}