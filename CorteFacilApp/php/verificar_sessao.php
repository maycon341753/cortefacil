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

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo'])) {
    header('Location: ../index.html');
    exit;
}

if ($_SESSION['tipo'] !== 'cliente' && $_SESSION['tipo'] !== 'admin') {
    header('Location: ../index.html');
    exit;
}
?>