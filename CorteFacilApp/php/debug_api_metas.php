<?php
// Debug específico para a API de metas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug da API de Metas</h2>";

// Simula a sessão do salão Liz Hadassa
session_start();
$_SESSION['salao_id'] = 4; // ID do salão Liz Hadassa
$_SESSION['salao_nome'] = 'Liz Hadassa Maitê Silva';

echo "<p><strong>Sessão configurada:</strong></p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";

try {
    echo "<h3>1. Testando conexão com o banco</h3>";
    include 'conexao.php';
    echo "✅ Conexão estabelecida com sucesso<br>";
    
    echo "<h3>2. Testando inclusão do gerenciar_ciclos_metas.php</h3>";
    include 'gerenciar_ciclos_metas.php';
    echo "✅ Arquivo incluído com sucesso<br>";
    
    echo "<h3>3. Testando função finalizarCiclosExpirados()</h3>";
    $resultado_finalizar = finalizarCiclosExpirados();
    echo "Resultado: " . ($resultado_finalizar !== false ? "✅ Sucesso" : "❌ Erro") . "<br>";
    
    echo "<h3>4. Testando função atualizarContagemAgendamentos()</h3>";
    $salao_id = $_SESSION['salao_id'];
    echo "Salão ID: $salao_id<br>";
    
    $ciclo_atual = atualizarContagemAgendamentos($salao_id);
    
    if ($ciclo_atual) {
        echo "✅ Função executada com sucesso<br>";
        echo "<strong>Dados do ciclo atual:</strong><br>";
        echo "<pre>";
        print_r($ciclo_atual);
        echo "</pre>";
    } else {
        echo "❌ Erro na função atualizarContagemAgendamentos()<br>";
    }
    
    echo "<h3>5. Testando função obterHistoricoCiclos()</h3>";
    $historico = obterHistoricoCiclos($salao_id, 6);
    echo "Histórico obtido: " . count($historico) . " ciclos<br>";
    echo "<pre>";
    print_r($historico);
    echo "</pre>";
    
    echo "<h3>6. Simulando resposta completa da API</h3>";
    
    if ($ciclo_atual) {
        // Calcula o bônus atual baseado nas metas atingidas
        $bonus_atual = 0;
        if ($ciclo_atual['meta_100_atingida']) {
            $bonus_atual = 150.00;
        } elseif ($ciclo_atual['meta_50_atingida']) {
            $bonus_atual = 50.00;
        }
        
        // Prepara os dados da meta atual
        $meta_atual = [
            'agendamentos_confirmados' => $ciclo_atual['agendamentos_confirmados'],
            'agendamentos_mes' => $ciclo_atual['agendamentos_confirmados'], // Compatibilidade
            'bonus_pago' => number_format($bonus_atual, 2, ',', '.'),
            'dias_restantes' => (int)$ciclo_atual['dias_restantes'],
            'data_inicio' => $ciclo_atual['data_inicio'],
            'data_fim' => $ciclo_atual['data_fim'],
            'meta_50_atingida' => $ciclo_atual['meta_50_atingida'],
            'meta_100_atingida' => $ciclo_atual['meta_100_atingida']
        ];
        
        $historico_formatado = [];
        foreach ($historico as $ciclo) {
            $bonus_ciclo = 0;
            if ($ciclo['meta_100_atingida']) {
                $bonus_ciclo = 150.00;
            } elseif ($ciclo['meta_50_atingida']) {
                $bonus_ciclo = 50.00;
            }
            
            $historico_formatado[] = [
                'mes_formatado' => $ciclo['periodo_formatado'],
                'agendamentos' => $ciclo['agendamentos_confirmados'],
                'bonus_pago' => number_format($bonus_ciclo, 2, ',', '.'),
                'meta_50_atingida' => $ciclo['meta_50_atingida'],
                'meta_100_atingida' => $ciclo['meta_100_atingida']
            ];
        }
        
        $resposta = [
            'status' => 'ok',
            'meta_atual' => $meta_atual,
            'historico' => $historico_formatado
        ];
        
        echo "✅ Resposta da API gerada com sucesso:<br>";
        echo "<pre>";
        echo json_encode($resposta, JSON_PRETTY_PRINT);
        echo "</pre>";
    } else {
        echo "❌ Não foi possível gerar a resposta da API<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ ERRO CAPTURADO:</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>7. Verificando logs de erro</h3>";
$error_log = error_get_last();
if ($error_log) {
    echo "<pre>";
    print_r($error_log);
    echo "</pre>";
} else {
    echo "Nenhum erro registrado nos logs PHP<br>";
}
?>