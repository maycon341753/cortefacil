<?php
require_once 'conexao.php';

try {
    $sql = "ALTER TABLE profissionais ADD COLUMN valor_servico DECIMAL(10,2) DEFAULT 0.00 AFTER especialidade";
    $conn->exec($sql);
    echo "Coluna valor_servico adicionada com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao adicionar coluna: " . $e->getMessage();
}
?>