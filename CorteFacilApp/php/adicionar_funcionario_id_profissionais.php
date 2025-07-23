<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    // Verificar se a coluna funcionario_id já existe
    $stmt = $pdo->prepare("DESCRIBE profissionais");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $funcionario_id_existe = false;
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'funcionario_id') {
            $funcionario_id_existe = true;
            break;
        }
    }
    
    if (!$funcionario_id_existe) {
        echo "Adicionando coluna funcionario_id na tabela profissionais...\n";
        $pdo->exec("ALTER TABLE profissionais ADD COLUMN funcionario_id INT(11) NULL");
        echo "Coluna funcionario_id adicionada com sucesso!\n";
    } else {
        echo "Coluna funcionario_id já existe na tabela profissionais.\n";
    }
    
    // Mostrar estrutura final
    echo "\nEstrutura atual da tabela profissionais:\n";
    $stmt = $pdo->prepare("DESCRIBE profissionais");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($colunas as $coluna) {
        echo "- {$coluna['Field']} ({$coluna['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>