<?php
include 'conexao.php';

header('Content-Type: application/json');

// Credenciais do administrador
$email = 'admin@cortefacil.com';
$senha = 'admin123'; // Senha de teste

try {
    // Verifica se o usuário existe na tabela usuarios com tipo 'admin'
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email AND tipo = 'admin'");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo json_encode([
            'status' => 'ok',
            'mensagem' => 'Administrador encontrado',
            'admin' => [
                'id' => $admin['id'],
                'nome' => $admin['nome'],
                'hash_senha' => $admin['senha']
            ],
            'senha_teste' => $senha,
            'verificacao' => password_verify($senha, $admin['senha'])
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Administrador não encontrado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao testar login: ' . $e->getMessage()
    ]);
}
?>