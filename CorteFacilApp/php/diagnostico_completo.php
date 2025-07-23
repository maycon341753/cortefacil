<?php
// Diagnóstico completo de conectividade - CorteFácil
header('Content-Type: text/html; charset=UTF-8');

echo "<h1>🔧 Diagnóstico de Conectividade - CorteFácil</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; }
    .section { margin: 20px 0; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; }
    .status-ok { background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; }
    .status-error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; }
</style>";

// 1. Informações do Servidor
echo "<div class='section'>";
echo "<h2>🖥️ Informações do Servidor</h2>";
echo "<div class='status-ok'>";
echo "<p class='success'>✅ Servidor PHP ativo</p>";
echo "<p class='info'>📍 Diretório atual: " . __DIR__ . "</p>";
echo "<p class='info'>🐘 PHP: " . phpversion() . "</p>";
echo "<p class='info'>🌐 Servidor: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido' . "</p>";
echo "<p class='info'>🔗 Host: " . $_SERVER['HTTP_HOST'] ?? 'localhost' . "</p>";
echo "<p class='info'>📂 Document Root: " . $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' . "</p>";
echo "</div>";
echo "</div>";

// 2. Teste de Conexão com Banco
echo "<div class='section'>";
echo "<h2>🗄️ Conexão com Banco de Dados</h2>";

