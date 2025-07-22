<?php
// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de sessão para maior segurança
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);

    // Removendo configurações que podem causar problemas em ambiente de desenvolvimento
    // ini_set('session.cookie_secure', 1);
    // ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

// Define headers de segurança e cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

// Se for uma chamada AJAX, retorna JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json; charset=utf-8');
    // Verifica se a sessão está válida e completa
    if (isset($_SESSION['id']) && isset($_SESSION['tipo']) && !empty($_SESSION['tipo']) && isset($_SESSION['nome'])) {
        echo json_encode([
            'status' => 'success',
            'tipo' => $_SESSION['tipo'],
            'id' => $_SESSION['id'],
            'nome' => $_SESSION['nome']
        ]);
    } else {
        session_destroy(); // Destrói a sessão se estiver inválida
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'mensagem' => 'Usuário não autenticado'
        ]);
    }
    exit;
}

// Se não estiver autenticado, redireciona para a página de login apropriada
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo'])) {
    if (strpos($_SERVER['REQUEST_URI'], '/cliente/') !== false) {
        header('Location: /CorteFacil/index.html');
    } else if (strpos($_SERVER['REQUEST_URI'], '/salao/') !== false) {
        header('Location: /CorteFacil/parceiro_login.html');
    } else if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        header('Location: /CorteFacil/admin_login.html');
    } else {
        header('Location: /CorteFacil/index.html');
    }
    exit;
}
?>