<?php
session_start();
header('Content-Type: application/json');
require_once 'conexao.php';

// Verifica se o usuário está logado e é do tipo 'salao'
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado ou não é parceiro']);
    exit;
}

// Obtém o ID do usuário autenticado
$usuario_id = $_SESSION['id'];

try {
    $pdo = getConexao();
    
    // Verifica se todos os campos necessários foram enviados
    if (!isset($_POST['senha_atual']) || !isset($_POST['nova_senha']) || !isset($_POST['confirmar_senha'])) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
        exit;
    }
    
    $senhaAtual = $_POST['senha_atual'];
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];
    
    // Verifica se a nova senha e a confirmação são iguais
    if ($novaSenha !== $confirmarSenha) {
        echo json_encode(['success' => false, 'message' => 'A nova senha e a confirmação não coincidem']);
        exit;
    }
    
    // Verifica se a nova senha tem pelo menos 6 caracteres
    if (strlen($novaSenha) < 6) {
        echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres']);
        exit;
    }
    
    // Busca o usuário no banco de dados
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }
    
    // Verifica se a senha atual está correta
    if (!password_verify($senhaAtual, $usuario['senha'])) {
        // Teste adicional: verifica se a senha pode estar em texto plano (caso antigo)
        if ($senhaAtual === $usuario['senha']) {
            // Atualiza para hash antes de continuar
            $senha_hash_temp = password_hash($senhaAtual, PASSWORD_DEFAULT);
            $stmt_temp = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt_temp->execute([$senha_hash_temp, $usuario_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
            exit;
        }
    }
    
    // Criptografa a nova senha
    $senhaCriptografada = password_hash($novaSenha, PASSWORD_DEFAULT);
    
    // Atualiza a senha no banco de dados
    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt->execute([$senhaCriptografada, $usuario_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível alterar a senha']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha: ' . $e->getMessage()]);
}