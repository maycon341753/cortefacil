<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Verificar se a coluna payment_id já existe
    $stmt = $conn->query("SHOW COLUMNS FROM agendamentos LIKE 'payment_id'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Adicionar coluna payment_id
        $sql = "ALTER TABLE agendamentos ADD COLUMN payment_id VARCHAR(255) NULL AFTER id";
        $conn->exec($sql);
        echo "Coluna payment_id adicionada com sucesso!\n";
    } else {
        echo "Coluna payment_id já existe.\n";
    }
    
    echo "Estrutura da tabela atualizada com sucesso!";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>