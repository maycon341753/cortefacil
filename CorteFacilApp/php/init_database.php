<?php
require_once 'conexao.php';

try {
    // Inserir usuário administrador
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Administrador', 'admin@cortefacil.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    $adminId = $conn->lastInsertId();
    echo "Admin criado com sucesso (ID: $adminId)\n";

    // Inserir usuário do salão
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo, cpf, data_nascimento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Salão Barbearia Moderna', 'salao@exemplo.com', password_hash('salao123', PASSWORD_DEFAULT), 'salao', '12345678901', '1990-01-01']);
    $salaoUserId = $conn->lastInsertId();
    echo "Usuário do salão criado com sucesso (ID: $salaoUserId)\n";

    // Inserir usuário cliente
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo, cpf, data_nascimento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Cliente Teste', 'cliente@teste.com', password_hash('cliente123', PASSWORD_DEFAULT), 'cliente', '98765432109', '1995-05-15']);
    $clienteId = $conn->lastInsertId();
    echo "Cliente criado com sucesso (ID: $clienteId)\n";

    // Inserir salão
    $stmt = $conn->prepare("
        INSERT INTO saloes (
            nome_fantasia, 
            documento, 
            cidade, 
            endereco, 
            whatsapp, 
            num_funcionarios, 
            media_diaria, 
            media_semanal, 
            pix_chave, 
            usuario_id,
            horario_abertura,
            horario_fechamento,
            intervalo_agendamento,
            dias_funcionamento
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'Barbearia Moderna',
        '12345678901234',
        'São Paulo',
        'Rua Teste, 123',
        '11999999999',
        5,
        20,
        120,
        '05286558178',
        $salaoUserId,
        '09:00',
        '18:00',
        30,
        '1,2,3,4,5,6'
    ]);
    $salaoId = $conn->lastInsertId();
    echo "Salão criado com sucesso (ID: $salaoId)\n";

    // Inserir profissionais
    $stmt = $conn->prepare("INSERT INTO profissionais (nome, salao_id, especialidade, telefone) VALUES (?, ?, ?, ?)");
    $stmt->execute(['João Silva', $salaoId, 'Corte Masculino', '11988888888']);
    $stmt->execute(['Maria Oliveira', $salaoId, 'Corte Feminino', '11977777777']);
    echo "Profissionais criados com sucesso\n";

    // Inserir serviços
    $stmt = $conn->prepare("INSERT INTO servicos (salao_id, nome, preco, duracao_minutos) VALUES (?, ?, ?, ?)");
    $stmt->execute([$salaoId, 'Corte Masculino', 35.00, 30]);
    $stmt->execute([$salaoId, 'Corte Feminino', 50.00, 60]);
    $stmt->execute([$salaoId, 'Barba', 25.00, 30]);
    echo "Serviços criados com sucesso\n";

    echo "\nBanco de dados inicializado com sucesso!\n";
    echo "\nCredenciais de acesso:\n";
    echo "Admin: admin@cortefacil.com / admin123\n";
    echo "Salão: salao@exemplo.com / salao123\n";
    echo "Cliente: cliente@teste.com / cliente123\n";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
} 