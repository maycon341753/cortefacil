<?php
require_once 'conexao.php';

echo "<h2>Verificação da Estrutura de Agendamentos - Valores</h2>";

try {
    // Verificar estrutura da tabela agendamentos
    echo "<h3>Estrutura da tabela agendamentos:</h3>";
    $stmt = $conn->query("DESCRIBE agendamentos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar alguns agendamentos com seus valores
    echo "<h3>Agendamentos com valores:</h3>";
    $stmt = $conn->query("
        SELECT 
            a.id,
            a.data,
            a.hora,
            a.status,
            a.taxa_servico,
            a.valor_servico,
            s.nome as servico_nome,
            s.preco as servico_preco,
            sal.nome_fantasia as salao_nome
        FROM agendamentos a
        LEFT JOIN servicos s ON s.id = a.servico_id
        LEFT JOIN saloes sal ON sal.id = a.salao_id
        ORDER BY a.data DESC
        LIMIT 10
    ");
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($agendamentos)) {
        echo "<p>Nenhum agendamento encontrado.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Data</th><th>Hora</th><th>Status</th><th>Taxa Serviço</th><th>Valor Serviço</th><th>Serviço</th><th>Preço Serviço</th><th>Salão</th></tr>";
        foreach ($agendamentos as $agendamento) {
            echo "<tr>";
            echo "<td>{$agendamento['id']}</td>";
            echo "<td>{$agendamento['data']}</td>";
            echo "<td>{$agendamento['hora']}</td>";
            echo "<td>{$agendamento['status']}</td>";
            echo "<td>R$ " . number_format($agendamento['taxa_servico'] ?? 0, 2, ',', '.') . "</td>";
            echo "<td>R$ " . number_format($agendamento['valor_servico'] ?? 0, 2, ',', '.') . "</td>";
            echo "<td>{$agendamento['servico_nome']}</td>";
            echo "<td>R$ " . number_format($agendamento['servico_preco'] ?? 0, 2, ',', '.') . "</td>";
            echo "<td>{$agendamento['salao_nome']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar se há diferença entre os valores
    echo "<h3>Análise dos valores:</h3>";
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total_agendamentos,
            SUM(CASE WHEN taxa_servico != s.preco THEN 1 ELSE 0 END) as diferenca_taxa_preco,
            SUM(CASE WHEN valor_servico != s.preco THEN 1 ELSE 0 END) as diferenca_valor_preco,
            AVG(taxa_servico) as media_taxa,
            AVG(s.preco) as media_preco_servico
        FROM agendamentos a
        LEFT JOIN servicos s ON s.id = a.servico_id
    ");
    $analise = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    echo "<li><strong>Total de agendamentos:</strong> {$analise['total_agendamentos']}</li>";
    echo "<li><strong>Agendamentos com taxa diferente do preço do serviço:</strong> {$analise['diferenca_taxa_preco']}</li>";
    echo "<li><strong>Agendamentos com valor_servico diferente do preço do serviço:</strong> {$analise['diferenca_valor_preco']}</li>";
    echo "<li><strong>Média da taxa de serviço:</strong> R$ " . number_format($analise['media_taxa'] ?? 0, 2, ',', '.') . "</li>";
    echo "<li><strong>Média do preço dos serviços:</strong> R$ " . number_format($analise['media_preco_servico'] ?? 0, 2, ',', '.') . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>