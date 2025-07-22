<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Salões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Debug da Tabela Salões</h1>
        
        <?php
        require_once 'conexao.php';
        
        try {
            echo '<div class="alert alert-info">Conexão com o banco de dados estabelecida com sucesso.</div>';
            
            // Verificar se a tabela saloes existe
            $checkTable = $conn->query("SHOW TABLES LIKE 'saloes'")->fetchColumn();
            
            if ($checkTable > 0) {
                echo '<div class="alert alert-success">A tabela saloes existe.</div>';
                
                // Contar registros
                $count = $conn->query("SELECT COUNT(*) FROM saloes")->fetchColumn();
                echo "<div class='alert alert-info'>Número de salões cadastrados: <strong>$count</strong></div>";
                
                if ($count > 0) {
                    // Listar salões
                    $saloes = $conn->query("SELECT * FROM saloes")->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<h3 class="mt-4">Lista de Salões</h3>';
                    echo '<table class="table table-striped">';
                    echo '<thead><tr><th>ID</th><th>Nome</th><th>Cidade</th><th>Usuário ID</th><th>Ativo</th></tr></thead>';
                    echo '<tbody>';
                    
                    foreach ($saloes as $salao) {
                        echo '<tr>';
                        echo "<td>{$salao['id']}</td>";
                        echo "<td>{$salao['nome_fantasia']}</td>";
                        echo "<td>{$salao['cidade']}</td>";
                        echo "<td>{$salao['usuario_id']}</td>";
                        echo "<td>{$salao['ativo']}</td>";
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<div class="alert alert-warning">Não há salões cadastrados.</div>';
                    
                    // Criar um salão de teste
                    echo '<h3 class="mt-4">Criar Salão de Teste</h3>';
                    echo '<form method="post" action="">';
                    echo '<button type="submit" name="criar_salao" class="btn btn-primary">Criar Salão de Teste</button>';
                    echo '</form>';
                    
                    if (isset($_POST['criar_salao'])) {
                        // Verificar se existe um usuário do tipo 'salao'
                        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE tipo = 'salao' LIMIT 1");
                        $stmt->execute();
                        $salaoUser = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$salaoUser) {
                            // Criar um usuário do tipo 'salao'
                            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
                            $stmt->execute(['Salão Teste', 'salao@teste.com', password_hash('salao123', PASSWORD_DEFAULT), 'salao']);
                            $salaoUserId = $conn->lastInsertId();
                            echo "<div class='alert alert-success'>Usuário do salão criado com ID: $salaoUserId</div>";
                        } else {
                            $salaoUserId = $salaoUser['id'];
                            echo "<div class='alert alert-info'>Usuário do salão já existe com ID: $salaoUserId</div>";
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
                        echo "<div class='alert alert-success'>Salão de teste criado com ID: $salaoId</div>";
                        echo "<div class='alert alert-info'>Recarregue a página para ver o salão criado.</div>";
                    }
                }
                
            } else {
                echo '<div class="alert alert-danger">A tabela saloes não existe!</div>';
                
                // Criar a tabela saloes
                echo '<h3 class="mt-4">Criar Tabela Salões</h3>';
                echo '<form method="post" action="">';
                echo '<button type="submit" name="criar_tabela" class="btn btn-primary">Criar Tabela Salões</button>';
                echo '</form>';
                
                if (isset($_POST['criar_tabela'])) {
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
                    echo "<div class='alert alert-success'>Tabela saloes criada com sucesso!</div>";
                    echo "<div class='alert alert-info'>Recarregue a página para confirmar a criação da tabela.</div>";
                }
            }
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <div class="mt-4">
            <a href="../admin/painel.html" class="btn btn-secondary">Voltar para o Painel</a>
        </div>
    </div>
</body>
</html>