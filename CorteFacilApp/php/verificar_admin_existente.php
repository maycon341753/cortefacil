<?php
include 'conexao.php';

header('Content-Type: application/json');

try {
    // Verifica se existem usuários administradores no banco
    $stmt = $conn->prepare("SELECT id, nome, email, tipo FROM usuarios WHERE tipo = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'ok',
        'total_admins' => count($admins),
        'admins' => $admins
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao verificar administradores: ' . $e->getMessage()
    ]);
}
?>