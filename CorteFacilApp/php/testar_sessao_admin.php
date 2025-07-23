<?php
session_start();

header('Content-Type: application/json');

// Definir manualmente as variáveis de sessão para simular um login de administrador
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'admin';
$_SESSION['user_name'] = 'Administrador';
$_SESSION['admin_id'] = 1;
$_SESSION['admin_nome'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['tipo'] = 'admin';
$_SESSION['nome'] = 'Administrador';

echo json_encode([
    'status' => 'ok',
    'mensagem' => 'Sessão de administrador criada com sucesso',
    'session_id' => session_id(),
    'session_data' => $_SESSION
]);
?>