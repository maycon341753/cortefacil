<?php
require_once 'conexao.php';
session_start();

echo "<h3>🧪 Teste do agendar.php corrigido</h3>";

try {
    $pdo = getConexao();
    
    // 1. Verificar se há usuário logado
    echo "<h4>1. Status da sessão:</h4>";
    if (isset($_SESSION['id'])) {
        echo "<p style='color: green;'>✅ Usuário logado: ID {$_SESSION['id']}, Tipo: " . ($_SESSION['tipo'] ?? 'N/A') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum usuário logado. <a href='../teste_login_profile.html'>Fazer login</a></p>";
        exit;
    }
    
    // 2. Verificar dados necessários para teste
    echo "<h4>2. Dados disponíveis para teste:</h4>";
    
    // Salões
    $stmt = $pdo->prepare("SELECT id, nome FROM saloes LIMIT 5");
    $stmt->execute();
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Salões disponíveis:</strong></p>";
    if (!empty($saloes)) {
        echo "<ul>";
        foreach ($saloes as $salao) {
            echo "<li>ID: {$salao['id']} - {$salao['nome']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum salão encontrado</p>";
    }
    
    // Profissionais
    $stmt = $pdo->prepare("SELECT id, nome, salao_id FROM profissionais LIMIT 5");
    $stmt->execute();
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Profissionais disponíveis:</strong></p>";
    if (!empty($profissionais)) {
        echo "<ul>";
        foreach ($profissionais as $prof) {
            echo "<li>ID: {$prof['id']} - {$prof['nome']} (Salão: {$prof['salao_id']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum profissional encontrado</p>";
    }
    
    // Serviços
    $stmt = $pdo->prepare("SELECT id, nome, preco FROM servicos LIMIT 5");
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Serviços disponíveis:</strong></p>";
    if (!empty($servicos)) {
        echo "<ul>";
        foreach ($servicos as $servico) {
            echo "<li>ID: {$servico['id']} - {$servico['nome']} (R$ " . number_format($servico['preco'], 2, ',', '.') . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum serviço encontrado</p>";
    }
    
    // 3. Formulário de teste
    if (!empty($saloes) && !empty($profissionais) && !empty($servicos)) {
        echo "<h4>3. Teste de agendamento:</h4>";
        echo "<form id='testeAgendamento' style='border: 1px solid #ddd; padding: 15px; background: #f9f9f9;'>";
        
        echo "<div style='margin: 10px 0;'>";
        echo "<label>Salão:</label><br>";
        echo "<select name='salao_id' required>";
        foreach ($saloes as $salao) {
            echo "<option value='{$salao['id']}'>{$salao['nome']}</option>";
        }
        echo "</select>";
        echo "</div>";
        
        echo "<div style='margin: 10px 0;'>";
        echo "<label>Profissional:</label><br>";
        echo "<select name='profissional_id' required>";
        foreach ($profissionais as $prof) {
            echo "<option value='{$prof['id']}'>{$prof['nome']}</option>";
        }
        echo "</select>";
        echo "</div>";
        
        echo "<div style='margin: 10px 0;'>";
        echo "<label>Serviço:</label><br>";
        echo "<select name='servico_id' required>";
        foreach ($servicos as $servico) {
            echo "<option value='{$servico['id']}'>{$servico['nome']} - R$ " . number_format($servico['preco'], 2, ',', '.') . "</option>";
        }
        echo "</select>";
        echo "</div>";
        
        echo "<div style='margin: 10px 0;'>";
        echo "<label>Data:</label><br>";
        echo "<input type='date' name='data' value='" . date('Y-m-d', strtotime('+1 day')) . "' required>";
        echo "</div>";
        
        echo "<div style='margin: 10px 0;'>";
        echo "<label>Hora:</label><br>";
        echo "<input type='time' name='hora' value='10:00' required>";
        echo "</div>";
        
        echo "<button type='button' onclick='testarAgendamento()' style='background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer;'>Testar Agendamento</button>";
        echo "</form>";
        
        echo "<div id='resultadoTeste' style='margin-top: 15px;'></div>";
        
        echo "<script>
        function testarAgendamento() {
            const form = document.getElementById('testeAgendamento');
            const formData = new FormData(form);
            
            fetch('agendar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const resultado = document.getElementById('resultadoTeste');
                if (data.status === 'success') {
                    resultado.innerHTML = '<div style=\"background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 3px;\"><strong>✅ Agendamento realizado com sucesso!</strong><br><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                } else {
                    resultado.innerHTML = '<div style=\"background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 3px;\"><strong>❌ Erro no agendamento:</strong><br>' + data.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('resultadoTeste').innerHTML = '<div style=\"background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 3px;\"><strong>❌ Erro:</strong><br>' + error + '</div>';
            });
        }
        </script>";
    } else {
        echo "<p style='color: red;'>❌ Não é possível testar: faltam dados básicos (salões, profissionais ou serviços)</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>❌ Erro: " . $e->getMessage() . "</strong></p>";
}
?>