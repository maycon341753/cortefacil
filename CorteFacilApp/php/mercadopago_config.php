<?php
// Configuração do Mercado Pago
define('MERCADOPAGO_ACCESS_TOKEN', 'APP_USR-975584341823172-071915-00f48f8c37f8a370020c33087c915415-1141410279');
define('MERCADOPAGO_API_URL', 'https://api.mercadopago.com');

// Função para fazer requisições à API do Mercado Pago
function mercadoPagoRequest($endpoint, $method = 'GET', $data = null) {
    $url = MERCADOPAGO_API_URL . $endpoint;
    
    $headers = [
        'Authorization: Bearer ' . MERCADOPAGO_ACCESS_TOKEN,
        'Content-Type: application/json',
        'X-Idempotency-Key: ' . uniqid()
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('Erro na requisição cURL: ' . $error);
    }
    
    $decodedResponse = json_decode($response, true);
    
    if ($httpCode >= 400) {
        $errorMessage = isset($decodedResponse['message']) ? $decodedResponse['message'] : 'Erro na API do Mercado Pago';
        throw new Exception($errorMessage . ' (HTTP ' . $httpCode . ')');
    }
    
    return $decodedResponse;
}
?>