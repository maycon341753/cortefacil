<?php
include 'conexao.php';

try {
    // Verificar se a tabela saloes existe
    $query = "SHOW TABLES LIKE 'saloes'";
    $stmt = $conn->query($query);
    $tableExists = $stmt->rowCount() > 0;
    
    $result = [
        'table_exists' => $tableExists
    ];
    
    if ($tableExists) {
        // Verificar a estrutura da tabela
        $query = "DESCRIBE saloes";
        $stmt = $conn->query($query);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result['table_structure'] = $columns;
        
        // Contar registros
        $query = "SELECT COUNT(*) as total FROM saloes";
        $stmt = $conn->query($query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $result['record_count'] = $row['total'];
        
        // Listar alguns registros
        if ($row['total'] > 0) {
            $query = "SELECT * FROM saloes LIMIT 5";
            $stmt = $conn->query($query);
            $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result['sample_records'] = $saloes;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'erro', 
        'mensagem' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>