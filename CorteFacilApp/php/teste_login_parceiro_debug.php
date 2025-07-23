<?php
require_once 'conexao.php';

echo "<h2>Teste de Login do Parceiro - Debug</h2>";

try {
    // Verificar se existe usuário do tipo 'salao'
    $stmt = $conn->query("SELECT u.id, u.nome, u.email, u.tipo, s.id as salao_id, s.nome_fantasia, s.ativo 
                         FROM usuarios u 
                         LEFT JOIN saloes s ON s.usuario_id = u.id 
                         WHERE u.tipo = 'salao'");
    $usuarios_salao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Usuários do tipo 'salao' encontrados:</h3>";
    if (empty($usuarios_salao)) {
        echo "<p style='color: red;'>❌ Nenhum usuário do tipo 'salao' encontrado!</p>";
        
        // Vamos criar um usuário de teste
        echo "<h3>Criando usuário de teste...</h3>";
        
        // Inserir usuário do salão
        $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Salão Teste', 'salao@teste.com', $senha_hash, 'salao']);
        $usuario_id = $conn->lastInsertId();
        
        echo "<p style='color: green;'>✅ Usuário criado: salao@teste.com / senha: 123456</p>";
        
        // Inserir salão
        $stmt = $conn->prepare("INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, usuario_id, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Salão Teste', '12345678000199', 'São Paulo', 'Rua Teste, 123', '11999999999', $usuario_id, 1]);
        
        echo "<p style='color: green;'>✅ Salão criado e vinculado ao usuário</p>";
        
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Salão ID</th><th>Nome Fantasia</th><th>Ativo</th></tr>";
        foreach ($usuarios_salao as $usuario) {
            echo "<tr>";
            echo "<td>{$usuario['id']}</td>";
            echo "<td>{$usuario['nome']}</td>";
            echo "<td>{$usuario['email']}</td>";
            echo "<td>{$usuario['tipo']}</td>";
            echo "<td>{$usuario['salao_id']}</td>";
            echo "<td>{$usuario['nome_fantasia']}</td>";
            echo "<td>" . ($usuario['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Testar login com credenciais padrão
    echo "<h3>Teste de Login:</h3>";
    $email_teste = 'salao@teste.com';
    $senha_teste = '123456';
    
    $stmt = $conn->prepare("
        SELECT u.id, u.senha, u.nome, s.id as salao_id, s.nome_fantasia
        FROM usuarios u
        JOIN saloes s ON s.usuario_id = u.id
        WHERE u.tipo = 'salao' 
        AND u.email = :email
        AND s.ativo = 1
    ");
    
    $stmt->execute(['email' => $email_teste]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "<p style='color: green;'>✅ Usuário encontrado: {$usuario['nome']}</p>";
        echo "<p>Salão: {$usuario['nome_fantasia']} (ID: {$usuario['salao_id']})</p>";
        
        if (password_verify($senha_teste, $usuario['senha'])) {
            echo "<p style='color: green;'>✅ Senha correta!</p>";
            echo "<p><strong>Credenciais de teste:</strong><br>";
            echo "Email: {$email_teste}<br>";
            echo "Senha: {$senha_teste}</p>";
        } else {
            echo "<p style='color: red;'>❌ Senha incorreta</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Usuário não encontrado ou salão inativo</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>