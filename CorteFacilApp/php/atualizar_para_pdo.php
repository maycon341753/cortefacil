<?php
require_once 'conexao.php';

function atualizarArquivoParaPDO($filePath) {
    $content = file_get_contents($filePath);
    
    // Substituições para mysqli
    $replacements = [
        // Substituir query
        '/\$conn->query\(([^)]+)\)->rowCount\(\)/' => '$conn->query($1)->fetchColumn()',
        '/\$conn->query\(([^)]+)\)->fetch_assoc\(\)/' => '$conn->query($1)->fetch(PDO::FETCH_ASSOC)',
        '/\$conn->query\(([^)]+)\)->fetch_all\(MYSQLI_ASSOC\)/' => '$conn->query($1)->fetchAll(PDO::FETCH_ASSOC)',
        
        // Substituir prepare e execute
        '/\$stmt = \$conn->prepare\(([^)]+)\);\s*\$stmt->bind_param\(([^)]+)\);/' => 
        '$stmt = $conn->prepare($1);
        $stmt->execute([$2]);',
        
        // Substituir fetch_assoc por fetch
        '/->fetch_assoc\(\)/' => '->fetch(PDO::FETCH_ASSOC)',
        
        // Substituir fetch_all por fetchAll
        '/->fetch_all\(MYSQLI_ASSOC\)/' => '->fetchAll(PDO::FETCH_ASSOC)',
        
        // Substituir last_insert_id
        '/\$conn->insert_id/' => '$conn->lastInsertId()',
        
        // Substituir affected_rows
        '/\$stmt->affected_rows/' => '$stmt->rowCount()',
        
        // Substituir real_escape_string
        '/\$conn->real_escape_string\(([^)]+)\)/' => '$1', // PDO usa prepared statements
        
        // Substituir connect_error
        '/\$conn->connect_error/' => '$conn->errorInfo()[2]',
        
        // Substituir error
        '/\$conn->error/' => '$conn->errorInfo()[2]',
        
        // Substituir num_rows
        '/\$result->num_rows/' => '$result->rowCount()',
        
        // Substituir mysqli_error
        '/mysqli_error\(\$conn\)/' => '$conn->errorInfo()[2]',
        
        // Substituir set_charset
        '/\$conn->set_charset\(([^)]+)\)/' => '// Charset definido na string DSN'
    ];
    
    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    file_put_contents($filePath, $content);
    echo "Arquivo atualizado: $filePath\n";
}

// Lista de arquivos para atualizar
$arquivos = [
    'admin_listar_metas.php',
    'inserir_dados_teste.php',
    'admin_movimentacoes_stats.php',
    'admin_dashboard_stats.php',
    'verificar_pagamento_pix.php',
    'admin_grafico_faturamento.php',
    'test_api_response.php',
    'check_saloes_table.php',
    'admin_listar_movimentacoes.php',
    'add_payment_id_column.php',
    'test_dashboard_api.php',
    'parceiro_excluir_profissional.php',
    'test_delete_salao.php',
    'admin_cadastrar_salao.php',
    'parceiro_salvar_profissional.php',
    'admin_metas_mes.php',
    'parceiro_obter_configuracoes.php',
    'init_database.php',
    'parceiro_listar_profissionais.php',
    'check_table_structure.php',
    'admin_excluir_salao.php',
    'admin_listar_saloes.php',
    'admin_obter_perfil.php',
    'criar_admin.php',
    'cliente/painel.php',
    'admin_login.php',
    'verificar_tabela_profissionais.php',
    'teste_obter_configuracoes.php',
    'admin_atualizar_salao.php',
    'criar_pagamento_pix.php',
    'verificar_usuarios_salao.php',
    'debug_saloes.php',
    'verificar_payment_id.php',
    'parceiro_login.php',
    'salao_obter_metas.php',
    'test_saloes_count.php',
    'test_delete_specific_salao.php',
    'test_dashboard_stats.php',
    'parceiro_obter_servico.php',
    'test_table_structure.php',
    'teste_profissionais.php',
    'criar_tabela_saloes.php',
    'debug_table_saloes.php',
    'teste_login_parceiro.php',
    'test_connection.php',
    'create_saloes.php',
    'admin_obter_salao.php',
    'admin_atualizar_perfil.php',
    'criar_usuario_salao_teste.php',
    'test_saloes_query.php',
    'gerar_recibo.php',
    'parceiro_salvar_servico.php',
    'criar_salao_teste.php',
    'parceiro_dashboard_stats.php',
    'definir_senha_usuario.php',
    'parceiro_excluir_servico.php',
    'parceiro_obter_profissional.php',
    'parceiro_listar_servicos.php',
    'corrigir_email_usuario.php',
    'parceiro_salvar_configuracoes.php',
    'funcionarios.php',
    'admin_ultimos_agendamentos.php',
    'admin_estatisticas_saloes.php',
    'gerar_payload_pix.php',
    'admin_gerar_relatorio_pdf.php'
];

// Atualizar cada arquivo
try {
    foreach ($arquivos as $arquivo) {
        $filePath = __DIR__ . '/' . $arquivo;
        if (file_exists($filePath)) {
            atualizarArquivoParaPDO($filePath);
        } else {
            echo "Arquivo não encontrado: $filePath\n";
        }
    }
    echo "\nTodos os arquivos foram atualizados com sucesso!\n";
} catch (Exception $e) {
    echo "Erro ao atualizar arquivos: " . $e->getMessage() . "\n";
}
?>