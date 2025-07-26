<?php
// Ativa a exibição de erros no output
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// configurações de conexão ORIGINAIS
define('DB_HOST', '31.97.18.57');
define('DB_PORT', '3308');
// Configurações para PRODUÇÃO (Hostinger)
define('DB_HOST', 'others_mw-cf');
define('DB_PORT', '3306');
define('DB_NAME', 'cortefacil');
define('DB_USER', 'mysql');
define('DB_PASS', 'Brava1997');

// Configurações do pool de conexões (com verificação para evitar redefinição)
if (!defined('POOL_MIN_SIZE')) define('POOL_MIN_SIZE', 2);
if (!defined('POOL_MAX_SIZE')) define('POOL_MAX_SIZE', 10);
if (!defined('POOL_TIMEOUT')) define('POOL_TIMEOUT', 30);
if (!defined('CONNECTION_TIMEOUT')) define('CONNECTION_TIMEOUT', 10);
if (!defined('IDLE_TIMEOUT')) define('IDLE_TIMEOUT', 300);

/**
 * Classe para gerenciar pool de conexões MySQL
 */
class MySQLConnectionPool {
    private static $instance = null;
    private $connections = [];
    private $lastCleanup = 0;
    
    private function __construct() {
        $this->lastCleanup = time();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Cria uma nova conexão usando as funções originais
     */
    private function createConnection() {
        // Verifica se o MySQL está rodando usando a função original
        if (!isMySQLRunning()) {
            throw new Exception("O servidor MySQL não está acessível. Verifique a conexão com " . DB_HOST . ":" . DB_PORT);
        }

        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => CONNECTION_TIMEOUT,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            $conn->query("SELECT 1"); // Testa a conexão
            
            return $conn;
            
        } catch (PDOException $e) {
            // Usa o mesmo tratamento de erro da função original
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
    
    /**
     * Verifica se uma conexão ainda está válida
     */
    private function isConnectionValid($connection) {
        try {
            if ($connection === null) return false;
            $connection->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Limpeza do pool
     */
    private function cleanupPool() {
        $now = time();
        
        if ($now - $this->lastCleanup < 60) {
            return;
        }
        
        foreach ($this->connections as $key => $connInfo) {
            if (!$connInfo['in_use']) {
                // Remove conexões inválidas ou muito antigas
                if (!$this->isConnectionValid($connInfo['connection']) || 
                    ($now - $connInfo['last_used']) > IDLE_TIMEOUT) {
                    unset($this->connections[$key]);
                }
            }
        }
        
        $this->connections = array_values($this->connections);
        $this->lastCleanup = $now;
    }
    
    /**
     * Obtém conexão do pool
     */
    public function getConnection() {
        $this->cleanupPool();
        
        // Procura conexão disponível
        foreach ($this->connections as $key => &$connInfo) {
            if (!$connInfo['in_use'] && $this->isConnectionValid($connInfo['connection'])) {
                $connInfo['in_use'] = true;
                $connInfo['last_used'] = time();
                return $connInfo['connection'];
            }
        }
        
        // Se não encontrou e pode criar nova
        if (count($this->connections) < POOL_MAX_SIZE) {
            $connection = $this->createConnection();
            
            $this->connections[] = [
                'connection' => $connection,
                'created_at' => time(),
                'last_used' => time(),
                'in_use' => true
            ];
            
            return $connection;
        }
        
        // Pool cheio, retorna conexão diretamente sem pool
        return $this->createConnection();
    }
    
    /**
     * Retorna conexão ao pool
     */
    public function releaseConnection($connection) {
        foreach ($this->connections as $key => &$connInfo) {
            if ($connInfo['connection'] === $connection) {
                $connInfo['in_use'] = false;
                $connInfo['last_used'] = time();
                return true;
            }
        }
        return false;
    }
}

// MANTÉM A FUNÇÃO ORIGINAL isMySQLRunning
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

// MANTÉM A FUNÇÃO ORIGINAL getConexao mas com pool
function getConexao() {
    static $conn = null;
    static $usePool = true;
    
    error_log("Iniciando getConexao()");
    
    // Se já tem conexão e está válida, reutiliza (comportamento original)
    if ($conn !== null) {
        try {
            $conn->query("SELECT 1");
            return $conn;
        } catch (Exception $e) {
            $conn = null; // Invalida conexão
        }
    }
    
    // Tenta usar o pool
    if ($usePool) {
        try {
            $pool = MySQLConnectionPool::getInstance();
            $conn = $pool->getConnection();
            error_log("Conexão obtida do pool com sucesso");
            return $conn;
        } catch (Exception $e) {
            error_log("Falha no pool, usando método original: " . $e->getMessage());
            $usePool = false; // Desabilita pool em caso de erro
        }
    }
    
    // Fallback para o método original
    if ($conn === null) {
        // Verifica se o MySQL está rodando
        if (!isMySQLRunning()) {
            error_log("MySQL não está rodando em " . DB_HOST . ":" . DB_PORT);
            throw new Exception("O servidor MySQL não está acessível. Verifique a conexão com " . DB_HOST . ":" . DB_PORT);
        }

        try {
            // Conecta diretamente ao banco de dados (CÓDIGO ORIGINAL)
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
            
            // Mensagens de erro mais específicas (CÓDIGO ORIGINAL)
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

// Função adicional para retornar conexão ao pool
function releaseConexao($connection) {
    $pool = MySQLConnectionPool::getInstance();
    return $pool->releaseConnection($connection);
}

// MANTÉM A INICIALIZAÇÃO ORIGINAL
try {
    $conn = getConexao();
    error_log("Conexão estabelecida com sucesso no arquivo principal");
} catch (Exception $e) {
    error_log("ERRO CRÍTICO: " . $e->getMessage());
    
    // Em desenvolvimento, mostra o erro detalhado (CÓDIGO ORIGINAL)
    if (ini_get('display_errors')) {
        echo "<pre>";
        echo "Erro de Conexão:\n";
        echo $e->getMessage() . "\n\n";
        echo "Stack Trace:\n";
        echo $e->getTraceAsString();
        echo "</pre>";
    }
    
    // Resposta JSON para AJAX (CÓDIGO ORIGINAL)
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