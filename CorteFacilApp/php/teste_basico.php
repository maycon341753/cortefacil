<?php
echo "<h2>Teste Básico de Conexão</h2>";

try {
    // Teste 1: Verificar se o arquivo conexao.php existe
    if (file_exists('conexao.php')) {
        echo "<p>✅ Arquivo conexao.php existe</p>";
    } else {
        echo "<p>❌ Arquivo conexao.php não encontrado</p>";
        exit;
    }
    
    // Teste 2: Incluir o arquivo
    require_once 'conexao.php';
    echo "<p>✅ Arquivo conexao.php incluído com sucesso</p>";
    
    // Teste 3: Testar a função getConexao
    $pdo = getConexao();
    echo "<p>✅ Conexão obtida com sucesso</p>";
    
    // Teste 4: Fazer uma consulta simples
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "<p>✅ Consulta teste executada: " . $result['teste'] . "</p>";
    
    // Teste 5: Verificar se existe o salão do Eduardo
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<p>Salões com 'Eduardo' no nome: " . $result['total'] . "</p>";
    
    // Teste 6: Listar todos os salões
    $stmt = $pdo->query("SELECT id, nome FROM saloes LIMIT 5");
    $saloes = $stmt->fetchAll();
    echo "<p>Primeiros 5 salões:</p>";
    foreach ($saloes as $salao) {
        echo "<p>- {$salao['nome']} (ID: {$salao['id']})</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>