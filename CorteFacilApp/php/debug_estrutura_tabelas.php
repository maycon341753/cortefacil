<?php
// Debug da estrutura das tabelas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug da estrutura das tabelas</h2>";

try {
    include 'conexao.php';
    
    echo "<h3>1. Estrutura da tabela saloes</h3>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>2. Dados do salão ID 4</h3>";
    $stmt = $conn->prepare("SELECT * FROM saloes WHERE id = 4");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao) {
        echo "✅ Salão encontrado:<br>";
        echo "<pre>";
        print_r($salao);
        echo "</pre>";
    } else {
        echo "❌ Salão ID 4 não encontrado<br>";
    }
    
    echo "<h3>3. Estrutura da tabela ciclos_metas</h3>";
    $stmt = $conn->prepare("DESCRIBE ciclos_metas");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>4. Ciclos existentes para o salão ID 4</h3>";
    $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE salao_id = 4 ORDER BY data_inicio DESC");
    $stmt->execute();
    $ciclos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($ciclos) {
        echo "Ciclos encontrados: " . count($ciclos) . "<br>";
        foreach ($ciclos as $ciclo) {
            echo "<pre>";
            print_r($ciclo);
            echo "</pre>";
        }
    } else {
        echo "❌ Nenhum ciclo encontrado para o salão ID 4<br>";
    }
    
    echo "<h3>5. Estrutura da tabela agendamentos</h3>";
    $stmt = $conn->prepare("DESCRIBE agendamentos");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>6. Agendamentos do salão ID 4</h3>";
    $stmt = $conn->prepare("SELECT id, status, data, valor FROM agendamentos WHERE salao_id = 4 ORDER BY data DESC LIMIT 10");
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($agendamentos) {
        echo "Últimos 10 agendamentos:<br>";
        foreach ($agendamentos as $agendamento) {
            echo "<pre>";
            print_r($agendamento);
            echo "</pre>";
        }
    } else {
        echo "❌ Nenhum agendamento encontrado para o salão ID 4<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ ERRO CAPTURADO:</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}
?>