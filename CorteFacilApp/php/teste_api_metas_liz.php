<?php
require_once 'conexao.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = getConexao();
    
    echo "=== TESTE DIRETO DA API DE METAS ===\n\n";
    
    // 1. Buscar o salão da Liz
    $stmt = $conn->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE ?");
    $stmt->execute(['%Liz%']);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Salão: {$salao['nome_fantasia']} (ID: {$salao['id']})\n\n";
    
    // 2. Simular sessão do salão
    session_start();
    $_SESSION['salao_id'] = $salao['id'];
    $_SESSION['salao_nome'] = $salao['nome_fantasia'];
    
    echo "🔐 Sessão simulada para salão ID: {$salao['id']}\n\n";
    
    // 3. Chamar a API de metas diretamente
    echo "📞 Chamando API salao_obter_metas.php...\n";
    
    // Capturar a saída da API
    ob_start();
    include 'salao_obter_metas.php';
    $api_response = ob_get_clean();
    
    echo "📋 Resposta da API:\n";
    echo $api_response . "\n\n";
    
    // 4. Verificar se é JSON válido
    $dados = json_decode($api_response, true);
    
    if ($dados) {
        echo "✅ JSON válido recebido\n";
        echo "Status: {$dados['status']}\n";
        
        if ($dados['status'] === 'ok') {
            echo "📊 Dados das metas:\n";
            echo "   Agendamentos: {$dados['meta_atual']['agendamentos_mes']}\n";
            echo "   Dias restantes: {$dados['meta_atual']['dias_restantes']}\n";
            echo "   Bônus atual: R$ {$dados['meta_atual']['bonus_pago']}\n";
            echo "   Meta 50 atingida: " . ($dados['meta_atual']['meta_50_atingida'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Meta 100 atingida: " . ($dados['meta_atual']['meta_100_atingida'] ? 'SIM' : 'NÃO') . "\n";
        } else {
            echo "❌ Erro na API: {$dados['mensagem']}\n";
        }
    } else {
        echo "❌ Resposta não é JSON válido\n";
        echo "Resposta bruta: " . $api_response . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>