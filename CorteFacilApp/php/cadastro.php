<?php
header('Content-Type: application/json; charset=utf-8');

// Ativa a exibição de erros no output
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar log de erros
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/error.log');

error_log("=== Iniciando nova requisição de cadastro ====");
error_log("Método da requisição: " . $_SERVER['REQUEST_METHOD']);
error_log("Headers recebidos: " . print_r(getallheaders(), true));

require_once __DIR__ . '/conexao.php';
error_log("Arquivo de conexão incluído");

try {
    error_log("Iniciando processamento da requisição");
    error_log("Content-Type: " . $_SERVER['CONTENT_TYPE']);
    
    // Receber e decodificar dados JSON
    $raw_data = file_get_contents('php://input');
    if ($raw_data === false) {
        error_log("Erro ao ler dados do input");
        throw new Exception('Erro ao ler dados do formulário');
    }
    error_log("Dados brutos recebidos: " . $raw_data);
    
    $dados = json_decode($raw_data, true);
    if ($dados === null) {
        error_log("Erro na decodificação JSON: " . json_last_error_msg());
        error_log("JSON inválido recebido: " . $raw_data);
        throw new Exception('Erro ao processar dados do formulário: ' . json_last_error_msg());
    }
    error_log("Dados decodificados com sucesso: " . print_r($dados, true));
    
    // Validar e sanitizar inputs
    $nome = filter_var($dados['nome'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var($dados['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $cpf = filter_var($dados['cpf'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone = filter_var($dados['telefone'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $data_nascimento = filter_var($dados['data_nascimento'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = filter_var($dados['senha'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Validações básicas
    if (empty($nome) || empty($email) || empty($cpf) || empty($telefone) || empty($data_nascimento) || empty($senha)) {
        throw new Exception('Todos os campos são obrigatórios');
    }
    
    // Validar formato do e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('E-mail inválido');
    }
    
    // Limpar CPF
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Validar CPF
    if (strlen($cpf) !== 11) {
        throw new Exception('CPF inválido');
    }
    
    // Validar formato da data
    $data = DateTime::createFromFormat('Y-m-d', $data_nascimento);
    if (!$data || $data->format('Y-m-d') !== $data_nascimento) {
        throw new Exception('Data de nascimento inválida');
    }
    
    // Validar idade mínima (16 anos)
    $hoje = new DateTime();
    $idade = $hoje->diff($data)->y;
    if ($idade < 16) {
        throw new Exception('É necessário ter pelo menos 16 anos para se cadastrar');
    }
    
    // Limpar telefone
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Validar comprimento do telefone
    if (strlen($telefone) < 10 || strlen($telefone) > 11) {
        throw new Exception('Número de telefone inválido');
    }
    
    // Validar força da senha
    if (strlen($senha) < 6) {
        throw new Exception('A senha deve ter pelo menos 6 caracteres');
    }
    
    error_log("Tentando obter conexão com o banco de dados");
    $pdo = getConexao();
    error_log("Conexão obtida com sucesso");
    
    // Verificar se CPF já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmt->execute([$cpf]);
    if ($stmt->fetch()) {
        throw new Exception('CPF já cadastrado');
    }
    
    // Verificar se e-mail já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('E-mail já cadastrado');
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir usuário
        $sql = "INSERT INTO usuarios (nome, email, cpf, telefone, data_nascimento, senha, tipo, ativo, data_cadastro) 
                VALUES (?, ?, ?, ?, ?, ?, 'cliente', 1, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome,
            $email,
            $cpf,
            $telefone,
            $data_nascimento,
            $senha_hash
        ]);
        
        $usuario_id = $pdo->lastInsertId();
        
        // Registrar no log de atividades
        $sql = "INSERT INTO log_atividades (usuario_id, acao, ip) VALUES (?, 'cadastro', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $usuario_id,
            $_SERVER['REMOTE_ADDR']
        ]);
        
        // Commit da transação
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'mensagem' => 'Cadastro realizado com sucesso! Redirecionando para o login...'
        ]);
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Erro de exceção: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensagem' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log('Erro PDO no cadastro: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Erro ao realizar cadastro. Por favor, tente novamente mais tarde.'
    ]);
} catch (Throwable $e) {
    error_log('Erro não tratado: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Erro interno do servidor. Por favor, tente novamente mais tarde.'
    ]);
}
?>
