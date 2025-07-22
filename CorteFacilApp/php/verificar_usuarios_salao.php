<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Busca usuários do tipo salão
    $stmt = $conn->prepare("SELECT u.*, s.nome_fantasia FROM usuarios u JOIN saloes s ON s.usuario_id = u.id WHERE u.tipo = 'salao'");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuários do tipo salão encontrados:\n";
    foreach ($usuarios as $usuario) {
        echo "ID: {$usuario['id']}\n";
        echo "Nome: {$usuario['nome']}\n";
        echo "Email: {$usuario['email']}\n";
        echo "Nome Fantasia: {$usuario['nome_fantasia']}\n";
        echo "-------------------\n";
    }
    
} catch (PDOException $e) {
    echo "Erro ao buscar usuários: " . $e->getMessage();
}
?>