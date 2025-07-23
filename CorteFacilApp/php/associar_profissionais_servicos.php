<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    echo "Verificando associações profissional-serviços...\n";
    
    // Verificar se existe a tabela profissional_servicos
    $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "Tabela profissional_servicos não existe. Criando...\n";
        $pdo->exec("CREATE TABLE profissional_servicos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            profissional_id INT NOT NULL,
            servico_id INT NOT NULL,
            FOREIGN KEY (profissional_id) REFERENCES profissionais(id),
            FOREIGN KEY (servico_id) REFERENCES servicos(id),
            UNIQUE KEY unique_prof_serv (profissional_id, servico_id)
        )");
        echo "Tabela criada!\n";
    }
    
    // Buscar profissional do salão 4 (Liz Hadassa)
    $stmt = $pdo->prepare("SELECT id, nome FROM profissionais WHERE salao_id = 4");
    $stmt->execute();
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar serviços do salão 4
    $stmt = $pdo->prepare("SELECT id, nome FROM servicos WHERE salao_id = 4 AND ativo = 1");
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Profissionais encontrados: " . count($profissionais) . "\n";
    echo "Serviços encontrados: " . count($servicos) . "\n";
    
    // Associar cada profissional a todos os serviços
    foreach ($profissionais as $prof) {
        foreach ($servicos as $serv) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO profissional_servicos (profissional_id, servico_id) VALUES (?, ?)");
                $stmt->execute([$prof['id'], $serv['id']]);
                echo "Associado: {$prof['nome']} -> {$serv['nome']}\n";
            } catch (Exception $e) {
                echo "Erro ao associar {$prof['nome']} -> {$serv['nome']}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Associações concluídas!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>