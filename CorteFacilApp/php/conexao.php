<?php
// Ativa a exibição de erros no output
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para verificar se o serviço MySQL está rodando
function isMySQLRunning() {
    try {
        $socket = @fsockopen('localhost', 3306);
        if ($socket) {
            fclose($socket);
            return true;
        }
        return false;
    } catch (Exception $e) {
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
            error_log("MySQL não está rodando");
            throw new Exception("O servidor MySQL não está rodando. Por favor, verifique se o XAMPP está iniciado corretamente.");
        }

        $host = 'localhost';
        $db = 'cortefacil';
        $user = 'root';
        $pass = '';
        
        try {
            // Primeiro tenta conectar apenas ao MySQL
            $dsn_base = "mysql:host={$host}";
            error_log("Tentando conectar ao MySQL com DSN base: {$dsn_base}");
            $conn_base = new PDO($dsn_base, $user, $pass);
            error_log("Conexão base estabelecida");

            // Verifica se o banco existe
            $result = $conn_base->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$db}'");
            if (!$result->fetch()) {
                error_log("Banco de dados {$db} não existe");
                throw new Exception("Banco de dados não encontrado");
            }
            error_log("Banco de dados {$db} existe");

            // Agora conecta ao banco específico
            $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            error_log("Tentando conectar ao banco {$db}");
            $conn = new PDO($dsn, $user, $pass, $options);
            error_log("Conexão PDO estabelecida com sucesso");
        } catch (PDOException $e) {
            throw new Exception("Conexão falhou: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Estabelece a conexão
try {
    $conn = getConexao();
} catch (Exception $e) {
    error_log("Erro ao estabelecer conexão: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Erro de conexão com o banco de dados'
    ));
    exit;
}

// Não retorna a conexão diretamente, apenas define a função
?>