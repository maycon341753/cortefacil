<?php
// Teste final da API de metas
session_start();
$_SESSION['salao_id'] = 4; // ID do salão Liz Hadassa

// Simula a chamada da API
include 'salao_obter_metas.php';
?>