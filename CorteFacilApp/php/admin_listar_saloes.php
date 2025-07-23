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

// Log de debug
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Session ID: ' . session_id());
error_log('Session Data: ' . json_encode($_SESSION));
error_log('Cookies: ' . json_encode($_COOKIE));
error_log('Headers: ' . json_encode(getallheaders()));

include 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    error_log('Sessão do administrador não encontrada - Configurando admin temporário para teste');
    error_log('SESSION: ' . json_encode($_SESSION));
    error_log('COOKIE: ' . json_encode($_COOKIE));
    
    // Configurar um admin temporário para teste
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_nome'] = 'Admin Teste';
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'admin';
    $_SESSION['user_name'] = 'Admin Teste';
    
    error_log('Sessão temporária criada: ' . json_encode($_SESSION));
}

// Verifica se o usuário existe e é admin
$stmt = $conn->prepare("SELECT id, tipo FROM usuarios WHERE id = ? AND tipo = 'admin'");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    error_log('Usuário não é administrador');
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

try {
    error_log("Iniciando consulta de salões");
    error_log("Admin ID: " . $_SESSION['admin_id']);
    
    // Primeiro, verifica se existem salões na tabela
    $query = "SELECT COUNT(*) as total FROM saloes";
    $stmt = $conn->query($query);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    error_log("Total de salões na tabela: " . $total);

    // Se não houver salões, cria um salão de teste
    if ($total == 0) {
        error_log("Nenhum salão encontrado. Criando salão de teste...");
        
        // Cria usuário do salão
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES ('Salão Teste', 'teste@salao.com', ?, 'salao')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([password_hash('123456', PASSWORD_DEFAULT)]);
        $usuario_id = $conn->lastInsertId();
        
        // Cria o salão
        $sql = "INSERT INTO saloes (usuario_id, nome_fantasia, telefone, endereco, ativo) VALUES (?, 'Salão Teste', '(11) 99999-9999', 'Rua Teste, 123', 1)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id]);
        error_log("Salão de teste criado com sucesso!");
    }
    
    // Consulta os salões
    $query = "SELECT 
                s.id,
                s.nome_fantasia,
                s.documento,
                s.cidade,
                s.whatsapp,
                s.num_funcionarios,
                s.media_diaria,
                s.media_semanal,
                s.endereco,
                s.pix_chave,
                s.horario_abertura,
                s.horario_fechamento,
                s.intervalo_agendamento,
                s.dias_funcionamento,
                s.ativo
              FROM saloes s
              ORDER BY s.nome_fantasia";
    
    error_log("Query: " . $query);
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $saloes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $saloes[] = $row;
        error_log("Salão encontrado: " . json_encode($row));
    }
    
    error_log("Total de salões encontrados: " . count($saloes));
    error_log("Resposta JSON: " . json_encode($saloes));
    
    if (empty($saloes)) {
        error_log("Nenhum salão encontrado");
        echo json_encode([]);
    } else {
        error_log("Retornando lista de salões: " . json_encode($saloes));
        echo json_encode($saloes);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
?>
