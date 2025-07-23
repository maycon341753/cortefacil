<?php
require_once 'CorteFacilApp/php/conexao.php';

try {
    $conn = getConexao();
    $stmt = $conn->query('DESCRIBE agendamentos');
    
    echo "<pre>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    // Também vamos verificar os dados de agendamentos
    $stmt2 = $conn->query('SELECT * FROM agendamentos WHERE cliente_id IN (SELECT id FROM usuarios WHERE nome LIKE "%Marcos Paulo dos Reis Borges%") LIMIT 5');
    
    echo "<h2>Agendamentos de Marcos Paulo:</h2><pre>";
    while($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>