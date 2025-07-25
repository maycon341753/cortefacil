<?php
require_once 'conexao.php';
require_once 'verificar_autenticacao.php';

// Verifica se o usuário está autenticado e é do tipo 'salao'
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    http_response_code(403);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso não autorizado']);
    exit;
}

// Obtém a conexão com o banco de dados
$conn = getConexao();

// Função para validar os dados do funcionário
function validarDadosFuncionario($dados) {
    $erros = [];
    
    if (empty($dados['nome'])) {
        $erros[] = 'Nome é obrigatório';
    }
    
    if (empty($dados['email'])) {
        $erros[] = 'Email é obrigatório';
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido';
    }
    
    if (empty($dados['senha']) && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
        $erros[] = 'Senha é obrigatória';
    }
    
    return $erros;
}

// Função para sincronizar funcionário com a tabela profissionais
function sincronizarProfissional($conn, $funcionario_id, $acao = 'inserir') {
    try {
        if ($acao === 'inserir' || $acao === 'atualizar') {
            // Busca os dados do funcionário
            $stmt = $conn->prepare("SELECT * FROM funcionarios WHERE id = ?");
            $stmt->execute([$funcionario_id]);
            $funcionario = $stmt->fetch();
            
            if (!$funcionario) {
                return false;
            }
            
            // Verifica se já existe um profissional para este funcionário
            $stmt = $conn->prepare("SELECT id FROM profissionais WHERE funcionario_id = ?");
            $stmt->execute([$funcionario_id]);
            $profissional_existente = $stmt->fetch();
            
            if ($profissional_existente) {
                // Atualiza o profissional existente
                $sql = "UPDATE profissionais SET 
                        nome = ?, 
                        telefone = ?, 
                        ativo = ? 
                        WHERE funcionario_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $funcionario['nome'],
                    $funcionario['telefone'],
                    $funcionario['ativo'] ?? 1,
                    $funcionario_id
                ]);
            } else {
                // Insere novo profissional
                $sql = "INSERT INTO profissionais (nome, salao_id, telefone, ativo, funcionario_id) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $funcionario['nome'],
                    $_SESSION['salao_id'],
                    $funcionario['telefone'],
                    $funcionario['ativo'] ?? 1,
                    $funcionario_id
                ]);
            }
        } elseif ($acao === 'excluir') {
            // Remove o profissional correspondente
            $stmt = $conn->prepare("DELETE FROM profissionais WHERE funcionario_id = ?");
            $stmt->execute([$funcionario_id]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Erro ao sincronizar profissional: " . $e->getMessage());
        return false;
    }
}

// Rota para listar funcionários
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $salao_id = $_SESSION['salao_id'];
        
        $sql = "SELECT id, nome, email, telefone, horario_trabalho_inicio, horario_trabalho_fim, dias_trabalho, ativo 
                FROM funcionarios 
                WHERE salao_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$salao_id]);
        
        $funcionarios = $stmt->fetchAll();
        
        echo json_encode(['status' => 'sucesso', 'funcionarios' => $funcionarios]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao listar funcionários: ' . $e->getMessage()]);
    }
}

