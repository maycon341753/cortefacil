<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    // Verifica se a tabela cupons já existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'cupons'");
    if ($stmt->rowCount() == 0) {
        // Cria a tabela cupons
        $sql = "CREATE TABLE cupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(20) NOT NULL UNIQUE,
            salao_id INT NOT NULL,
            status ENUM('disponivel', 'utilizado', 'expirado') DEFAULT 'disponivel',
            data_geracao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_utilizacao DATETIME NULL,
            data_expiracao DATE NOT NULL,
            cliente_id INT NULL,
            valor_ressarcimento DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (salao_id) REFERENCES saloes(id),
            FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Tabela cupons criada com sucesso']);
    } else {
        echo json_encode(['status' => 'info', 'mensagem' => 'Tabela cupons já existe']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao criar tabela cupons: ' . $e->getMessage()]);
}