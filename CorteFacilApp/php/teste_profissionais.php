<?php
// Iniciar a sessão antes de qualquer saída
session_start();
require_once 'conexao.php';

// Simular login de parceiro
$_SESSION['id'] = 1;
$_SESSION['tipo'] = 'salao';
$_SESSION['salao_id'] = 1;
$_SESSION['nome'] = 'Salão Teste';

// Exibir informações da sessão
echo "<h3>Informações da Sessão</h3>";
echo "ID: " . $_SESSION['id'] . "<br>";
echo "Tipo: " . $_SESSION['tipo'] . "<br>";
echo "Salão ID: " . $_SESSION['salao_id'] . "<br>";
echo "Nome: " . $_SESSION['nome'] . "<br>";
echo "Session ID: " . session_id() . "<br><br>";

// Buscar profissionais
echo "<h3>Profissionais</h3>";

try {
    $stmt = $conn->prepare("
        SELECT 
            id,
            nome,
            especialidade,
            telefone
        FROM profissionais 
        WHERE salao_id = :salao_id
        ORDER BY nome
    ");
    
    $stmt->execute(['salao_id' => $_SESSION['salao_id']]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($profissionais) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Especialidade</th><th>Telefone</th></tr>";
        
        foreach ($profissionais as $profissional) {
            echo "<tr>";
            echo "<td>" . $profissional['id'] . "</td>";
            echo "<td>" . $profissional['nome'] . "</td>";
            echo "<td>" . $profissional['especialidade'] . "</td>";
            echo "<td>" . $profissional['telefone'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "Nenhum profissional encontrado.";
    }
    
} catch (PDOException $e) {
    echo "Erro ao buscar profissionais: " . $e->getMessage();
}
?>