<?php
echo "<h2>Teste Simples - Sem Banco</h2>";
echo "<p>✅ PHP está funcionando!</p>";
echo "<p>Data/Hora: " . date('Y-m-d H:i:s') . "</p>";

// Verificar se o arquivo conexao.php existe
if (file_exists('conexao.php')) {
    echo "<p>✅ Arquivo conexao.php encontrado</p>";
} else {
    echo "<p>❌ Arquivo conexao.php não encontrado</p>";
}

// Verificar se consegue incluir o arquivo
try {
    require_once 'conexao.php';
    echo "<p>✅ Arquivo conexao.php incluído</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao incluir conexao.php: " . $e->getMessage() . "</p>";
}

// Tentar conectar
try {
    echo "<p>Tentando conectar ao banco...</p>";
    $pdo = getConexao();
    echo "<p>✅ Conexão estabelecida!</p>";
    
    // Fazer uma consulta simples
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "<p>✅ Consulta executada: " . $result['teste'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

echo "<p>Fim do teste</p>";
?>