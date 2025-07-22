<?php
require 'conexao.php';

// Verificar a estrutura da tabela
$stmt = $conn->query('DESCRIBE saloes');
echo "\nEstrutura da tabela saloes:\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

// Verificar os registros
$stmt = $conn->query('SELECT * FROM saloes');
echo "\nRegistros da tabela saloes:\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>