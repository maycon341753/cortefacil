<?php
// Configurações de sessão para maior segurança
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Desativado para desenvolvimento local
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexao.php';

// Define headers de segurança e tipo de conteúdo
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
header('Access-Control-Allow-Origin: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'));

// Verifica se é uma requisição AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'mensagem' => 'Acesso não permitido']);
    exit;
}

try {
    // Receber e decodificar dados JSON
    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao processar dados do formulário');
    }
    
    // Limpa e valida os dados de entrada
    $cpf = filter_var($dados['cpf'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = filter_var($dados['senha'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($cpf) || empty($senha)) {
        throw new Exception('CPF e senha são obrigatórios');
    }
    
    // Remove caracteres especiais do CPF
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Valida o formato do CPF
    if (strlen($cpf) !== 11) {
        throw new Exception('CPF inválido');
    }
    
    $pdo = getConexao();
    
    // Busca o usuário pelo CPF
    $sql = "SELECT id, nome, email, senha, tipo FROM usuarios WHERE cpf = ? AND ativo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cpf]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Verifica se é um cliente
        if ($usuario['tipo'] !== 'cliente') {
            throw new Exception('Acesso não autorizado para este tipo de usuário');
        }
        
        // Regenera o ID da sessão por segurança
        session_regenerate_id(true);
        
        // Armazena os dados do usuário na sessão
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['tipo'] = $usuario['tipo'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['last_activity'] = time();
        
        // Registra o login no log de atividades
        $sql = "INSERT INTO log_atividades (usuario_id, acao, ip) VALUES (?, 'login', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $usuario['id'],
            $_SERVER['REMOTE_ADDR']
        ]);
        
        echo json_encode([
            'status' => 'success',
            'tipo' => $usuario['tipo'],
            'mensagem' => 'Login realizado com sucesso'
        ]);
    } else {
        // Registra tentativa de login falha
        $sql = "INSERT INTO log_tentativas_login (cpf, ip, data_hora) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cpf, $_SERVER['REMOTE_ADDR']]);
        
        throw new Exception('CPF ou senha inválidos');
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'mensagem' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log('Erro no login: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Erro interno do servidor. Por favor, tente novamente mais tarde.'
    ]);
}
?>
