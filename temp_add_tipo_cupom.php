<?php
require_once 'CorteFacilApp/php/conexao.php';

try {
    $pdo = getConexao();
    
    // Verifica se a coluna tipo_cupom já existe na tabela cupons
    $stmt = $pdo->query("SHOW COLUMNS FROM cupons LIKE 'tipo_cupom'");
    if ($stmt->rowCount() == 0) {
        // Adiciona a coluna tipo_cupom
        $sql = "ALTER TABLE cupons ADD COLUMN tipo_cupom ENUM('normal', 'corte_gratis') DEFAULT 'normal' AFTER salao_id";
        $pdo->exec($sql);
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Coluna tipo_cupom adicionada com sucesso']);
    } else {
        echo json_encode(['status' => 'info', 'mensagem' => 'Coluna tipo_cupom já existe']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao adicionar coluna tipo_cupom: ' . $e->getMessage()]);
}
?>