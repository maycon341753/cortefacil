<?php
require_once 'conexao.php';

echo "<h1>✅ Sistema Corrigido - Agendamento vs Faturamento</h1>";

echo "<h2>📋 Como funciona agora:</h2>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>💰 Pagamento do Cliente:</h3>";
echo "<ul>";
echo "<li><strong>Taxa de agendamento:</strong> R$ 0,99 (valor fixo que o cliente paga via PIX)</li>";
echo "<li><strong>Finalidade:</strong> Garantir o agendamento e evitar faltas</li>";
echo "<li><strong>Pagamento do serviço:</strong> Será feito diretamente no salão</li>";
echo "</ul>";

echo "<h3>📊 Faturamento do Parceiro:</h3>";
echo "<ul>";
echo "<li><strong>Valor exibido:</strong> Preço real do serviço (ex: R$ 30,00 para um corte)</li>";
echo "<li><strong>Finalidade:</strong> Mostrar o potencial de faturamento dos agendamentos</li>";
echo "<li><strong>Observação:</strong> Representa o valor que será recebido quando o cliente for ao salão</li>";
echo "</ul>";
echo "</div>";

try {
    $conn = getConexao();
    
    echo "<h2>🧪 Demonstração Prática:</h2>";
    
    // Buscar um serviço para exemplo
    $stmt = $conn->query("SELECT id, nome, preco FROM servicos LIMIT 1");
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($servico) {
        echo "<h3>Exemplo com o serviço: {$servico['nome']}</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>Aspecto</th><th>Valor</th><th>Explicação</th>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Taxa de Agendamento</strong></td>";
        echo "<td style='color: blue;'><strong>R$ 0,99</strong></td>";
        echo "<td>Valor que o cliente paga via PIX para garantir o agendamento</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Preço do Serviço</strong></td>";
        echo "<td style='color: green;'><strong>R$ " . number_format($servico['preco'], 2, ',', '.') . "</strong></td>";
        echo "<td>Valor que o cliente pagará no salão pelo serviço</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Faturamento no Dashboard</strong></td>";
        echo "<td style='color: green; background-color: #e8f5e8;'><strong>R$ " . number_format($servico['preco'], 2, ',', '.') . "</strong></td>";
        echo "<td>Valor exibido no dashboard do parceiro (potencial de faturamento)</td>";
        echo "</tr>";
        
        echo "</table>";
    }
    
    echo "<h2>🔄 Fluxo Completo:</h2>";
    echo "<ol>";
    echo "<li><strong>Cliente agenda:</strong> Paga R$ 0,99 via PIX</li>";
    echo "<li><strong>Agendamento confirmado:</strong> Status muda para 'realizado'</li>";
    echo "<li><strong>Dashboard do parceiro:</strong> Mostra o valor real do serviço no faturamento</li>";
    echo "<li><strong>Cliente vai ao salão:</strong> Paga o valor real do serviço</li>";
    echo "</ol>";
    
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✅ Resultado:</h3>";
    echo "<ul style='color: #155724; margin-bottom: 0;'>";
    echo "<li>Cliente paga apenas R$ 0,99 para agendar</li>";
    echo "<li>Parceiro vê o faturamento potencial real dos serviços</li>";
    echo "<li>Sistema diferencia taxa de agendamento do valor do serviço</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Sistema ajustado conforme solicitado! 🎉</em></p>";
?>