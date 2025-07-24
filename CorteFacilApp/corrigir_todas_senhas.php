<?php
require_once 'php/conexao.php';

try {
    $pdo = getConexao();
    
    echo "<h2>Correção de Senhas - Sistema CorteFácil</h2>";
    
    // Busca todos os usuários do tipo salao
    $stmt = $pdo->prepare("SELECT id, cpf, nome, nome_fantasia, senha FROM usuarios WHERE tipo = 'salao'");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Usuários encontrados: " . count($usuarios) . "</h3>";
    
    $corrigidos = 0;
    
    foreach ($usuarios as $usuario) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<h4>Usuário: " . htmlspecialchars($usuario['nome']) . " (ID: " . $usuario['id'] . ")</h4>";
        echo "<p>CPF: " . htmlspecialchars($usuario['cpf']) . "</p>";
        
        $senha_hash = $usuario['senha'];
        
        // Verifica se a senha atual é um hash válido
        $info_hash = password_get_info($senha_hash);
        
        if ($info_hash['algo'] === null || $info_hash['algo'] === 0) {
            // Não é um hash válido, provavelmente texto plano
            echo "<p style='color: orange;'>Senha em texto plano detectada: " . htmlspecialchars($senha_hash) . "</p>";
            
            // Converte para hash
            $novo_hash = password_hash($senha_hash, PASSWORD_DEFAULT);
            
            $stmt_update = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt_update->execute([$novo_hash, $usuario['id']]);
            
            echo "<p style='color: green;'>✓ Senha convertida para hash seguro</p>";
            $corrigidos++;
        } else {
            echo "<p style='color: green;'>✓ Senha já está em formato hash</p>";
            
            // Testa se a senha '123456' funciona
            if (password_verify('123456', $senha_hash)) {
                echo "<p style='color: blue;'>✓ Senha '123456' funciona</p>";
            } else {
                echo "<p style='color: red;'>✗ Senha '123456' não funciona</p>";
                
                // Redefine para 123456
                $novo_hash = password_hash('123456', PASSWORD_DEFAULT);
                $stmt_update = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt_update->execute([$novo_hash, $usuario['id']]);
                
                echo "<p style='color: green;'>✓ Senha redefinida para '123456'</p>";
                $corrigidos++;
            }
        }
        
        echo "</div>";
    }
    
    echo "<h3 style='color: green;'>Correção concluída! $corrigidos usuários foram corrigidos.</h3>";
    echo "<p><a href='salao/dashboard.php'>Voltar ao Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>