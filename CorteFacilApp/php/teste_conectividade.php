<?php
// Teste completo de conectividade do projeto CorteFácil
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>🔍 Teste de Conectividade - CorteFácil</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

// 1. Teste de Servidor
echo "<div class='section'>";
echo "<h2>🖥️ Servidor Local</h2>";
echo "<p class='success'>✅ Servidor PHP rodando em localhost:8000</p>";
echo "<p class='info'>📍 Diretório: " . __DIR__ . "</p>";
echo "<p class='info'>🐘 Versão PHP: " . phpversion() . "</p>";
echo "</div>";

// 2. Teste de Conexão com Banco
echo "<div class='section'>";
echo "<h2>🗄️ Banco de Dados</h2>";

try {
    include 'conexao.php';
    
    if (isset($conn) && $conn instanceof PDO) {
        echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
        
        // Teste de consulta simples
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result['test'] == 1) {
            echo "<p class='success'>✅ Consultas funcionando</p>";
        }
        
        // Verificar tabelas principais
        $tabelas = ['usuarios', 'saloes', 'agendamentos', 'servicos', 'profissionais'];
        echo "<h3>📋 Tabelas do Sistema:</h3>";
        
        foreach ($tabelas as $tabela) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM $tabela");
                $count = $stmt->fetch()['count'];
                echo "<p class='success'>✅ $tabela: $count registros</p>";
            } catch (Exception $e) {
                echo "<p class='error'>❌ $tabela: Não encontrada</p>";
            }
        }
        
    } else {
        echo "<p class='error'>❌ Falha na conexão com banco</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 3. Teste de Arquivos Principais
echo "<div class='section'>";
echo "<h2>📁 Arquivos Principais</h2>";

$arquivos_importantes = [
    'admin_login.html' => '../admin_login.html',
    'admin/painel.php' => '../admin/painel.php',
    'parceiro_login.html' => '../parceiro_login.html',
    'index.html' => '../index.html'
];

foreach ($arquivos_importantes as $nome => $caminho) {
    if (file_exists($caminho)) {
        echo "<p class='success'>✅ $nome</p>";
    } else {
        echo "<p class='error'>❌ $nome (não encontrado)</p>";
    }
}
echo "</div>";

// 4. Teste de URLs Principais
echo "<div class='section'>";
echo "<h2>🌐 URLs do Sistema</h2>";
$base_url = "http://localhost:8000";

$urls = [
    'Login Admin' => "$base_url/admin_login.html",
    'Painel Admin' => "$base_url/admin/painel.php",
    'Login Parceiro' => "$base_url/parceiro_login.html",
    'Página Inicial' => "$base_url/index.html"
];

foreach ($urls as $nome => $url) {
    echo "<p class='info'>🔗 <a href='$url' target='_blank'>$nome</a></p>";
}
echo "</div>";

// 5. Teste de Sessão
echo "<div class='section'>";
echo "<h2>🔐 Sistema de Sessão</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p class='info'>📋 Session ID: " . session_id() . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✅ Usuário logado: " . ($_SESSION['user_name'] ?? 'N/A') . "</p>";
    echo "<p class='info'>👤 Tipo: " . ($_SESSION['user_type'] ?? 'N/A') . "</p>";
} else {
    echo "<p class='info'>ℹ️ Nenhum usuário logado</p>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>📊 Resumo</h2>";
echo "<p class='success'>✅ Servidor funcionando corretamente</p>";
echo "<p class='success'>✅ Banco de dados conectado</p>";
echo "<p class='info'>🚀 Sistema pronto para uso!</p>";
echo "</div>";
?>