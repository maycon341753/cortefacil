<?php
require_once 'conexao.php';
require_once 'gerenciar_ciclos_metas.php';

header('Content-Type: application/json');

try {
    echo "=== TESTE DO SISTEMA DE CICLOS DE METAS ===\n\n";
    
    // 1. Criar a tabela se não existir
    echo "1. Criando tabela ciclos_metas...\n";
    criarTabelaCiclosMetas();
    echo "✓ Tabela criada/verificada com sucesso\n\n";
    
    // 2. Buscar um salão de teste
    $conn = getConexao();
    $stmt = $conn->prepare("SELECT id, nome_fantasia FROM saloes WHERE ativo = 1 LIMIT 1");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        throw new Exception("Nenhum salão ativo encontrado para teste");
    }
    
    echo "2. Salão de teste: {$salao['nome_fantasia']} (ID: {$salao['id']})\n\n";
    
    // 3. Obter ou criar ciclo ativo
    echo "3. Obtendo ciclo ativo...\n";
    $ciclo = obterCicloAtivo($salao['id']);
    
    if ($ciclo) {
        echo "✓ Ciclo ativo encontrado:\n";
        echo "   - Data início: {$ciclo['data_inicio']}\n";
        echo "   - Data fim: {$ciclo['data_fim']}\n";
        echo "   - Agendamentos confirmados: {$ciclo['agendamentos_confirmados']}\n";
        echo "   - Dias restantes: {$ciclo['dias_restantes']}\n";
    } else {
        echo "✗ Nenhum ciclo ativo encontrado\n";
    }
    echo "\n";
    
    // 4. Atualizar contagem de agendamentos
    echo "4. Atualizando contagem de agendamentos...\n";
    $ciclo_atualizado = atualizarContagemAgendamentos($salao['id']);
    
    if ($ciclo_atualizado) {
        echo "✓ Contagem atualizada:\n";
        echo "   - Agendamentos confirmados: {$ciclo_atualizado['agendamentos_confirmados']}\n";
        echo "   - Meta 50 atingida: " . ($ciclo_atualizado['meta_50_atingida'] ? 'SIM' : 'NÃO') . "\n";
        echo "   - Meta 100 atingida: " . ($ciclo_atualizado['meta_100_atingida'] ? 'SIM' : 'NÃO') . "\n";
    } else {
        echo "✗ Erro ao atualizar contagem\n";
    }
    echo "\n";
    
    // 5. Buscar histórico
    echo "5. Buscando histórico de ciclos...\n";
    $historico = obterHistoricoCiclos($salao['id'], 3);
    
    if ($historico) {
        echo "✓ Histórico encontrado (" . count($historico) . " ciclos):\n";
        foreach ($historico as $i => $h) {
            echo "   Ciclo " . ($i + 1) . ": {$h['data_inicio']} a {$h['data_fim']} - {$h['agendamentos_confirmados']} agendamentos\n";
        }
    } else {
        echo "✗ Nenhum histórico encontrado\n";
    }
    echo "\n";
    
    // 6. Finalizar ciclos expirados
    echo "6. Finalizando ciclos expirados...\n";
    finalizarCiclosExpirados();
    echo "✓ Verificação de ciclos expirados concluída\n\n";
    
    echo "=== TESTE CONCLUÍDO COM SUCESSO ===\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
}
?>