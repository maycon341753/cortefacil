<?php
require_once 'php/conexao.php';

try {
    $pdo = getConexao();
    
    // Busca o usuário atual da sessão
    session_start();
    if (!isset($_SESSION['id'])) {
        echo "Usuário não está logado. <a href='teste_alterar_senha_parceiro.php'>Fazer login</a>";
        exit;
    }
    
    $usuario_id = $_SESSION['id'];
    
    // Busca a senha atual do usuário
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $senha_hash = $stmt->fetchColumn();
    
    echo "<h2>Teste de Senha para Usuário ID: $usuario_id</h2>";
    echo "<p>Hash atual: " . substr($senha_hash, 0, 50) . "...</p>";
    
    // Testa várias senhas possíveis
    $senhas_teste = ['123456', 'senha123', 'admin', 'salao123'];
    
    echo "<h3>Teste de Senhas:</h3>";
    foreach ($senhas_teste as $senha) {
        $resultado = password_verify($senha, $senha_hash);
        echo "<p>Senha '$senha': " . ($resultado ? '<span style="color: green;">FUNCIONA</span>' : '<span style="color: red;">NÃO FUNCIONA</span>') . "</p>";
    }
    
    // Se nenhuma senha funcionar, vamos redefinir para 123456
    $todas_falharam = true;
    foreach ($senhas_teste as $senha) {
        if (password_verify($senha, $senha_hash)) {
            $todas_falharam = false;
            break;
        }
    }
    
    if ($todas_falharam) {
        echo "<h3>Redefinindo senha para '123456'...</h3>";
        $nova_senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->execute([$nova_senha_hash, $usuario_id]);
        
        echo "<p style='color: green;'>Senha redefinida com sucesso! Agora você pode usar '123456' como senha atual.</p>";
        
        // Teste a nova senha
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $nova_senha_hash_db = $stmt->fetchColumn();
        
        $teste_nova = password_verify('123456', $nova_senha_hash_db);
        echo "<p>Teste da nova senha: " . ($teste_nova ? '<span style="color: green;">FUNCIONA</span>' : '<span style="color: red;">ERRO</span>') . "</p>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>