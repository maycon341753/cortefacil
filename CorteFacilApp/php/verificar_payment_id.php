<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    echo "<h2>Verificando estrutura da tabela agendamentos</h2>";
    
    // Verificar se a coluna payment_id existe
    $stmt = $conn->query("SHOW COLUMNS FROM agendamentos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Colunas existentes:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    $payment_id_exists = false;
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'payment_id') {
            $payment_id_exists = true;
        }
    }
    echo "</table>";
    
    if ($payment_id_exists) {
        echo "<p style='color: green;'><strong>✅ Coluna payment_id existe!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Coluna payment_id NÃO existe!</strong></p>";
        echo "<p>Tentando adicionar a coluna...</p>";
        
        try {
            $sql = "ALTER TABLE agendamentos ADD COLUMN payment_id VARCHAR(255) NULL AFTER transaction_id";
            $conn->exec($sql);
            echo "<p style='color: green;'><strong>✅ Coluna payment_id adicionada com sucesso!</strong></p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>❌ Erro ao adicionar coluna: " . $e->getMessage() . "</strong></p>";
        }
    }
    
    // Verificar novamente após tentativa de adição
    echo "<h3>Verificação final:</h3>";
    $stmt = $conn->query("SHOW COLUMNS FROM agendamentos LIKE 'payment_id'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<p style='color: green;'><strong>✅ Coluna payment_id confirmada!</strong></p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p style='color: red;'><strong>❌ Coluna payment_id ainda não existe!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Erro: " . $e->getMessage() . "</strong></p>";
}
?>