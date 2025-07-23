<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    // Buscar um salão para associar o funcionário
    $stmt = $conn->prepare("SELECT id FROM saloes LIMIT 1");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "Nenhum salão encontrado. Criando salão de teste...\n";
        
        // Criar usuário para o salão
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Salão Teste', 'salao.teste@email.com', password_hash('123456', PASSWORD_DEFAULT), 'salao']);
        $usuario_id = $conn->lastInsertId();
        
        // Criar salão
        $stmt = $conn->prepare("INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Salão Teste', '12345678901234', 'São Paulo', 'Rua Teste, 123', '11999999999', $usuario_id]);
        $salao_id = $conn->lastInsertId();
    } else {
        $salao_id = $salao['id'];
    }
    
    // Criar funcionário de teste
    $stmt = $conn->prepare("INSERT INTO funcionarios (nome, email, senha, telefone, salao_id, especialidade, valor_servico, horario_trabalho_inicio, horario_trabalho_fim, dias_trabalho) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'João Silva',
        'joao.silva@email.com',
        password_hash('123456', PASSWORD_DEFAULT),
        '11987654321',
        $salao_id,
        'Corte Masculino e Barba',
        35.00,
        '09:00',
        '18:00',
        '1,2,3,4,5,6'
    ]);
    
    echo "Funcionário de teste criado com sucesso!\n";
    echo "Nome: João Silva\n";
    echo "Email: joao.silva@email.com\n";
    echo "Especialidade: Corte Masculino e Barba\n";
    echo "Valor do Serviço: R$ 35,00\n";
    
    // Verificar se foi criado
    $stmt = $conn->prepare("SELECT * FROM funcionarios WHERE email = ?");
    $stmt->execute(['joao.silva@email.com']);
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($funcionario) {
        echo "\nFuncionário encontrado no banco:\n";
        echo "ID: " . $funcionario['id'] . "\n";
        echo "Nome: " . $funcionario['nome'] . "\n";
        echo "Especialidade: " . $funcionario['especialidade'] . "\n";
        echo "Valor do Serviço: R$ " . number_format($funcionario['valor_servico'], 2, ',', '.') . "\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>