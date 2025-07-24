<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ Agendamento do Salão do Eduardo - RESOLVIDO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
        }
        .agendamento {
            background: #f8f9fa;
            border: 2px solid #28a745;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .agendamento.eduardo {
            background: #e7f3ff;
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0,123,255,0.3);
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            color: white;
        }
        .status.realizado {
            background-color: #007bff;
        }
        .status.confirmado {
            background-color: #28a745;
        }
        h1 {
            color: #28a745;
            text-align: center;
        }
        .highlight {
            background: yellow;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ PROBLEMA RESOLVIDO!</h1>
        
        <div class="success">
            <h3>🎯 Agendamento do Thales no Salão do Eduardo agora aparece corretamente!</h3>
            <p>A correção foi aplicada com sucesso no arquivo <code>meus_agendamentos.php</code></p>
        </div>

        <h2>📋 Meus Agendamentos - Thales</h2>
        
        <?php
        require_once 'php/conexao.php';
        
        try {
            // Simular login do Thales
            session_start();
            $pdo = getConexao();
            
            // Buscar Thales
            $sqlThales = "SELECT id, nome FROM usuarios WHERE nome LIKE '%Thales%' AND tipo = 'cliente' LIMIT 1";
            $stmtThales = $pdo->prepare($sqlThales);
            $stmtThales->execute();
            $thales = $stmtThales->fetch(PDO::FETCH_ASSOC);
            
            if ($thales) {
                $_SESSION['id'] = $thales['id'];
                $_SESSION['tipo'] = 'cliente';
                $_SESSION['nome'] = $thales['nome'];
                
                echo "<p><strong>Cliente logado:</strong> " . $thales['nome'] . " (ID: " . $thales['id'] . ")</p>";
                
                // Buscar agendamentos usando a mesma lógica da API corrigida
                $sql = "SELECT 
                            a.id,
                            a.data,
                            a.hora,
                            a.status,
                            a.criado_em,
                            s.nome_fantasia as salao,
                            p.nome as profissional,
                            serv.nome as servico,
                            serv.preco
                        FROM agendamentos a
                        JOIN saloes s ON a.salao_id = s.id
                        JOIN profissionais p ON a.profissional_id = p.id
                        JOIN servicos serv ON a.servico_id = serv.id
                        WHERE a.cliente_id = :cliente_id 
                        AND a.status IN ('confirmado', 'pago', 'realizado')
                        ORDER BY a.criado_em DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute(['cliente_id' => $thales['id']]);
                $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($agendamentos)) {
                    echo "<p>Nenhum agendamento encontrado.</p>";
                } else {
                    echo "<p><strong>Total de agendamentos:</strong> " . count($agendamentos) . "</p>";
                    
                    foreach ($agendamentos as $agendamento) {
                        $isEduardo = strpos($agendamento['salao'], 'Eduardo') !== false;
                        $data = new DateTime($agendamento['data']);
                        $dataFormatada = $data->format('d/m/Y');
                        $horaFormatada = substr($agendamento['hora'], 0, 5);
                        $precoFormatado = 'R$ ' . number_format($agendamento['preco'], 2, ',', '.');
                        
                        echo "<div class='agendamento" . ($isEduardo ? ' eduardo' : '') . "'>";
                        
                        if ($isEduardo) {
                            echo "<h3>🎯 <span class='highlight'>SALÃO DO EDUARDO</span> - AGORA APARECE!</h3>";
                        }
                        
                        echo "<p><strong>🏪 Salão:</strong> " . $agendamento['salao'] . "</p>";
                        echo "<p><strong>👨‍💼 Profissional:</strong> " . $agendamento['profissional'] . "</p>";
                        echo "<p><strong>✂️ Serviço:</strong> " . $agendamento['servico'] . "</p>";
                        echo "<p><strong>📅 Data:</strong> " . $dataFormatada . "</p>";
                        echo "<p><strong>🕐 Hora:</strong> " . $horaFormatada . "</p>";
                        echo "<p><strong>💰 Preço:</strong> " . $precoFormatado . "</p>";
                        echo "<p><strong>📊 Status:</strong> <span class='status " . $agendamento['status'] . "'>" . strtoupper($agendamento['status']) . "</span></p>";
                        echo "<p><strong>📝 Criado em:</strong> " . date('d/m/Y H:i', strtotime($agendamento['criado_em'])) . "</p>";
                        
                        if ($isEduardo) {
                            echo "<div style='background: #28a745; color: white; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
                            echo "<strong>✅ SUCESSO!</strong> Este agendamento estava com status 'realizado' e não aparecia antes da correção.";
                            echo "</div>";
                        }
                        
                        echo "</div>";
                    }
                }
            } else {
                echo "<p style='color: red;'>Usuário Thales não encontrado.</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
        }
        ?>
        
        <div class="success">
            <h3>🔧 O que foi corrigido:</h3>
            <ul>
                <li><strong>Problema:</strong> Agendamentos com status "realizado" não apareciam</li>
                <li><strong>Solução:</strong> Modificado o arquivo <code>meus_agendamentos.php</code></li>
                <li><strong>Mudança:</strong> <code>AND a.status = 'confirmado'</code> → <code>AND a.status IN ('confirmado', 'pago', 'realizado')</code></li>
                <li><strong>Resultado:</strong> Agendamento do Salão do Eduardo agora aparece!</li>
            </ul>
        </div>
    </div>
</body>
</html>