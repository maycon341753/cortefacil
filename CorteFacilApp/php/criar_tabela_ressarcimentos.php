<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    // Verifica se a tabela ressarcimentos já existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'ressarcimentos'");
    if ($stmt->rowCount() == 0) {
        // Cria a tabela ressarcimentos
        $sql = "CREATE TABLE ressarcimentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cupom_id INT NOT NULL,
            salao_id INT NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            data_ressarcimento DATETIME NOT NULL,
            data_pagamento DATETIME NULL,
            status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
            comprovante_url VARCHAR(255) NULL,
            observacoes TEXT NULL,
            FOREIGN KEY (cupom_id) REFERENCES cupons(id),
            FOREIGN KEY (salao_id) REFERENCES saloes(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Tabela ressarcimentos criada com sucesso']);
    } else {
        echo json_encode(['status' => 'info', 'mensagem' => 'Tabela ressarcimentos já existe']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao criar tabela ressarcimentos: ' . $e->getMessage()]);
}