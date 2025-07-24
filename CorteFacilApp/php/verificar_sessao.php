<?php
// Configurações de sessão para maior segurança
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Desativado para desenvolvimento local
ini_set('session.cookie_samesite', 'Lax'); // Alterado para Lax para desenvolvimento local

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verifica se é uma chamada AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Se for uma chamada AJAX, retorna JSON
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    
    if (isset($_SESSION['id']) && isset($_SESSION['tipo']) && isset($_SESSION['nome'])) {
        echo json_encode([
            'logado' => true,
            'tipo' => $_SESSION['tipo'],
            'id' => $_SESSION['id'],
            'nome' => $_SESSION['nome']
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'logado' => false,
            'mensagem' => 'Usuário não autenticado'
        ]);
    }
    exit;
}

// Para includes PHP, verifica a sessão normalmente
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo'])) {
    header('Location: ../index.html');
    exit;
}

// Verifica se o tipo de usuário é válido (removendo a restrição muito específica)
if (!in_array($_SESSION['tipo'], ['cliente', 'admin', 'salao'])) {
    header('Location: ../index.html');
    exit;
}
?>