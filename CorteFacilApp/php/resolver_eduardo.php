<?php
// Script final para resolver o problema do Salão do Eduardo
echo "=== Resolvendo problema do Salão do Eduardo ===\n";

$host = '31.97.18.57';
$port = '3308';
$dbname = 'cortefacil';
$user = 'mysql';
$pass = 'Brava1997';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // 1. Buscar o Salão do Eduardo
    echo "1. Buscando Salão do Eduardo...\n";
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome_fantasia LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "❌ Salão do Eduardo não encontrado!\n";
        exit;
    }
    
    echo "✅ Salão encontrado: " . $salao['nome_fantasia'] . " (ID: " . $salao['id'] . ")\n";
    $salao_id = $salao['id'];
    
    // 2. Verificar profissionais ativos
    echo "2. Verificando profissionais ativos...\n";
    $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Profissionais ativos: " . count($profissionais) . "\n";
    foreach ($profissionais as $prof) {
        echo "- " . $prof['nome'] . " (ID: " . $prof['id'] . ")\n";
    }
    
    // 3. Verificar serviços ativos
    echo "3. Verificando serviços ativos...\n";
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Serviços ativos: " . count($servicos) . "\n";
    foreach ($servicos as $serv) {
        echo "- " . $serv['nome'] . " (ID: " . $serv['id'] . ", R$ " . $serv['preco'] . ")\n";
    }
    
    // 4. Verificar associações na tabela profissional_servicos
    echo "4. Verificando associações profissional-serviço...\n";
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM profissional_servicos ps
        JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ?
    ");
    $stmt->execute([$salao_id]);
    $result = $stmt->fetch();
    
    echo "Associações existentes: " . $result['total'] . "\n";
    
    // 5. Se não há associações, criar
    if ($result['total'] == 0 && !empty($profissionais) && !empty($servicos)) {
        echo "5. Criando associações profissional-serviço...\n";
        
        foreach ($profissionais as $prof) {
            foreach ($servicos as $serv) {
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO profissional_servicos (profissional_id, servico_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$prof['id'], $serv['id']]);
                echo "Associação criada: " . $prof['nome'] . " → " . $serv['nome'] . "\n";
            }
        }
    } else {
        echo "5. Associações já existem ou não há dados para criar\n";
    }
    
    // 6. Testar a consulta da API
    echo "6. Testando consulta da API...\n";
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.nome, s.preco, s.duracao_minutos
        FROM servicos s
        INNER JOIN profissional_servicos ps ON s.id = ps.servico_id
        INNER JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ? AND s.ativo = 1 AND p.ativo = 1
        ORDER BY s.nome
    ");
    
    $stmt->execute([$salao_id]);
    $servicos_api = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Serviços retornados pela API: " . count($servicos_api) . "\n";
    
    if (count($servicos_api) > 0) {
        echo "✅ SUCESSO! Serviços encontrados:\n";
        foreach ($servicos_api as $serv) {
            echo "- " . $serv['nome'] . " (R$ " . number_format($serv['preco'], 2, ',', '.') . ")\n";
        }
    } else {
        echo "❌ PROBLEMA: Nenhum serviço retornado pela consulta da API\n";
    }
    
    echo "\n=== RESULTADO FINAL ===\n";
    if (count($servicos_api) > 0) {
        echo "✅ Problema resolvido! O Salão do Eduardo agora deve exibir " . count($servicos_api) . " serviço(s).\n";
    } else {
        echo "❌ Problema ainda existe. Verifique os dados do salão.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>