// Rota para cadastrar ou atualizar funcionário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dados = json_decode(file_get_contents('php://input'), true);
        $acao = $dados['acao'] ?? 'cadastrar';
        
        // Validação dos dados
        $erros = validarDadosFuncionario($dados);
        if (!empty($erros)) {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos', 'erros' => $erros]);
            exit;
        }
        
        if ($acao === 'cadastrar') {
            // Verifica se o email já está em uso
            $stmt = $conn->prepare("SELECT id FROM funcionarios WHERE email = ?");
            $stmt->execute([$dados['email']]);
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['status' => 'erro', 'mensagem' => 'Email já cadastrado']);
                exit;
            }
            
            $sql = "INSERT INTO funcionarios (nome, email, senha, telefone, salao_id, horario_trabalho_inicio, horario_trabalho_fim, dias_trabalho, ativo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $dados['nome'],
                $dados['email'],
                password_hash($dados['senha'], PASSWORD_DEFAULT),
                $dados['telefone'] ?? null,
                $_SESSION['salao_id'],
                $dados['horario_trabalho_inicio'] ?? '09:00',
                $dados['horario_trabalho_fim'] ?? '18:00',
                $dados['dias_trabalho'] ?? '1,2,3,4,5,6',
                $dados['ativo'] ?? 1
            ]);
            
            // Obter o ID do funcionário recém-criado
            $funcionario_id = $conn->lastInsertId();
            
            // Sincronizar com a tabela profissionais
            sincronizarProfissional($conn, $funcionario_id, 'inserir');
            
            echo json_encode(['status' => 'sucesso', 'mensagem' => 'Funcionário cadastrado com sucesso']);
        } else if ($acao === 'atualizar' && isset($dados['id'])) {
            // Verifica se o funcionário pertence ao salão
            $stmt = $conn->prepare("SELECT id FROM funcionarios WHERE id = ? AND salao_id = ?");
            $stmt->execute([$dados['id'], $_SESSION['salao_id']]);
            if ($stmt->rowCount() === 0) {
                http_response_code(403);
                echo json_encode(['status' => 'erro', 'mensagem' => 'Funcionário não encontrado']);
                exit;
            }
            
            $campos = [];
            $valores = [];
            
            if (isset($dados['nome'])) {
                $campos[] = 'nome = ?';
                $valores[] = $dados['nome'];
            }
            
            if (isset($dados['email'])) {
                $campos[] = 'email = ?';
                $valores[] = $dados['email'];
            }
            
            if (!empty($dados['senha'])) {
                $campos[] = 'senha = ?';
                $valores[] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            }
            
            if (isset($dados['telefone'])) {
                $campos[] = 'telefone = ?';
                $valores[] = $dados['telefone'];
            }
            
            if (isset($dados['ativo'])) {
                $campos[] = 'ativo = ?';
                $valores[] = $dados['ativo'];
            }
            
            if (isset($dados['horario_trabalho_inicio'])) {
                $campos[] = 'horario_trabalho_inicio = ?';
                $valores[] = $dados['horario_trabalho_inicio'];
            }
            
            if (isset($dados['horario_trabalho_fim'])) {
                $campos[] = 'horario_trabalho_fim = ?';
                $valores[] = $dados['horario_trabalho_fim'];
            }
            
            if (isset($dados['dias_trabalho'])) {
                $campos[] = 'dias_trabalho = ?';
                $valores[] = $dados['dias_trabalho'];
            }
            
            if (!empty($campos)) {
                $valores[] = $dados['id'];
                $sql = "UPDATE funcionarios SET " . implode(', ', $campos) . " WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute($valores);
                
                // Sincronizar com a tabela profissionais
                sincronizarProfissional($conn, $dados['id'], 'atualizar');
                
                echo json_encode(['status' => 'sucesso', 'mensagem' => 'Funcionário atualizado com sucesso']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'erro', 'mensagem' => 'Nenhum dado para atualizar']);
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao processar requisição: ' . $e->getMessage()]);
    }
}

// Rota para excluir funcionário
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $dados = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($dados['id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'erro', 'mensagem' => 'ID do funcionário não fornecido']);
            exit;
        }
        
        // Verifica se o funcionário pertence ao salão
        $stmt = $conn->prepare("SELECT id FROM funcionarios WHERE id = ? AND salao_id = ?");
        $stmt->execute([$dados['id'], $_SESSION['salao_id']]);
        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Funcionário não encontrado']);
            exit;
        }
        
        $sql = "DELETE FROM funcionarios WHERE id = ? AND salao_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$dados['id'], $_SESSION['salao_id']]);
        
        // Sincronizar com a tabela profissionais (remover)
        sincronizarProfissional($conn, $dados['id'], 'excluir');
        
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Funcionário excluído com sucesso']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir funcionário: ' . $e->getMessage()]);
    }
}
?>