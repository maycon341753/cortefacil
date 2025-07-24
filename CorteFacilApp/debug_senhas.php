<?php
require_once 'php/conexao.php';

try {
    $pdo = getConexao();
    
    // Busca todos os usuários do tipo salão
    $stmt = $pdo->prepare("
        SELECT u.id, u.cpf, u.nome, u.senha, s.nome_fantasia
        FROM usuarios u
        JOIN saloes s ON s.usuario_id = u.id
        WHERE u.tipo = 'salao'
        ORDER BY u.id
    ");
    
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Debug de Senhas - Usuários Salão</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Nome Fantasia</th><th>Senha Hash</th><th>Teste 123456</th></tr>";
    
    foreach ($usuarios as $usuario) {
        $teste_senha = password_verify('123456', $usuario['senha']) ? 'SIM' : 'NÃO';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($usuario['id']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['cpf']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['nome_fantasia']) . "</td>";
        echo "<td>" . substr($usuario['senha'], 0, 30) . "...</td>";
        echo "<td style='color: " . ($teste_senha === 'SIM' ? 'green' : 'red') . ";'>" . $teste_senha . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Teste específico para o usuário logado
    session_start();
    if (isset($_SESSION['id'])) {
        echo "<h3>Usuário Atual da Sessão</h3>";
        echo "<p>ID: " . $_SESSION['id'] . "</p>";
        echo "<p>Tipo: " . $_SESSION['tipo'] . "</p>";
        echo "<p>Nome: " . $_SESSION['nome'] . "</p>";
        
        // Busca a senha do usuário atual
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['id']]);
        $senha_atual = $stmt->fetchColumn();
        
        echo "<p>Hash da senha atual: " . substr($senha_atual, 0, 30) . "...</p>";
        echo "<p>Teste com '123456': " . (password_verify('123456', $senha_atual) ? 'FUNCIONA' : 'NÃO FUNCIONA') . "</p>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>