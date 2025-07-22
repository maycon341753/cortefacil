<?php
// Ativa a exibição de erros no output
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações de conexão
define('DB_HOST', '31.97.18.57');
define('DB_PORT', '3308');
define('DB_NAME', 'cortefacil');
define('DB_USER', 'mysql');
define('DB_PASS', 'Brava1997');

// Função para verificar se o serviço MySQL está rodando
function isMySQLRunning($host = DB_HOST, $port = DB_PORT) {
    try {
        $socket = @fsockopen($host, $port, $errno, $errstr, 5); // timeout de 5 segundos
        if ($socket) {
            fclose($socket);
            return true;
        }
        error_log("Falha ao conectar socket: errno=$errno, errstr=$errstr");
        return false;
    } catch (Exception $e) {
        error_log("Erro na verificação do MySQL: " . $e->getMessage());
        return false;
    }
}

// Função para obter conexão PDO
function getConexao() {
    static $conn = null;
    error_log("Iniciando getConexao()");
    
    if ($conn === null) {
        // Verifica se o MySQL está rodando
        if (!isMySQLRunning()) {
            error_log("MySQL não está rodando em " . DB_HOST . ":" . DB_PORT);
            throw new Exception("O servidor MySQL não está acessível. Verifique a conexão com " . DB_HOST . ":" . DB_PORT);
        }

        try {
            // Conecta diretamente ao banco de dados
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10, // timeout de 10 segundos
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            error_log("Tentando conectar ao banco " . DB_NAME . " em " . DB_HOST . ":" . DB_PORT);
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            error_log("Conexão PDO estabelecida com sucesso");
            
            // Testa a conexão fazendo uma query simples
            $conn->query("SELECT 1");
            error_log("Teste de conexão realizado com sucesso");
            
        } catch (PDOException $e) {
            error_log("Erro PDO: " . $e->getMessage());
            
            // Mensagens de erro mais específicas
            if (strpos($e->getMessage(), 'Connection refused') !== false) {
                throw new Exception("Conexão recusada. Verifique se o servidor MySQL está rodando e acessível.");
            } elseif (strpos($e->getMessage(), 'timeout') !== false) {
                throw new Exception("Timeout na conexão. O servidor pode estar sobrecarregado.");
            } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
                throw new Exception("Acesso negado. Verifique as credenciais do banco de dados.");
            } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                throw new Exception("Banco de dados '" . DB_NAME . "' não encontrado.");
            } else {
                throw new Exception("Erro de conexão: " . $e->getMessage());
            }
        }
    }
    
    return $conn;
}

// Estabelece a conexão
try {
    $conn = getConexao();
    error_log("Conexão estabelecida com sucesso no arquivo principal");
} catch (Exception $e) {
    error_log("ERRO CRÍTICO: " . $e->getMessage());
    
    // Em desenvolvimento, mostra o erro detalhado
    if (ini_get('display_errors')) {
        echo "<pre>";
        echo "Erro de Conexão:\n";
        echo $e->getMessage() . "\n\n";
        echo "Stack Trace:\n";
        echo $e->getTraceAsString();
        echo "</pre>";
    }
    
    // Resposta JSON para AJAX
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro de conexão com o banco de dados',
            'details' => $e->getMessage() // Apenas em desenvolvimento
        ]);
    }
    exit;
}
?>