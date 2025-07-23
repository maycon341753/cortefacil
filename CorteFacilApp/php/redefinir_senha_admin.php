<?php
include 'conexao.php';

header('Content-Type: application/json');

// Credenciais do administrador
$email = 'admin@cortefacil.com';
$nova_senha = 'admin123';
$hash_senha = password_hash($nova_senha, PASSWORD_DEFAULT);

try {
    // Atualiza a senha do administrador
    $stmt = $conn->prepare("UPDATE usuarios SET senha = :senha WHERE email = :email AND tipo = 'admin'");
    $resultado = $stmt->execute([
        ':senha' => $hash_senha,
        ':email' => $email
    ]);
    
    if ($resultado) {
        echo json_encode([
            'status' => 'ok',
            'mensagem' => 'Senha do administrador atualizada com sucesso',
            'email' => $email,
            'nova_senha' => $nova_senha,
            'hash' => $hash_senha
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Não foi possível atualizar a senha do administrador'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao redefinir senha: ' . $e->getMessage()
    ]);
}
?>