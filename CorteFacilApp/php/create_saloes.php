<?php
require_once 'conexao.php';

try {
    // Verificar se a tabela saloes existe
    $checkTable = $conn->query("SHOW TABLES LIKE 'saloes'")->fetchColumn();
    
    if ($checkTable == 0) {
        // Criar a tabela saloes
        $sql = "CREATE TABLE IF NOT EXISTS saloes (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nome_fantasia VARCHAR(100) NOT NULL,
            documento VARCHAR(18) NOT NULL,
            cidade VARCHAR(100) NOT NULL,
            endereco VARCHAR(255) NOT NULL,
            whatsapp VARCHAR(20) NOT NULL,
            num_funcionarios INT NOT NULL DEFAULT 1,
            media_diaria INT NOT NULL DEFAULT 0,
            media_semanal INT NOT NULL DEFAULT 0,
            pix_chave VARCHAR(255) DEFAULT NULL,
            usuario_id INT(11) NOT NULL,
            ativo BOOLEAN DEFAULT 1,
            horario_abertura TIME NOT NULL DEFAULT '09:00',
            horario_fechamento TIME NOT NULL DEFAULT '18:00',
            intervalo_agendamento INT NOT NULL DEFAULT 30,
            dias_funcionamento VARCHAR(20) DEFAULT '1,2,3,4,5,6',
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY documento (documento),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $conn->exec($sql);
        echo "Tabela saloes criada com sucesso!<br>";
    } else {
        echo "A tabela saloes já existe.<br>";
    }
    
    // Verificar se já existem registros na tabela saloes
    $count = $conn->query("SELECT COUNT(*) FROM saloes")->fetchColumn();
    
    if ($count == 0) {
        // Verificar se existe um usuário do tipo 'salao'
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE tipo = 'salao' LIMIT 1");
        $stmt->execute();
        $salaoUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$salaoUser) {
            // Criar um usuário do tipo 'salao'
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Salão Teste', 'salao@teste.com', password_hash('salao123', PASSWORD_DEFAULT), 'salao']);
            $salaoUserId = $conn->lastInsertId();
            echo "Usuário do salão criado com ID: $salaoUserId<br>";
        } else {
            $salaoUserId = $salaoUser['id'];
            echo "Usuário do salão já existe com ID: $salaoUserId<br>";
        }
        
        // Inserir um salão de teste
        $stmt = $conn->prepare("INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, num_funcionarios, media_diaria, media_semanal, pix_chave, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Barbearia Teste',
            '12345678901234',
            'São Paulo',
            'Rua Teste, 123',
            '11999999999',
            3,
            15,
            90,
            '12345678901',
            $salaoUserId
        ]);
        
        $salaoId = $conn->lastInsertId();
        echo "Salão de teste criado com ID: $salaoId<br>";
        
        // Inserir mais um salão para teste
        $stmt = $conn->prepare("INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, num_funcionarios, media_diaria, media_semanal, pix_chave, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Salão Beleza',
            '98765432109876',
            'Rio de Janeiro',
            'Av. Principal, 456',
            '21988888888',
            5,
            25,
            150,
            '98765432109',
            $salaoUserId
        ]);
        
        $salaoId2 = $conn->lastInsertId();
        echo "Segundo salão de teste criado com ID: $salaoId2<br>";
    } else {
        echo "Já existem $count salões cadastrados.<br>";
    }
    
    echo "<br>Processo concluído com sucesso!";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}
?>