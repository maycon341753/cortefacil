<?php
require_once 'php/conexao.php';

echo "<h2>Verificar e Criar Agendamento - Thales no Salão do Eduardo</h2>";

try {
    $pdo = getConexao();
    
    // 1. Buscar o cliente Thales
    echo "<h3>1. Buscando cliente Thales</h3>";
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE nome LIKE '%thales%' OR nome LIKE '%Thales%'");
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        echo "<p style='color: red;'>❌ Cliente Thales não encontrado!</p>";
        
        // Criar cliente Thales para teste
        echo "<h4>Criando cliente Thales...</h4>";
        $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, senha, telefone) VALUES (?, ?, ?, ?)");
        $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt->execute(['Thales Theo Gustavo Viana', 'thales@teste.com', $senha_hash, '(11) 99999-9999']);
        
        $cliente_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Cliente Thales criado com ID: {$cliente_id}</p>";
        
        // Buscar novamente
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<p style='color: green;'>✅ Cliente encontrado: {$cliente['nome']} (ID: {$cliente['id']})</p>";
    }
    
    // 2. Buscar o Salão do Eduardo
    echo "<h3>2. Buscando Salão do Eduardo</h3>";
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome_fantasia LIKE '%Eduardo%' OR nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "<p style='color: red;'>❌ Salão do Eduardo não encontrado!</p>";
        
        // Listar salões disponíveis
        echo "<h4>Salões disponíveis:</h4>";
        $stmt = $pdo->prepare("SELECT id, nome_fantasia FROM saloes ORDER BY nome_fantasia");
        $stmt->execute();
        $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($saloes as $s) {
            echo "<p>ID: {$s['id']} - {$s['nome_fantasia']}</p>";
        }
        
        // Usar o primeiro salão disponível como "Eduardo"
        if (!empty($saloes)) {
            $salao = $saloes[0];
            echo "<p style='color: orange;'>⚠️ Usando salão '{$salao['nome_fantasia']}' como teste</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Salão encontrado: {$salao['nome_fantasia']} (ID: {$salao['id']})</p>";
    }
    
    // 3. Buscar um profissional do salão
    echo "<h3>3. Buscando profissional do salão</h3>";
    $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE salao_id = ? LIMIT 1");
    $stmt->execute([$salao['id']]);
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profissional) {
        echo "<p style='color: red;'>❌ Nenhum profissional encontrado no salão!</p>";
        
        // Criar um profissional para teste
        echo "<h4>Criando profissional para teste...</h4>";
        $stmt = $pdo->prepare("INSERT INTO profissionais (nome, salao_id) VALUES (?, ?)");
        $stmt->execute(['Profissional Teste', $salao['id']]);
        
        $profissional_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Profissional criado com ID: {$profissional_id}</p>";
        
        // Buscar novamente
        $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE id = ?");
        $stmt->execute([$profissional_id]);
        $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<p style='color: green;'>✅ Profissional encontrado: {$profissional['nome']} (ID: {$profissional['id']})</p>";
    }
    
    // 4. Buscar um serviço do salão
    echo "<h3>4. Buscando serviço do salão</h3>";
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE salao_id = ? LIMIT 1");
    $stmt->execute([$salao['id']]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        echo "<p style='color: red;'>❌ Nenhum serviço encontrado no salão!</p>";
        
        // Criar um serviço para teste
        echo "<h4>Criando serviço para teste...</h4>";
        $stmt = $pdo->prepare("INSERT INTO servicos (nome, preco, duracao, salao_id) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Corte Masculino', 25.00, 30, $salao['id']]);
        
        $servico_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Serviço criado com ID: {$servico_id}</p>";
        
        // Buscar novamente
        $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
        $stmt->execute([$servico_id]);
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<p style='color: green;'>✅ Serviço encontrado: {$servico['nome']} (ID: {$servico['id']})</p>";
    }
    
    // 5. Verificar se já existe agendamento do Thales neste salão
    echo "<h3>5. Verificando agendamentos existentes</h3>";
    $stmt = $pdo->prepare("
        SELECT * FROM agendamentos 
        WHERE cliente_id = ? AND salao_id = ?
        ORDER BY criado_em DESC
    ");
    $stmt->execute([$cliente['id'], $salao['id']]);
    $agendamentos_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($agendamentos_existentes)) {
        echo "<p style='color: green;'>✅ Encontrados " . count($agendamentos_existentes) . " agendamento(s) existente(s):</p>";
        
        foreach ($agendamentos_existentes as $ag) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
            echo "<strong>ID:</strong> {$ag['id']}<br>";
            echo "<strong>Data:</strong> {$ag['data']}<br>";
            echo "<strong>Hora:</strong> {$ag['hora']}<br>";
            echo "<strong>Status:</strong> {$ag['status']}<br>";
            echo "<strong>Criado em:</strong> {$ag['criado_em']}<br>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum agendamento encontrado. Criando um novo...</p>";
        
        // 6. Criar agendamento de teste
        echo "<h3>6. Criando agendamento de teste</h3>";
        
        $data_agendamento = date('Y-m-d', strtotime('+1 day')); // Amanhã
        $hora_agendamento = '14:00:00';
        
        $stmt = $pdo->prepare("
            INSERT INTO agendamentos 
            (cliente_id, salao_id, profissional_id, servico_id, data, hora, status, valor, criado_em) 
            VALUES (?, ?, ?, ?, ?, ?, 'confirmado', ?, NOW())
        ");
        
        $stmt->execute([
            $cliente['id'],
            $salao['id'],
            $profissional['id'],
            $servico['id'],
            $data_agendamento,
            $hora_agendamento,
            $servico['preco']
        ]);
        
        $agendamento_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Agendamento criado com sucesso!</p>";
        echo "<div style='border: 2px solid green; padding: 15px; margin: 10px 0;'>";
        echo "<strong>ID do Agendamento:</strong> {$agendamento_id}<br>";
        echo "<strong>Cliente:</strong> {$cliente['nome']}<br>";
        echo "<strong>Salão:</strong> {$salao['nome_fantasia']}<br>";
        echo "<strong>Profissional:</strong> {$profissional['nome']}<br>";
        echo "<strong>Serviço:</strong> {$servico['nome']}<br>";
        echo "<strong>Data:</strong> " . date('d/m/Y', strtotime($data_agendamento)) . "<br>";
        echo "<strong>Hora:</strong> " . date('H:i', strtotime($hora_agendamento)) . "<br>";
        echo "<strong>Status:</strong> confirmado<br>";
        echo "<strong>Valor:</strong> R$ " . number_format($servico['preco'], 2, ',', '.') . "<br>";
        echo "</div>";
    }
    
    // 7. Instruções para o usuário
    echo "<h3>7. Próximos Passos</h3>";
    echo "<div style='background-color: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3;'>";
    echo "<p><strong>Para ver o agendamento na tela:</strong></p>";
    echo "<ol>";
    echo "<li>Faça login como Thales usando:</li>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> {$cliente['email']}</li>";
    echo "<li><strong>Senha:</strong> 123456</li>";
    echo "</ul>";
    echo "<li>Acesse a seção 'Meus Agendamentos'</li>";
    echo "<li>O agendamento deve aparecer na lista</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>