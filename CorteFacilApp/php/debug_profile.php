<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getConexao();
    
    // Debug da sessão
    $debug_info = [
        'session_status' => session_status(),
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'session_isset_id' => isset($_SESSION['id']),
        'session_id_value' => $_SESSION['id'] ?? 'não definido'
    ];
    
    // Se há ID na sessão, verificar se o usuário existe
    if (isset($_SESSION['id'])) {
        $stmt = $pdo->prepare("SELECT id, nome, email, cpf, tipo FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $debug_info['usuario_encontrado'] = $usuario ? 'sim' : 'não';
        $debug_info['dados_usuario'] = $usuario;
        
        // Listar todos os usuários para verificar
        $stmt = $pdo->prepare("SELECT id, nome, email, tipo FROM usuarios LIMIT 10");
        $stmt->execute();
        $todos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $debug_info['todos_usuarios'] = $todos_usuarios;
    }
    
    // Verificar estrutura da tabela usuarios
    $stmt = $pdo->prepare("DESCRIBE usuarios");
    $stmt->execute();
    $estrutura_tabela = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug_info['estrutura_tabela_usuarios'] = $estrutura_tabela;
    
    echo json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Erro no debug: ' . $e->getMessage(),
        'session_data' => $_SESSION ?? 'sessão não disponível'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>