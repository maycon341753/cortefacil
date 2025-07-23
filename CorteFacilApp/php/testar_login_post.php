<?php
// Script para testar o login do administrador enviando uma requisição POST

// Configurações da requisição
$url = 'http://localhost:8000/CorteFacilApp/php/admin_login.php';
$data = [
    'email' => 'admin@cortefacil.com',
    'senha' => 'admin123'
];

// Inicializa cURL
$ch = curl_init($url);

// Configura a requisição POST
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Origin: http://localhost:8000'
]);

// Executa a requisição
$response = curl_exec($ch);
$info = curl_getinfo($ch);

// Separa o cabeçalho do corpo da resposta
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

// Fecha a conexão cURL
curl_close($ch);

// Exibe informações da resposta
header('Content-Type: application/json');
echo json_encode([
    'status_code' => $info['http_code'],
    'headers' => $header,
    'response' => json_decode($body),
    'curl_info' => $info
], JSON_PRETTY_PRINT);
?>