<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Verificar se as colunas especialidade e valor_servico existem
    $stmt = $conn->prepare("SHOW COLUMNS FROM funcionarios LIKE 'especialidade'");
    $stmt->execute();
    $especialidadeExists = $stmt->rowCount() > 0;
    
    $stmt = $conn->prepare("SHOW COLUMNS FROM funcionarios LIKE 'valor_servico'");
    $stmt->execute();
    $valorServicoExists = $stmt->rowCount() > 0;
    
    // Adicionar coluna especialidade se não existir
    if (!$especialidadeExists) {
        $sql = "ALTER TABLE funcionarios ADD COLUMN especialidade VARCHAR(100) DEFAULT NULL AFTER salao_id";
        $conn->exec($sql);
        echo "Coluna 'especialidade' adicionada com sucesso!\n";
    } else {
        echo "Coluna 'especialidade' já existe.\n";
    }
    
    // Adicionar coluna valor_servico se não existir
    if (!$valorServicoExists) {
        $sql = "ALTER TABLE funcionarios ADD COLUMN valor_servico DECIMAL(10,2) DEFAULT 0.00 AFTER especialidade";
        $conn->exec($sql);
        echo "Coluna 'valor_servico' adicionada com sucesso!\n";
    } else {
        echo "Coluna 'valor_servico' já existe.\n";
    }
    
    // Verificar a estrutura final da tabela
    $stmt = $conn->prepare("DESCRIBE funcionarios");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nEstrutura final da tabela funcionários:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>