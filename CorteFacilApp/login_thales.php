<?php
session_start();
require_once 'php/conexao.php';

echo "<h2>Login Automático - Thales</h2>";

try {
    $pdo = getConexao();
    
    // Buscar o cliente Thales
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE nome LIKE '%thales%' OR nome LIKE '%Thales%'");
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        echo "<p style='color: red;'>❌ Cliente Thales não encontrado!</p>";
        exit;
    }
    
    // Fazer login automático
    $_SESSION['id'] = $cliente['id'];
    $_SESSION['nome'] = $cliente['nome'];
    $_SESSION['email'] = $cliente['email'];
    $_SESSION['tipo'] = 'cliente';
    
    echo "<p style='color: green;'>✅ Login realizado com sucesso!</p>";
    echo "<p><strong>Usuário:</strong> {$cliente['nome']}</p>";
    echo "<p><strong>Email:</strong> {$cliente['email']}</p>";
    echo "<p><strong>ID:</strong> {$cliente['id']}</p>";
    
    // Redirecionar para a página do cliente
    echo "<script>";
    echo "setTimeout(function() {";
    echo "  window.location.href = 'cliente.php';";
    echo "}, 2000);";
    echo "</script>";
    
    echo "<p>Redirecionando para a página do cliente em 2 segundos...</p>";
    echo "<p><a href='cliente.php'>Clique aqui se não for redirecionado automaticamente</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>