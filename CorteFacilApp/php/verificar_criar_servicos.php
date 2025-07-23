<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    echo "Verificando serviços disponíveis...\n";
    
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE ativo = 1 ORDER BY salao_id");
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Serviços disponíveis:\n";
    foreach ($servicos as $serv) {
        echo "- {$serv['nome']} (R$ {$serv['preco']}) - Salão ID: {$serv['salao_id']}\n";
    }
    
    if (empty($servicos)) {
        echo "Nenhum serviço encontrado!\n";
        echo "Criando serviços básicos para o salão ID 4 (Liz Hadassa Maité Silva)...\n";
        
        // Criar serviços básicos
        $servicos_basicos = [
            ['nome' => 'Corte Masculino', 'preco' => 25.00, 'duracao' => 30],
            ['nome' => 'Corte Feminino', 'preco' => 35.00, 'duracao' => 45],
            ['nome' => 'Barba', 'preco' => 15.00, 'duracao' => 20],
            ['nome' => 'Coloração', 'preco' => 80.00, 'duracao' => 120],
            ['nome' => 'Escova', 'preco' => 30.00, 'duracao' => 40]
        ];
        
        foreach ($servicos_basicos as $servico) {
            $stmt = $pdo->prepare("INSERT INTO servicos (nome, preco, duracao_minutos, salao_id, ativo) VALUES (?, ?, ?, 4, 1)");
            $stmt->execute([$servico['nome'], $servico['preco'], $servico['duracao']]);
            echo "Serviço '{$servico['nome']}' criado!\n";
        }
        
        echo "Serviços criados com sucesso!\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>