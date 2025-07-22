<?php
// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Definido como 0 para funcionar em localhost sem HTTPS

session_start();

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Log de debug
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Session ID: ' . session_id());
error_log('Session Data: ' . json_encode($_SESSION));
error_log('Cookies: ' . json_encode($_COOKIE));
error_log('Headers: ' . json_encode(getallheaders()));

include 'conexao.php';

// Recebe os dados do formulário ou JSON
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $usuario = $input['email'] ?? '';
    $senha = $input['senha'] ?? '';
} else {
    $usuario = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
}

// Log para debug
error_log("Tentativa de login - Usuario: " . $usuario);

try {
    // Verifica se o usuário existe na tabela usuarios com tipo 'admin'
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email AND tipo = 'admin'");
    $stmt->execute([':email' => $usuario]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log para debug
    error_log("Resultado da consulta: " . ($admin ? "Usuario encontrado" : "Usuario nao encontrado"));
    
    if ($admin) {
        error_log("Senha fornecida: " . $senha);
        error_log("Hash armazenado: " . $admin['senha']);
        
        if (password_verify($senha, $admin['senha'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_name'] = $admin['nome'];
            
            // Mantendo compatibilidade com código existente
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            
            error_log("Login bem sucedido para: " . $admin['nome']);
            echo json_encode([
                'status' => 'ok',
                'user' => [
                    'id' => $admin['id'],
                    'nome' => $admin['nome']
                ]
            ]);
        } else {
            error_log("Senha invalida");
            echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário ou senha inválidos']);
        }
    } else {
        error_log("Usuario nao encontrado no banco");
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário ou senha inválidos']);
    }

} catch (Exception $e) {
    error_log("Erro no login: " . $e->getMessage());
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao realizar login']);
}
?>
