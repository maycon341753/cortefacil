<?php
session_start();

// Simular login do Thales
$_SESSION['id'] = 7; // ID do Thales
$_SESSION['tipo'] = 'cliente';
$_SESSION['nome'] = 'Thales Theo Gustavo Viana';

echo "<h2>Teste - Meus Agendamentos do Thales</h2>";
echo "<p>Simulando sessão do Thales (ID: 7)</p>";

// Incluir o arquivo de meus agendamentos
ob_start();
include 'php/meus_agendamentos.php';
$response = ob_get_clean();

echo "<h3>Resposta da API:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Decodificar JSON para análise
$data = json_decode($response, true);

if ($data && isset($data['data'])) {
    echo "<h3>Agendamentos encontrados: " . count($data['data']) . "</h3>";
    
    foreach ($data['data'] as $agendamento) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
        echo "<strong>ID:</strong> {$agendamento['id']}<br>";
        echo "<strong>Salão:</strong> {$agendamento['salao']}<br>";
        echo "<strong>Serviço:</strong> {$agendamento['servico']}<br>";
        echo "<strong>Profissional:</strong> {$agendamento['profissional']}<br>";
        echo "<strong>Data:</strong> {$agendamento['data']}<br>";
        echo "<strong>Hora:</strong> {$agendamento['hora']}<br>";
        echo "<strong>Status:</strong> {$agendamento['status']}<br>";
        echo "<strong>Preço:</strong> {$agendamento['preco']}<br>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>Erro ao decodificar resposta ou nenhum agendamento encontrado</p>";
}
?>