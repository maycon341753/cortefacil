<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado']);
    exit;
}

try {
    require_once 'conexao.php';
    
    $stmt = $conn->prepare("SELECT id, nome, email FROM usuarios WHERE id = :id AND tipo = 'admin'");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo json_encode([
            'status' => 'ok',
            'admin' => [
                'id' => $admin['id'],
                'nome' => $admin['nome'],
                'email' => $admin['email']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Administrador não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro interno do servidor']);
}
?>