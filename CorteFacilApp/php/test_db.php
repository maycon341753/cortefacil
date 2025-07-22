<?php
header('Content-Type: application/json');

try {
    // Tenta conectar ao banco de dados
    $host = 'localhost';
    $db = 'cortefacil';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verifica se a tabela usuarios existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $tableExists = $stmt->rowCount() > 0;
    
    echo json_encode([
        'status' => 'ok',
        'message' => 'Conexão com o banco de dados estabelecida com sucesso',
        'table_usuarios_exists' => $tableExists
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'erro',
        'message' => 'Erro ao conectar com o banco de dados',
        'error' => $e->getMessage()
    ]);
}
?>