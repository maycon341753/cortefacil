<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexao.php';

try {
    $conn = getConexao();
    
    // Lista os salões disponíveis
    $stmt = $conn->query("SELECT id, nome_fantasia FROM saloes");
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Salões Disponíveis</h1>";
    foreach ($saloes as $salao) {
        echo "ID: {$salao['id']} - Nome: {$salao['nome_fantasia']}<br>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>