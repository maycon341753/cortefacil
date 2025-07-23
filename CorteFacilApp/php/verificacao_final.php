<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    echo "=== VERIFICAÇÃO FINAL ===\n\n";
    
    // 1. Verificar funcionários
    echo "1. FUNCIONÁRIOS:\n";
    $stmt = $pdo->prepare("SELECT f.*, s.nome as nome_salao FROM funcionarios f LEFT JOIN saloes s ON f.salao_id = s.id WHERE f.ativo = 1");
    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($funcionarios as $func) {
        echo "   - {$func['nome']} (Salão: {$func['nome_salao']}) - ID: {$func['id']}\n";
    }
    
    // 2. Verificar profissionais
    echo "\n2. PROFISSIONAIS:\n";
    $stmt = $pdo->prepare("SELECT p.*, s.nome as nome_salao FROM profissionais p LEFT JOIN saloes s ON p.salao_id = s.id WHERE p.ativo = 1");
    $stmt->execute();
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($profissionais as $prof) {
        echo "   - {$prof['nome']} (Especialidade: {$prof['especialidade']}) (Salão: {$prof['nome_salao']}) - ID: {$prof['id']}\n";
    }
    
    // 3. Verificar serviços
    echo "\n3. SERVIÇOS:\n";
    $stmt = $pdo->prepare("SELECT s.*, sal.nome as nome_salao FROM servicos s LEFT JOIN saloes sal ON s.salao_id = sal.id WHERE s.ativo = 1 ORDER BY s.salao_id");
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($servicos as $serv) {
        echo "   - {$serv['nome']} (R$ {$serv['preco']}) - Salão: {$serv['nome_salao']}\n";
    }
    
    // 4. Verificar associações profissional-serviços
    echo "\n4. ASSOCIAÇÕES PROFISSIONAL-SERVIÇOS:\n";
    $stmt = $pdo->prepare("
        SELECT p.nome as profissional, s.nome as servico, sal.nome as nome_salao 
        FROM profissional_servicos ps 
        JOIN profissionais p ON ps.profissional_id = p.id 
        JOIN servicos s ON ps.servico_id = s.id 
        JOIN saloes sal ON p.salao_id = sal.id
        ORDER BY sal.nome, p.nome
    ");
    $stmt->execute();
    $associacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($associacoes as $assoc) {
        echo "   - {$assoc['profissional']} pode fazer: {$assoc['servico']} (Salão: {$assoc['nome_salao']})\n";
    }
    
    echo "\n=== VERIFICAÇÃO CONCLUÍDA ===\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>