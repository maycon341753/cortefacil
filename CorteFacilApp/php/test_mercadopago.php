<?php
require_once 'mercadopago_config.php';

try {
    echo "Testando conexão com Mercado Pago...\n\n";
    
    // Teste básico de autenticação
    $response = mercadoPagoRequest('/v1/payment_methods');
    
    if (isset($response[0]['id'])) {
        echo "✅ Conexão com Mercado Pago estabelecida com sucesso!\n";
        echo "Métodos de pagamento disponíveis:\n";
        
        foreach (array_slice($response, 0, 5) as $method) {
            echo "- {$method['id']}: {$method['name']}\n";
        }
    } else {
        echo "❌ Erro na resposta da API\n";
        print_r($response);
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao conectar com Mercado Pago: " . $e->getMessage() . "\n";
}
?>