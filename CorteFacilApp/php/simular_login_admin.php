<?php
session_start();
header('Content-Type: application/json');

// Simular login do administrador
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'admin';
$_SESSION['user_name'] = 'Administrador';

// Manter compatibilidade com código existente
$_SESSION['admin_id'] = 1;
$_SESSION['admin_nome'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['tipo'] = 'admin';
$_SESSION['nome'] = 'Administrador';

echo json_encode([
    'status' => 'ok',
    'message' => 'Login simulado com sucesso',
    'session_data' => $_SESSION
]);
?>