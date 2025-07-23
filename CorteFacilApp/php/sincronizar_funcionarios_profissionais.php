<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    echo "Sincronizando funcionários existentes com a tabela profissionais...\n";
    
    // Buscar todos os funcionários ativos
    $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE ativo = 1");
    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sincronizados = 0;
    
    foreach ($funcionarios as $funcionario) {
        // Verificar se já existe um profissional para este funcionário
        $stmt = $pdo->prepare("SELECT id FROM profissionais WHERE funcionario_id = ?");
        $stmt->execute([$funcionario['id']]);
        $profissional_existente = $stmt->fetch();
        
        if (!$profissional_existente) {
            // Inserir novo profissional
            $sql = "INSERT INTO profissionais (nome, salao_id, especialidade, telefone, ativo, funcionario_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $funcionario['nome'],
                $funcionario['salao_id'],
                $funcionario['especialidade'],
                $funcionario['telefone'],
                $funcionario['ativo'],
                $funcionario['id']
            ]);
            
            echo "Funcionário '{$funcionario['nome']}' sincronizado como profissional.\n";
            $sincronizados++;
        } else {
            echo "Funcionário '{$funcionario['nome']}' já possui profissional correspondente.\n";
        }
    }
    
    echo "\nSincronização concluída! {$sincronizados} funcionários foram sincronizados.\n";
    
    // Mostrar profissionais disponíveis
    echo "\nProfissionais disponíveis:\n";
    $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE ativo = 1 ORDER BY salao_id, nome");
    $stmt->execute();
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($profissionais as $prof) {
        echo "- {$prof['nome']} ({$prof['especialidade']}) - Salão ID: {$prof['salao_id']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>