<?php
header('Content-Type: application/json');
require_once 'conexao.php';
require_once 'autenticacao.php';

// Verifica se o parceiro está autenticado
if (!verificarAutenticacao() || !verificarTipoUsuario('parceiro')) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não autenticado ou não é parceiro']);
    exit;
}

// Obtém o ID do usuário autenticado
$usuario_id = $_SESSION['usuario_id'];

try {
    // Verifica se todos os campos necessários foram enviados
    if (!isset($_POST['senha_atual']) || !isset($_POST['nova_senha']) || !isset($_POST['confirmar_senha'])) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios']);
        exit;
    }
    
    $senhaAtual = $_POST['senha_atual'];
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];
    
    // Verifica se a nova senha e a confirmação são iguais
    if ($novaSenha !== $confirmarSenha) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'A nova senha e a confirmação não coincidem']);
        exit;
    }
    
    // Verifica se a senha atual está correta
    $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não encontrado']);
        exit;
    }
    
    $usuario = $resultado->fetch_assoc();
    
    // Verifica se a senha atual está correta
    if (!password_verify($senhaAtual, $usuario['senha'])) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Senha atual incorreta']);
        exit;
    }
    
    // Criptografa a nova senha
    $senhaCriptografada = password_hash($novaSenha, PASSWORD_DEFAULT);
    
    // Atualiza a senha no banco de dados
    $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $senhaCriptografada, $usuario_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Senha alterada com sucesso', 'nova_senha' => $novaSenha]);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível alterar a senha']);
    }
    
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao alterar senha: ' . $e->getMessage()]);
}