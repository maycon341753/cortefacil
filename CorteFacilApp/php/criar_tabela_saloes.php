<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Verifica se a tabela saloes existe
    $result = $conn->query("SHOW TABLES LIKE 'saloes'");
    if ($result->rowCount() == 0) {
        // Cria a tabela se não existir
        $sql = "CREATE TABLE saloes (
            id INT(11) NOT NULL AUTO_INCREMENT,
            usuario_id INT(11) NOT NULL,
            nome_fantasia VARCHAR(100) NOT NULL,
            cnpj VARCHAR(18) DEFAULT NULL,
            telefone VARCHAR(15) DEFAULT NULL,
            endereco VARCHAR(255) DEFAULT NULL,
            ativo TINYINT(1) DEFAULT 1,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        
        $conn->exec($sql);
        echo "Tabela saloes criada com sucesso!";
    } else {
        echo "A tabela saloes já existe.";
    }
    
} catch (PDOException $e) {
    die("Erro ao criar tabela saloes: " . $e->getMessage());
}
?>