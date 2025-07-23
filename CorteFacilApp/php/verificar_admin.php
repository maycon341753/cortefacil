<?php
// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de sessão para maior segurança
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    
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
    // Verifica se a sessão está válida e completa para um administrador
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
        echo json_encode([
            'status' => 'success',
            'tipo' => $_SESSION['user_type'],
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['user_name'] ?? 'Administrador'
        ]);
    } else {
        session_destroy(); // Destrói a sessão se estiver inválida
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'mensagem' => 'Administrador não autenticado'
        ]);
    }
    exit;
}

// Se não estiver autenticado como administrador, redireciona para a página de login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // Registra tentativa de acesso não autorizado
    error_log('Tentativa de acesso não autorizado à área administrativa: ' . $_SERVER['REQUEST_URI']);
    
    // Redireciona para a página de login do administrador
    header('Location: /CorteFacilApp/admin_login.html');
    exit;
}
?>