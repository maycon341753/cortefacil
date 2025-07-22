<?php
session_start();

// Simula login de admin para teste
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_nome'] = 'Administrador';
    $_SESSION['admin_email'] = 'admin@cortefacil.com';
}

echo json_encode([
    'status' => 'ok',
    'admin_id' => $_SESSION['admin_id'],
    'admin_nome' => $_SESSION['admin_nome']
]);
?>