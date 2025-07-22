<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexao.php';

try {
    $conn = getConexao();
    $salao_id = 5; // ID do salão que queremos excluir
    
    // Inicia a transação
    $conn->beginTransaction();
    
    // Obtém o ID do usuário associado ao salão
    $stmt = $conn->prepare("SELECT usuario_id FROM saloes WHERE id = :id");
    $stmt->execute(['id' => $salao_id]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        throw new Exception('Salão não encontrado');
    }
    
    $usuario_id = $salao['usuario_id'];
    
    // Exclui registros relacionados
    $tables = ['avaliacoes', 'agendamentos', 'promocoes', 'servicos', 'profissionais', 'metas'];
    
    foreach ($tables as $table) {
        $stmt = $conn->prepare("DELETE FROM {$table} WHERE salao_id = :salao_id");
        $stmt->execute(['salao_id' => $salao_id]);
        echo "Registros excluídos da tabela {$table}<br>";
    }
    
    // Exclui o salão
    $stmt = $conn->prepare("DELETE FROM saloes WHERE id = :id");
    $stmt->execute(['id' => $salao_id]);
    echo "Salão excluído<br>";
    
    // Exclui o usuário associado
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id AND tipo = 'salao'");
    $stmt->execute(['id' => $usuario_id]);
    echo "Usuário associado excluído<br>";
    
    // Confirma a transação
    $conn->commit();
    
    echo "<h2>Exclusão concluída com sucesso!</h2>";
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<h2>Erro ao excluir:</h2> " . $e->getMessage();
}
?>