<?php
require_once 'conexao.php';
require_once 'verificar_sessao.php';

header('Content-Type: application/json');

try {
    // Consulta para obter todas as categorias distintas de serviços
    $sql = "SELECT DISTINCT categoria FROM servicos WHERE ativo = 1 ORDER BY categoria";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $categorias = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['categoria'])) {
            $categorias[] = $row['categoria'];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'categorias' => $categorias
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao carregar categorias: ' . $e->getMessage()
    ]);
}
?>