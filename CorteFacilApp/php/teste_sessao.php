<?php
session_start();
header('Content-Type: application/json');

// Exibir todas as variáveis de sessão
echo json_encode([
    'session_data' => $_SESSION,
    'session_id' => session_id(),
    'session_status' => session_status()
]);
?>