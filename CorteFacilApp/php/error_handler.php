<?php
header('Content-Type: application/json');

if (isset($_GET['error']) && $_GET['error'] === 'invalid_json') {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Erro ao carregar agendamentos. Por favor, tente novamente.',
        'redirect' => '../cliente/painel.php'
    ));
    exit;
}

header('Location: ../cliente/painel.php');
exit;