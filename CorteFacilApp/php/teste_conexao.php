<?php
// Teste de conexão direta
echo "Testando conexão com banco remoto...\n";

$host = '31.97.18.57';
$port = '3308';
$dbname = 'cortefacil';
$user = 'mysql';
$pass = 'Brava1997';

try {
    // Teste 1: Verificar se a porta está acessível
    echo "1. Testando conectividade com $host:$port...\n";
    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if ($socket) {
        echo "✅ Porta acessível\n";
        fclose($socket);
    } else {
        echo "❌ Porta não acessível: $errstr ($errno)\n";
        exit;
    }
    
    // Teste 2: Tentar conexão PDO
    echo "2. Testando conexão PDO...\n";
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Conexão PDO estabelecida\n";
    
    // Teste 3: Consulta simples
    echo "3. Testando consulta...\n";
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "✅ Consulta executada: " . $result['teste'] . "\n";
    
    // Teste 4: Verificar salões
    echo "4. Verificando salões...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM saloes");
    $result = $stmt->fetch();
    echo "Total de salões: " . $result['total'] . "\n";
    
    // Teste 5: Buscar Salão do Eduardo
    echo "5. Buscando Salão do Eduardo...\n";
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch();
    
    if ($salao) {
        echo "✅ Salão encontrado: " . $salao['nome'] . " (ID: " . $salao['id'] . ")\n";
        $salao_id = $salao['id'];
        
        // Teste 6: Verificar profissionais
        echo "6. Verificando profissionais...\n";
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM profissionais WHERE salao_id = ? AND ativo = 1");
        $stmt->execute([$salao_id]);
        $result = $stmt->fetch();
        echo "Profissionais ativos: " . $result['total'] . "\n";
        
        // Teste 7: Verificar serviços
        echo "7. Verificando serviços...\n";
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM servicos WHERE salao_id = ? AND ativo = 1");
        $stmt->execute([$salao_id]);
        $result = $stmt->fetch();
        echo "Serviços ativos: " . $result['total'] . "\n";
        
        // Teste 8: Verificar tabela profissional_servicos
        echo "8. Verificando tabela profissional_servicos...\n";
        $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela existe\n";
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total 
                FROM profissional_servicos ps
                JOIN profissionais p ON ps.profissional_id = p.id
                WHERE p.salao_id = ?
            ");
            $stmt->execute([$salao_id]);
            $result = $stmt->fetch();
            echo "Associações existentes: " . $result['total'] . "\n";
        } else {
            echo "❌ Tabela não existe\n";
        }
        
    } else {
        echo "❌ Salão do Eduardo não encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>