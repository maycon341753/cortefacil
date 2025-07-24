<?php
session_start();

// Simular login do parceiro para teste
$_SESSION['id'] = 2; // ID do usuário de teste
$_SESSION['tipo'] = 'salao';
$_SESSION['nome'] = 'Salão do Eduardo';
$_SESSION['salao_id'] = 2;

echo "Sessão simulada criada para teste da funcionalidade de alterar senha.<br>";
echo "ID: " . $_SESSION['id'] . "<br>";
echo "Tipo: " . $_SESSION['tipo'] . "<br>";
echo "Nome: " . $_SESSION['nome'] . "<br>";
echo "Salão ID: " . $_SESSION['salao_id'] . "<br><br>";

echo '<a href="painel.php" class="btn btn-primary">Ir para o Painel</a>';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Alterar Senha Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Teste - Funcionalidade Alterar Senha</h5>
                    </div>
                    <div class="card-body">
                        <p>Sessão simulada criada para teste da funcionalidade de alterar senha.</p>
                        <ul>
                            <li><strong>ID:</strong> <?php echo $_SESSION['id']; ?></li>
                            <li><strong>Tipo:</strong> <?php echo $_SESSION['tipo']; ?></li>
                            <li><strong>Nome:</strong> <?php echo $_SESSION['nome']; ?></li>
                            <li><strong>Salão ID:</strong> <?php echo $_SESSION['salao_id']; ?></li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <h6>Instruções para teste:</h6>
                            <ol>
                                <li>Clique no botão abaixo para ir ao painel</li>
                                <li>No painel, clique no nome do usuário (que aparece sublinhado no canto superior direito)</li>
                                <li>Um modal de alteração de senha deve abrir</li>
                                <li>Teste a funcionalidade com a senha atual: <strong>123456</strong></li>
                            </ol>
                        </div>
                        
                        <div class="d-grid">
                            <a href="painel.php" class="btn btn-primary">Ir para o Painel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>