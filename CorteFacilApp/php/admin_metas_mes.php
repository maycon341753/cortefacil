<?php
session_start();
require_once 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    // Tenta fazer login automático para teste
    include_once 'admin_login_temp.php';
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode([]);
        exit;
    }
}

try {
    $conn = getConexao();
    $mes_atual = date('Y-m');
    
    // Busca os salões que fizeram mais de 5 cortes no mês atual
    $query = "SELECT 
                s.nome_fantasia as salao_nome,
                COUNT(a.id) as total_cortes
              FROM saloes s
              LEFT JOIN agendamentos a ON s.id = a.salao_id 
                AND DATE_FORMAT(a.data, '%Y-%m') = ?
                AND a.status IN ('realizado', 'confirmado')
              GROUP BY s.id, s.nome_fantasia
              HAVING total_cortes >= 5
              ORDER BY total_cortes DESC
              LIMIT 3";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$mes_atual]);
    
    $saloes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $saloes[] = [
            'salao_nome' => $row['salao_nome'],
            'total_cortes' => $row['total_cortes']
        ];
    }
    
    // Retorna sempre um array, mesmo que vazio
    echo json_encode($saloes);

} catch (Exception $e) {
    // Em caso de erro, retorna um array vazio para não quebrar o frontend
    echo json_encode([]);
}
?>