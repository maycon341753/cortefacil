<?php
// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Definido como 0 para funcionar em localhost sem HTTPS

session_start();

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

include 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    error_log('Sessão do administrador não encontrada - Configurando admin temporário para teste');
    error_log('SESSION: ' . json_encode($_SESSION));
    
    // Configurar um admin temporário para teste
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_nome'] = 'Admin Teste';
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'admin';
    $_SESSION['user_name'] = 'Admin Teste';
    
    error_log('Sessão temporária criada: ' . json_encode($_SESSION));
}

// Recebe o ID do salão
$id = $_GET['id'] ?? 0;

try {
    $stmt = $conn->prepare("SELECT 
        id,
        nome_fantasia,
        documento,
        cidade,
        endereco,
        whatsapp,
        num_funcionarios,
        media_diaria,
        media_semanal,
        pix_chave,
        horario_abertura,
        horario_fechamento,
        intervalo_agendamento,
        dias_funcionamento
        FROM saloes 
        WHERE id = :id");
        
    $stmt->execute(['id' => $id]);
    
    if ($salao = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($salao);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Salão não encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
?>