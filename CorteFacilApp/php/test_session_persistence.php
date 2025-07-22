<?php
header('Content-Type: application/json');
session_start();

// Log de debug
error_log('Session ID: ' . session_id());
error_log('Session Data: ' . json_encode($_SESSION));
error_log('Cookies: ' . json_encode($_COOKIE));

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Definido como 0 para funcionar em localhost sem HTTPS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_nome'] = 'Administrador';
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'admin';
    $_SESSION['user_name'] = 'Administrador';
    
    echo json_encode([
        'action' => 'set',
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'cookies' => $_COOKIE
    ]);
} else {
    echo json_encode([
        'action' => 'get',
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'cookies' => $_COOKIE,
        'headers' => getallheaders()
    ]);
}
?>