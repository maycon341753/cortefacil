<?php
require_once 'php/conexao.php';

try {
    $pdo = getConexao();
    
    // Verificar se já existe o usuário do salão
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = 'salao@exemplo.com'");
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        // Inserir usuário do salão
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, cpf, data_nascimento) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Salão Exemplo',
            'salao@exemplo.com',
            password_hash('123456', PASSWORD_DEFAULT),
            'salao',
            '12345678901',
            '1990-01-01'
        ]);
        $usuario_id = $pdo->lastInsertId();
        echo "Usuário do salão criado com ID: $usuario_id\n";
    } else {
        $usuario_id = $usuario['id'];
        echo "Usuário do salão já existe com ID: $usuario_id\n";
    }
    
    // Verificar se já existe o salão
    $stmt = $pdo->prepare("SELECT id FROM saloes WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        // Inserir salão
        $stmt = $pdo->prepare("INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, num_funcionarios, media_diaria, media_semanal, pix_chave, usuario_id, ativo, horario_abertura, horario_fechamento, intervalo_agendamento, dias_funcionamento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Salão Exemplo',
            '12345678901234',
            'São Paulo',
            'Rua Teste, 123',
            '11999999999',
            10,
            50,
            350,
            '12345678901',
            $usuario_id,
            1,
            '09:00:00',
            '18:00:00',
            30,
            '1,2,3,4,5,6'
        ]);
        $salao_id = $pdo->lastInsertId();
        echo "Salão criado com ID: $salao_id\n";
    } else {
        $salao_id = $salao['id'];
        echo "Salão já existe com ID: $salao_id\n";
    }
    
    // Inserir profissionais se não existirem
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM profissionais WHERE salao_id = ?");
    $stmt->execute([$salao_id]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['total'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO profissionais (nome, salao_id, especialidade, telefone, ativo) VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute(['João Silva', $salao_id, 'Corte Masculino', '11988888888', 1]);
        echo "Profissional João Silva criado\n";
        
        $stmt->execute(['Maria Oliveira', $salao_id, 'Corte Feminino', '11977777777', 1]);
        echo "Profissional Maria Oliveira criada\n";
    } else {
        echo "Profissionais já existem: " . $count['total'] . "\n";
    }
    
    // Inserir serviços se não existirem
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM servicos WHERE salao_id = ?");
    $stmt->execute([$salao_id]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['total'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO servicos (salao_id, nome, preco, duracao_minutos, ativo) VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([$salao_id, 'Corte Masculino', 35.00, 30, 1]);
        echo "Serviço Corte Masculino criado\n";
        
        $stmt->execute([$salao_id, 'Corte Feminino', 50.00, 60, 1]);
        echo "Serviço Corte Feminino criado\n";
        
        $stmt->execute([$salao_id, 'Barba', 25.00, 30, 1]);
        echo "Serviço Barba criado\n";
    } else {
        echo "Serviços já existem: " . $count['total'] . "\n";
    }
    
    echo "\nDados inseridos com sucesso!\n";
    echo "Agora teste novamente o sistema.";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>