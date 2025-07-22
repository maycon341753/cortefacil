<?php
require_once 'conexao.php';

header('Content-Type: text/plain');

try {
    // Verificar a estrutura da tabela profissionais
    $stmt = $conn->prepare("DESCRIBE profissionais");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Estrutura da tabela profissionais:\n";
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']})\n";
    }
    
} catch (PDOException $e) {
    echo "Erro ao verificar tabela: " . $e->getMessage();
}
?>