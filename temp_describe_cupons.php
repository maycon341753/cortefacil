<?php
require_once 'CorteFacilApp/php/conexao.php';

try {
    $pdo = getConexao();
    $stmt = $pdo->query('DESCRIBE promocoes');
    echo "<pre>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>