<?php
// Verificar estrutura das tabelas
echo "Verificando estrutura das tabelas...\n";

$host = '31.97.18.57';
$port = '3308';
$dbname = 'cortefacil';
$user = 'mysql';
$pass = 'Brava1997';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Verificar estrutura da tabela saloes
    echo "=== Estrutura da tabela 'saloes' ===\n";
    $stmt = $pdo->query("DESCRIBE saloes");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n=== Primeiros registros da tabela 'saloes' ===\n";
    $stmt = $pdo->query("SELECT * FROM saloes LIMIT 3");
    $saloes = $stmt->fetchAll();
    foreach ($saloes as $salao) {
        echo "ID: " . $salao['id'];
        // Tentar diferentes possíveis nomes de colunas
        $possibleNames = ['nome', 'name', 'nome_salao', 'razao_social', 'titulo'];
        foreach ($possibleNames as $field) {
            if (isset($salao[$field])) {
                echo " | $field: " . $salao[$field];
            }
        }
        echo "\n";
    }
    
    echo "\n=== Estrutura da tabela 'profissionais' ===\n";
    $stmt = $pdo->query("DESCRIBE profissionais");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n=== Estrutura da tabela 'servicos' ===\n";
    $stmt = $pdo->query("DESCRIBE servicos");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n=== Verificar se existe tabela 'profissional_servicos' ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela existe\n";
        $stmt = $pdo->query("DESCRIBE profissional_servicos");
        $columns = $stmt->fetchAll();
        foreach ($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "❌ Tabela não existe\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>