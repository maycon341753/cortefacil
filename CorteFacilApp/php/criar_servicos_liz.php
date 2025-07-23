<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    echo "Criando serviços para o salão da Liz Hadassa (ID 4)...\n";
    
    $servicos = [
        ['nome' => 'Corte Masculino', 'preco' => 25.00, 'duracao' => 30],
        ['nome' => 'Corte Feminino', 'preco' => 35.00, 'duracao' => 45],
        ['nome' => 'Barba', 'preco' => 15.00, 'duracao' => 20],
        ['nome' => 'Coloração', 'preco' => 80.00, 'duracao' => 120],
        ['nome' => 'Escova', 'preco' => 30.00, 'duracao' => 40]
    ];
    
    foreach ($servicos as $servico) {
        $stmt = $pdo->prepare("INSERT INTO servicos (nome, preco, duracao_minutos, salao_id, ativo) VALUES (?, ?, ?, 4, 1)");
        $stmt->execute([$servico['nome'], $servico['preco'], $servico['duracao']]);
        echo "Serviço '{$servico['nome']}' criado!\n";
    }
    
    echo "Todos os serviços foram criados com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>