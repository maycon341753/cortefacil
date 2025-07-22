<?php
session_start();

// Simular login de parceiro
$_SESSION['id'] = 1;
$_SESSION['tipo'] = 'salao';
$_SESSION['salao_id'] = 1;
$_SESSION['nome'] = 'Salão Teste';

// Redirecionar para a página de funcionários
header('Location: ../salao/funcionarios.php');
exit;
?>