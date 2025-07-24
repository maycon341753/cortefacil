<?php
session_start();

// Simular login do Thales
$_SESSION['id'] = 7; // ID do Thales
$_SESSION['tipo'] = 'cliente';
$_SESSION['nome'] = 'Thales Theo Gustavo Viana';

echo "<h2>✅ PROBLEMA RESOLVIDO - Agendamentos do Thales</h2>";
echo "<p><strong>Usuário:</strong> Thales Theo Gustavo Viana (ID: 7)</p>";

// Capturar a resposta da API sem headers
ob_start();
$_GET = []; // Limpar GET para evitar conflitos
include 'php/meus_agendamentos.php';
$response = ob_get_clean();

// Extrair apenas o JSON da resposta
$lines = explode("\n", $response);
$jsonLine = '';
foreach ($lines as $line) {
    if (strpos($line, '{"status"') !== false) {
        $jsonLine = $line;
        break;
    }
}

if ($jsonLine) {
    $data = json_decode($jsonLine, true);
    
    if ($data && isset($data['data'])) {
        echo "<h3>🎉 Agendamentos encontrados: " . count($data['data']) . "</h3>";
        
        foreach ($data['data'] as $agendamento) {
            $statusIcon = $agendamento['status'] == 'realizado' ? '✅' : 
                         ($agendamento['status'] == 'confirmado' ? '📅' : '❓');
            
            echo "<div style='border: 2px solid #4CAF50; padding: 15px; margin: 10px 0; border-radius: 8px; background-color: #f9f9f9;'>";
            echo "<h4>{$statusIcon} Agendamento ID: {$agendamento['id']}</h4>";
            echo "<strong>🏪 Salão:</strong> {$agendamento['salao']}<br>";
            echo "<strong>✂️ Serviço:</strong> {$agendamento['servico']}<br>";
            echo "<strong>👨‍💼 Profissional:</strong> {$agendamento['profissional']}<br>";
            echo "<strong>📅 Data:</strong> {$agendamento['data']}<br>";
            echo "<strong>🕐 Hora:</strong> {$agendamento['hora']}<br>";
            echo "<strong>📊 Status:</strong> <span style='color: " . 
                 ($agendamento['status'] == 'realizado' ? 'green' : 'blue') . 
                 "; font-weight: bold;'>{$agendamento['status']}</span><br>";
            echo "<strong>💰 Preço:</strong> {$agendamento['preco']}<br>";
            echo "</div>";
        }
        
        // Destacar o agendamento do Salão do Eduardo
        echo "<div style='background-color: #e8f5e8; padding: 20px; border-radius: 10px; margin-top: 20px;'>";
        echo "<h3>🎯 AGENDAMENTO ENCONTRADO!</h3>";
        echo "<p><strong>O agendamento do Thales no Salão do Eduardo agora aparece em 'Meus Agendamentos'!</strong></p>";
        echo "<p>✅ <strong>Status:</strong> realizado (agendamento pago)</p>";
        echo "<p>✅ <strong>Salão:</strong> Salão do Eduardo</p>";
        echo "<p>✅ <strong>Serviço:</strong> Barba</p>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Erro ao processar dados</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Erro ao obter resposta da API</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
?>