try {
    // Inclui o arquivo de conexão
    include_once 'conexao.php';
    
    if (isset($conn) && $conn instanceof PDO) {
        echo "<div class='status-ok'>";
        echo "<p class='success'>✅ Conexão estabelecida com sucesso</p>";
        
        // Informações da conexão
        $stmt = $conn->query("SELECT DATABASE() as db_name, USER() as user_name, VERSION() as version");
        $info = $stmt->fetch();
        
        echo "<p class='info'>🏷️ Banco: " . $info['db_name'] . "</p>";
        echo "<p class='info'>👤 Usuário: " . $info['user_name'] . "</p>";
        echo "<p class='info'>📊 Versão MySQL: " . $info['version'] . "</p>";
        echo "</div>";
        
        // Teste de tabelas
        echo "<h3>📋 Verificação de Tabelas</h3>";
        $tabelas_esperadas = ['usuarios', 'saloes', 'agendamentos', 'servicos', 'profissionais'];
        
        foreach ($tabelas_esperadas as $tabela) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM $tabela");
                $count = $stmt->fetch()['count'];
                echo "<p class='success'>✅ $tabela: $count registros</p>";
            } catch (Exception $e) {
                echo "<p class='error'>❌ $tabela: " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<div class='status-error'>";
        echo "<p class='error'>❌ Falha na conexão com banco</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='status-error'>";
    echo "<p class='error'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</div>";

// 3. Verificação de Arquivos Críticos
echo "<div class='section'>";
echo "<h2>📁 Arquivos Críticos do Sistema</h2>";

$arquivos_criticos = [
    'Conexão DB' => 'conexao.php',
    'Login Admin' => 'admin_login.php',
    'Verificar Admin' => 'verificar_admin.php',
    'Login Parceiro' => 'parceiro_login.php',
    'API Salões' => 'api_saloes.php',
    'API Agendamentos' => 'api_agendamentos.php'
];

foreach ($arquivos_criticos as $nome => $arquivo) {
    if (file_exists($arquivo)) {
        echo "<p class='success'>✅ $nome ($arquivo)</p>";
    } else {
        echo "<p class='error'>❌ $nome ($arquivo) - Não encontrado</p>";
    }
}
echo "</div>";

// 4. Teste de URLs Principais
echo "<div class='section'>";
echo "<h2>🌐 URLs do Sistema</h2>";
$base_url = "http://" . $_SERVER['HTTP_HOST'];

$urls_sistema = [
    'Página Inicial' => "$base_url/index.html",
    'Login Admin' => "$base_url/admin_login.html",
    'Login Parceiro' => "$base_url/parceiro_login.html",
    'Painel Admin' => "$base_url/admin/painel.php",
    'API Salões' => "$base_url/php/api_saloes.php",
    'Teste Conexão' => "$base_url/php/test_connection.php"
];

foreach ($urls_sistema as $nome => $url) {
    echo "<p class='info'>🔗 <a href='$url' target='_blank'>$nome</a></p>";
}
echo "</div>";

// 5. Configurações PHP Importantes
echo "<div class='section'>";
echo "<h2>⚙️ Configurações PHP</h2>";

$configs = [
    'display_errors' => ini_get('display_errors') ? 'Ativado' : 'Desativado',
    'error_reporting' => error_reporting(),
    'max_execution_time' => ini_get('max_execution_time') . 's',
    'memory_limit' => ini_get('memory_limit'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'session.save_path' => ini_get('session.save_path')
];

foreach ($configs as $config => $valor) {
    echo "<p class='info'>📋 $config: <code>$valor</code></p>";
}
echo "</div>";

// 6. Teste de Sessão
echo "<div class='section'>";
echo "<h2>🔐 Sistema de Sessão</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p class='info'>📋 Status da Sessão: " . 
    (session_status() === PHP_SESSION_ACTIVE ? 'Ativa' : 'Inativa') . "</p>";
echo "<p class='info'>🆔 Session ID: " . session_id() . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✅ Usuário logado: " . ($_SESSION['user_name'] ?? 'N/A') . "</p>";
    echo "<p class='info'>👤 Tipo: " . ($_SESSION['user_type'] ?? 'N/A') . "</p>";
    echo "<p class='info'>🆔 ID: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p class='warning'>⚠️ Nenhum usuário logado</p>";
}
echo "</div>";

// 7. Teste de Funcionalidades Específicas
echo "<div class='section'>";
echo "<h2>🧪 Testes de Funcionalidades</h2>";

// Teste de login admin
try {
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM usuarios WHERE tipo = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->fetch()['count'];
        
        if ($admin_count > 0) {
            echo "<p class='success'>✅ Administradores cadastrados: $admin_count</p>";
        } else {
            echo "<p class='warning'>⚠️ Nenhum administrador cadastrado</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao verificar admins: " . $e->getMessage() . "</p>";
}

// Teste de salões
try {
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM saloes");
        $stmt->execute();
        $salao_count = $stmt->fetch()['count'];
        
        echo "<p class='success'>✅ Salões cadastrados: $salao_count</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao verificar salões: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 8. Resumo Final
echo "<div class='section'>";
echo "<h2>📊 Resumo do Diagnóstico</h2>";

$problemas = 0;
$sucessos = 0;

// Conta sucessos e problemas baseado nos testes acima
if (isset($conn) && $conn instanceof PDO) {
    $sucessos++;
    echo "<p class='success'>✅ Conexão com banco funcionando</p>";
} else {
    $problemas++;
    echo "<p class='error'>❌ Problema na conexão com banco</p>";
}

if (file_exists('admin_login.php')) {
    $sucessos++;
    echo "<p class='success'>✅ Sistema de login funcionando</p>";
} else {
    $problemas++;
    echo "<p class='error'>❌ Arquivos de login não encontrados</p>";
}

if (session_status() === PHP_SESSION_ACTIVE) {
    $sucessos++;
    echo "<p class='success'>✅ Sistema de sessão funcionando</p>";
} else {
    $problemas++;
    echo "<p class='error'>❌ Problema no sistema de sessão</p>";
}

echo "<hr>";
if ($problemas == 0) {
    echo "<div class='status-ok'>";
    echo "<h3 class='success'>🎉 Sistema Funcionando Perfeitamente!</h3>";
    echo "<p>Todos os componentes estão operacionais.</p>";
    echo "</div>";
} else {
    echo "<div class='status-error'>";
    echo "<h3 class='error'>⚠️ Problemas Detectados</h3>";
    echo "<p>$problemas problema(s) encontrado(s). Verifique os itens marcados em vermelho.</p>";
    echo "</div>";
}

echo "</div>";

echo "<p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "Diagnóstico executado em " . date('d/m/Y H:i:s');
echo "</p>";
?>