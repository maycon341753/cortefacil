<?php
header('Content-Type: text/html; charset=utf-8');

// Incluir configuração do banco
require_once 'config.php';

echo "<h2>Teste da API de Serviços</h2>";

// Primeiro, vamos buscar o ID do Salão do Eduardo
try {
    $stmt = $pdo->prepare("SELECT id, nome FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao) {
        echo "<p><strong>Salão encontrado:</strong> {$salao['nome']} (ID: {$salao['id']})</p>";
        $salao_id = $salao['id'];
        
        // Agora vamos testar a API de serviços
        echo "<h3>Testando API listar_servicos.php</h3>";
        
        $url = "http://localhost/cortefacil/CorteFacilApp/php/listar_servicos.php?salao_id=" . $salao_id;
        echo "<p><strong>URL da API:</strong> <a href='$url' target='_blank'>$url</a></p>";
        
        // Fazer requisição para a API
        $response = file_get_contents($url);
        echo "<h4>Resposta da API:</h4>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Decodificar JSON
        $data = json_decode($response, true);
        if ($data) {
            echo "<h4>Dados decodificados:</h4>";
            echo "<pre>" . print_r($data, true) . "</pre>";
            
            if (isset($data['data']) && is_array($data['data'])) {
                echo "<p><strong>Número de serviços encontrados:</strong> " . count($data['data']) . "</p>";
            }
        }
        
        // Verificar diretamente no banco
        echo "<h3>Verificação direta no banco de dados</h3>";
        $stmt = $pdo->prepare("SELECT * FROM servicos WHERE salao_id = ?");
        $stmt->execute([$salao_id]);
        $servicos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Serviços no banco:</strong> " . count($servicos_db) . "</p>";
        if (!empty($servicos_db)) {
            echo "<pre>" . print_r($servicos_db, true) . "</pre>";
        }
        
    } else {
        echo "<p><strong>Erro:</strong> Salão do Eduardo não encontrado!</p>";
        
        // Listar todos os salões
        $stmt = $pdo->prepare("SELECT id, nome FROM saloes");
        $stmt->execute();
        $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Salões disponíveis:</h3>";
        foreach ($saloes as $s) {
            echo "<p>ID: {$s['id']} - Nome: {$s['nome']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>