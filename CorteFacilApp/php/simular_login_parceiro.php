<?php
// Iniciar a sessão antes de qualquer saída
session_start();

// Definir o cookie da sessão para ser acessível em todo o domínio
ini_set('session.cookie_domain', '.localhost');

// Simular login de parceiro
$_SESSION['id'] = 1;
$_SESSION['tipo'] = 'salao';
$_SESSION['salao_id'] = 1;
$_SESSION['nome'] = 'Salão Teste';

// Forçar a escrita da sessão
session_write_close();

// Exibir informações da sessão
echo "Login simulado com sucesso!";
echo "<br>ID: " . $_SESSION['id'];
echo "<br>Tipo: " . $_SESSION['tipo'];
echo "<br>Salão ID: " . $_SESSION['salao_id'];
echo "<br>Nome: " . $_SESSION['nome'];
echo "<br>Session ID: " . session_id();

// Adicionar link para testar a listagem de profissionais
echo "<br><br><a href='parceiro_listar_profissionais.php'>Testar listagem de profissionais</a>";
?>