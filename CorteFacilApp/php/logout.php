<?php
session_start();
session_destroy();
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'mensagem' => 'Logout realizado com sucesso']);
?>