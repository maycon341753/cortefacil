<?php
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

// Debug: verificar se a sessão está ativa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Usuário não autenticado',
        'debug' => [
            'session_data' => $_SESSION,
            'message' => 'Faça login primeiro para acessar o perfil'
        ]
    ]);
    exit;
}

try {
    $pdo = getConexao();
    
    // Debug: verificar se o ID da sessão é válido
    $session_id = $_SESSION['id'];
    if (!is_numeric($session_id) || $session_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'ID de usuário inválido na sessão',
            'debug' => [
                'session_id_value' => $session_id,
                'session_id_type' => gettype($session_id)
            ]
        ]);
        exit;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, nome, email, cpf, data_nascimento, tipo
        FROM usuarios
        WHERE id = ?
    ");
    
    $stmt->execute([$session_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'cpf' => $usuario['cpf'],
                'data_nascimento' => $usuario['data_nascimento'],
                'tipo' => $usuario['tipo']
            ]
        ]);
    } else {
        // Usuário não existe no banco - limpar sessão inválida
        session_destroy();
        session_start(); // Reinicia uma sessão limpa
        
        // Debug: verificar se existem usuários na tabela
        $stmt_count = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios");
        $stmt_count->execute();
        $total_usuarios = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Listar alguns usuários para debug (apenas IDs)
        $stmt_list = $pdo->prepare("SELECT id, nome FROM usuarios LIMIT 5");
        $stmt_list->execute();
        $usuarios_exemplo = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(401);
        echo json_encode([
            'error' => 'Sessão inválida - usuário não existe',
            'message' => 'A sessão foi limpa. Faça login novamente.',
            'action_required' => 'login',
            'debug' => [
                'previous_session_user_id' => $session_id,
                'total_usuarios_cadastrados' => $total_usuarios,
                'usuarios_exemplo' => $usuarios_exemplo,
                'session_cleared' => true
            ]
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro ao buscar dados do usuário',
        'debug' => [
            'pdo_error' => $e->getMessage(),
            'session_id' => $_SESSION['id'] ?? 'não definido'
        ]
    ]);
} 
?>