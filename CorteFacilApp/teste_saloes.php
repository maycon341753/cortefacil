<?php
require_once 'php/conexao.php';

header('Content-Type: application/json');

try {
    $pdo = getConexao();
    
    // Verificar se há dados na tabela salões
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM saloes");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total de salões na base de dados: " . $count['total'] . "\n\n";
    
    // Listar todos os salões
    $stmt = $pdo->prepare("SELECT * FROM saloes");
    $stmt->execute();
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Dados dos salões:\n";
    foreach ($saloes as $salao) {
        echo "ID: " . $salao['id'] . "\n";
        echo "Nome: " . $salao['nome_fantasia'] . "\n";
        echo "Cidade: " . $salao['cidade'] . "\n";
        echo "Ativo: " . ($salao['ativo'] ? 'Sim' : 'Não') . "\n";
        echo "---\n";
    }
    
    // Testar a consulta do listar_saloes.php
    echo "\nTestando consulta do listar_saloes.php:\n";
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.nome_fantasia as nome,
            s.cidade,
            s.horario_abertura,
            s.horario_fechamento,
            s.dias_funcionamento,
            s.intervalo_agendamento,
            COALESCE(AVG(a.nota), 0) as avaliacao
        FROM saloes s
        LEFT JOIN avaliacoes a ON a.salao_id = s.id
        WHERE s.ativo = 1
        GROUP BY s.id
        ORDER BY s.nome_fantasia
    ");
    
    $stmt->execute();
    $saloes_consulta = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Resultado da consulta: " . count($saloes_consulta) . " salões encontrados\n";
    print_r($saloes_consulta);
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>