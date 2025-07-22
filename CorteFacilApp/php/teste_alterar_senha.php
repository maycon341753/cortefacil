<?php
session_start();
require_once 'conexao.php';

// Simular autenticação para teste
$_SESSION['usuario_id'] = 1; // Substitua pelo ID de um usuário parceiro existente
$_SESSION['tipo_usuario'] = 'parceiro';

// Verificar se o usuário existe e obter a senha atual
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuário não encontrado";
    exit;
}

$usuario = $resultado->fetch_assoc();
echo "Senha atual (hash): " . $usuario['senha'] . "<br>";

// Verificar se a senha atual está correta (para teste)
$senhaAtual = 'senha123'; // Substitua pela senha atual do usuário
if (password_verify($senhaAtual, $usuario['senha'])) {
    echo "Senha atual está correta<br>";
} else {
    echo "Senha atual está incorreta<br>";
}

// Testar a atualização da senha
$novaSenha = 'novaSenha123';
$senhaCriptografada = password_hash($novaSenha, PASSWORD_DEFAULT);

echo "Nova senha (texto): " . $novaSenha . "<br>";
echo "Nova senha (hash): " . $senhaCriptografada . "<br>";

// Comentado para não alterar a senha durante o teste
/*
$stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
$stmt->bind_param("si", $senhaCriptografada, $usuario_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Senha alterada com sucesso";
} else {
    echo "Não foi possível alterar a senha";
}
*/