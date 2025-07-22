<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função para testar conexão MySQL
function testMySQLConnection() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'cortefacil';
    
    try {
        // Tenta conectar ao MySQL sem selecionar banco
        $dsn = "mysql:host=$host";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            return [
                'status' => 'erro',
                'mensagem' => 'Erro na conexão MySQL',
                'erro' => $e->getMessage()
            ];
        }
        
        // Verifica se o banco existe
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db'");
        if ($stmt->rowCount() == 0) {
            return [
                'status' => 'erro',
                'mensagem' => 'Banco de dados não encontrado',
                'erro' => "O banco de dados '$db' não existe"
            ];
        }
        
        // Conecta ao banco específico
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        // Testa a tabela usuarios
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
            $count = $stmt->fetchColumn();
            
            return [
                'status' => 'ok',
                'mensagem' => 'Conexão bem sucedida',
                'total_usuarios' => $count
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'erro',
                'mensagem' => 'Erro ao acessar tabela usuarios',
                'erro' => $e->getMessage()
            ];
        }
        
    } catch (Exception $e) {
        return [
            'status' => 'erro',
            'mensagem' => 'Exceção capturada',
            'erro' => $e->getMessage()
        ];
    }
}

// Informações do servidor
$server_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'mysql_service' => shell_exec('net start | findstr "MySQL"') ?? 'N/A',
    'apache_service' => shell_exec('net start | findstr "Apache"') ?? 'N/A'
];

// Testa a conexão
$test_result = testMySQLConnection();

// Combina os resultados
$response = [
    'server_info' => $server_info,
    'connection_test' => $test_result
];

// Retorna o resultado
if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode($response, JSON_PRETTY_PRINT);
?>