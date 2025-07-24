<?php
require_once 'php/conexao.php';

echo "<h1>🕒 Teste das Melhorias nos Horários</h1>";

echo "<h2>📋 Funcionalidades Implementadas:</h2>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Melhorias Implementadas:</h3>";
echo "<ul>";
echo "<li><strong>Horários passados são filtrados:</strong> Se for hoje, horários que já passaram não aparecem</li>";
echo "<li><strong>Conflitos por serviço:</strong> Se outro cliente agendou o mesmo profissional para o mesmo serviço, o horário fica indisponível</li>";
echo "<li><strong>Horário atual considerado:</strong> Sistema usa o horário real atual para filtrar</li>";
echo "</ul>";
echo "</div>";

try {
    $pdo = getConexao();
    
    // Teste 1: Verificar horários para hoje
    echo "<h3>🧪 Teste 1: Horários para hoje</h3>";
    $hoje = date('Y-m-d');
    $agora = date('H:i');
    echo "<p><strong>Data atual:</strong> $hoje</p>";
    echo "<p><strong>Horário atual:</strong> $agora</p>";
    
    // Simular chamada da API
    $salao_id = 7; // Salão do Eduardo
    $profissional_id = 1; // Primeiro profissional
    $servico_id = 1; // Primeiro serviço
    
    echo "<p>Testando com: Salão ID $salao_id, Profissional ID $profissional_id, Serviço ID $servico_id</p>";
    
    // Buscar informações do salão
    $stmt = $pdo->prepare("SELECT horario_abertura, horario_fechamento, intervalo_agendamento FROM saloes WHERE id = ?");
    $stmt->execute([$salao_id]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao) {
        echo "<p><strong>Horário do salão:</strong> {$salao['horario_abertura']} às {$salao['horario_fechamento']}</p>";
        echo "<p><strong>Intervalo:</strong> {$salao['intervalo_agendamento']} minutos</p>";
        
        // Buscar agendamentos existentes
        $stmt = $pdo->prepare("
            SELECT TIME_FORMAT(hora, '%H:%i') as horario, servico_id
            FROM agendamentos 
            WHERE profissional_id = ? AND data = ? AND status NOT IN ('CANCELADO')
            ORDER BY hora
        ");
        $stmt->execute([$profissional_id, $hoje]);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📅 Agendamentos existentes para hoje:</h4>";
        if (empty($agendamentos)) {
            echo "<p>Nenhum agendamento encontrado.</p>";
        } else {
            echo "<ul>";
            foreach ($agendamentos as $ag) {
                echo "<li>Horário: {$ag['horario']} - Serviço ID: {$ag['servico_id']}</li>";
            }
            echo "</ul>";
        }
        
        // Simular a lógica da API
        $abertura = new DateTime($hoje . ' ' . $salao['horario_abertura']);
        $fechamento = new DateTime($hoje . ' ' . $salao['horario_fechamento']);
        $intervalo = $salao['intervalo_agendamento'] ?? 30;
        
        $horariosOcupados = [];
        foreach ($agendamentos as $ag) {
            if ($ag['servico_id'] == $servico_id) {
                $horariosOcupados[] = $ag['horario'];
            }
        }
        
        echo "<h4>🚫 Horários ocupados para o serviço $servico_id:</h4>";
        if (empty($horariosOcupados)) {
            echo "<p>Nenhum horário ocupado para este serviço.</p>";
        } else {
            echo "<ul>";
            foreach ($horariosOcupados as $horario) {
                echo "<li>$horario</li>";
            }
            echo "</ul>";
        }
        
        // Gerar horários disponíveis
        $horariosDisponiveis = [];
        $horarioAtual = clone $abertura;
        
        echo "<h4>✅ Horários disponíveis (considerando horário atual e conflitos):</h4>";
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin: 20px 0;'>";
        
        while ($horarioAtual <= $fechamento) {
            $horarioStr = $horarioAtual->format('H:i');
            
            // Verifica se o horário não está ocupado
            $horarioDisponivel = !in_array($horarioStr, $horariosOcupados);
            
            // Se for hoje, verifica se o horário não passou
            $horarioPassou = ($horarioStr <= $agora);
            if ($horarioPassou) {
                $horarioDisponivel = false;
            }
            
            $cor = $horarioDisponivel ? '#d4edda' : '#f8d7da';
            $status = $horarioDisponivel ? '✅' : '❌';
            $motivo = '';
            
            if (!$horarioDisponivel) {
                if ($horarioPassou) {
                    $motivo = ' (passou)';
                } elseif (in_array($horarioStr, $horariosOcupados)) {
                    $motivo = ' (ocupado)';
                }
            }
            
            echo "<div style='background-color: $cor; padding: 8px; text-align: center; border-radius: 4px; font-size: 12px;'>";
            echo "$status $horarioStr$motivo";
            echo "</div>";
            
            if ($horarioDisponivel) {
                $horariosDisponiveis[] = $horarioStr;
            }
            
            $horarioAtual->modify("+{$intervalo} minutes");
        }
        
        echo "</div>";
        
        echo "<h4>📊 Resumo:</h4>";
        echo "<p><strong>Total de horários disponíveis:</strong> " . count($horariosDisponiveis) . "</p>";
        echo "<p><strong>Horários filtrados por já ter passado:</strong> Sim</p>";
        echo "<p><strong>Horários filtrados por conflito de serviço:</strong> Sim</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Salão não encontrado!</p>";
    }
    
    echo "<h3>🔗 Como testar:</h3>";
    echo "<ol>";
    echo "<li>Acesse o painel do cliente</li>";
    echo "<li>Escolha um salão</li>";
    echo "<li>Escolha um profissional</li>";
    echo "<li>Escolha um serviço</li>";
    echo "<li>Escolha a data de hoje</li>";
    echo "<li>Verifique que horários passados não aparecem</li>";
    echo "<li>Faça um agendamento</li>";
    echo "<li>Tente agendar novamente o mesmo profissional + serviço no mesmo horário</li>";
    echo "<li>Verifique que o horário não aparece mais disponível</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>