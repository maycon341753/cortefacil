<?php
session_start();
header('Content-Type: application/json');

// Simular login do administrador
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'admin';
$_SESSION['user_name'] = 'Administrador';

// Dados para atualização
$dados = [
    'nome' => 'Admin Teste',
    'email' => 'admin@teste.com'
];

// Fazer requisição para admin_atualizar_perfil.php
$ch = curl_init('http://localhost/cortefacil/php/admin_atualizar_perfil.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

echo json_encode([
    'status' => 'ok',
    'session_data' => $_SESSION,
    'request_data' => $dados,
    'response' => $response ? json_decode($response, true) : null,
    'error' => $error
]);
?>