<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    // Criar tabela de log de atividades
    $sql = "CREATE TABLE IF NOT EXISTS log_atividades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        acao VARCHAR(50) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    
    // Criar tabela de tentativas de login
    $sql = "CREATE TABLE IF NOT EXISTS log_tentativas_login (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cpf VARCHAR(11) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        data_hora TIMESTAMP NOT NULL,
        INDEX idx_cpf_ip (cpf, ip)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    
    // Adicionar coluna 'ativo' na tabela usuarios se não existir
    $sql = "SHOW COLUMNS FROM usuarios LIKE 'ativo'";
    $result = $pdo->query($sql);
    
    if ($result->rowCount() == 0) {
        $sql = "ALTER TABLE usuarios ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1;";
        $pdo->exec($sql);
    }
    
    echo json_encode(['status' => 'success', 'mensagem' => 'Tabelas criadas com sucesso']);
    
} catch (PDOException $e) {
    error_log('Erro ao criar tabelas: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Erro ao criar tabelas: ' . $e->getMessage()
    ]);
}
?>