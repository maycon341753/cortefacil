<?php
session_start();

header('Content-Type: application/json');

$session_data = array(
    'session_id' => session_id(),
    'admin_id' => isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null,
    'admin_nome' => isset($_SESSION['admin_nome']) ? $_SESSION['admin_nome'] : null,
    'session_data' => $_SESSION
);

echo json_encode($session_data);
?>