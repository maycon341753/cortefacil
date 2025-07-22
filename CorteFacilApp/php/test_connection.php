<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexao.php';

echo "<h1>Teste de Conexão e Tabela Salões</h1>";

try {
    echo "<p>Conexão com o banco de dados estabelecida com sucesso.</p>";
    
    // Verificar se a tabela saloes existe
    $checkTable = $conn->query("SHOW TABLES LIKE 'saloes'")->fetchColumn();
    
    if ($checkTable > 0) {
        echo "<p>A tabela 'saloes' existe.</p>";
        
        // Contar registros
        $count = $conn->query("SELECT COUNT(*) FROM saloes")->fetchColumn();
        echo "<p>Número de salões cadastrados: <strong>$count</strong></p>";
        
        if ($count > 0) {
            // Listar salões
            $saloes = $conn->query("SELECT * FROM saloes")->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h2>Lista de Salões</h2>";
            echo "<pre>";
            print_r($saloes);
            echo "</pre>";
        } else {
            echo "<p>Não há salões cadastrados.</p>";
            
            // Criar um salão de teste
            echo "<h2>Criando um salão de teste...</h2>";
            
            // Verificar se existe um usuário do tipo 'salao'
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE tipo = 'salao' LIMIT 1");
            $stmt->execute();
            $salaoUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$salaoUser) {
                // Criar um usuário do tipo 'salao'
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
                $stmt->execute(['Salão Teste', 'salao@teste.com', password_hash('salao123', PASSWORD_DEFAULT), 'salao']);
                $salaoUserId = $conn->lastInsertId();
                echo "<p>Usuário do salão criado com ID: $salaoUserId</p>";
            } else {
                $salaoUserId = $salaoUser['id'];
                echo "<p>Usuário do salão já existe com ID: $salaoUserId</p>";
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
            echo "<p>Salão de teste criado com ID: $salaoId</p>";
        }
    } else {
        echo "<p>A tabela 'saloes' não existe!</p>";
        
        // Criar a tabela saloes
        echo "<h2>Criando a tabela 'saloes'...</h2>";
        
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
        echo "<p>Tabela 'saloes' criada com sucesso!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
}
?>