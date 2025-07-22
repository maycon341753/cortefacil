<?php
session_start();
require_once 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

// Verifica se o código do cupom foi fornecido
$codigo = filter_input(INPUT_GET, 'codigo', FILTER_SANITIZE_STRING);
if (!$codigo) {
    die('Código do cupom não fornecido');
}

try {
    $pdo = getConexao();

    // Busca informações do cupom
    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            s.nome_fantasia as nome_salao,
            s.endereco as endereco_salao,
            s.telefone as telefone_salao,
            u.nome as nome_cliente
        FROM cupons c
        JOIN saloes s ON c.salao_id = s.id
        LEFT JOIN usuarios u ON c.cliente_id = u.id
        WHERE c.codigo = ?
    ");
    $stmt->execute([$codigo]);
    $cupom = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cupom) {
        die('Cupom não encontrado');
    }

    // Atualiza o status se estiver expirado
    if ($cupom['status'] === 'disponivel' && strtotime($cupom['data_expiracao']) < time()) {
        $cupom['status'] = 'expirado';
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom #<?php echo htmlspecialchars($cupom['codigo']); ?> - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .cupom-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-disponivel {
            background-color: #2dce89;
            color: white;
        }
        .status-utilizado {
            background-color: #11cdef;
            color: white;
        }
        .status-expirado {
            background-color: #fb6340;
            color: white;
        }
        .info-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }
        .info-title {
            font-size: 1.2em;
            color: #5e72e4;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            color: #8898aa;
            font-weight: 500;
        }
        .info-value {
            color: #32325d;
            font-weight: 600;
        }
        .codigo-cupom {
            font-family: monospace;
            font-size: 2em;
            letter-spacing: 2px;
            color: #5e72e4;
            text-align: center;
            padding: 15px;
            background: #f8f9fe;
            border-radius: 10px;
            margin: 20px 0;
        }
        @media print {
            body {
                background: white;
            }
            .cupom-container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="cupom-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Cupom de Corte Grátis</h1>
            <span class="status-badge status-<?php echo htmlspecialchars($cupom['status']); ?>">
                <?php echo htmlspecialchars($cupom['status']); ?>
            </span>
        </div>

        <div class="codigo-cupom">
            <?php echo htmlspecialchars($cupom['codigo']); ?>
        </div>

        <div class="info-section">
            <div class="info-title">Informações do Salão</div>
            <div class="info-row">
                <span class="info-label">Nome:</span>
                <span class="info-value"><?php echo htmlspecialchars($cupom['nome_salao']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Endereço:</span>
                <span class="info-value"><?php echo htmlspecialchars($cupom['endereco_salao']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefone:</span>
                <span class="info-value"><?php echo htmlspecialchars($cupom['telefone_salao']); ?></span>
            </div>
        </div>

        <div class="info-section">
            <div class="info-title">Detalhes do Cupom</div>
            <div class="info-row">
                <span class="info-label">Data de Geração:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($cupom['data_geracao'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Data de Expiração:</span>
                <span class="info-value"><?php echo date('d/m/Y', strtotime($cupom['data_expiracao'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Valor de Ressarcimento:</span>
                <span class="info-value">R$ <?php echo number_format($cupom['valor_ressarcimento'], 2, ',', '.'); ?></span>
            </div>
            <?php if ($cupom['status'] === 'utilizado' && $cupom['nome_cliente']): ?>
            <div class="info-row">
                <span class="info-label">Utilizado por:</span>
                <span class="info-value"><?php echo htmlspecialchars($cupom['nome_cliente']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Data de Utilização:</span>
                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($cupom['data_utilizacao'])); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-4 text-center no-print">
            <button class="btn btn-primary me-2" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimir
            </button>
            <button class="btn btn-secondary" onclick="window.close()">
                <i class="fas fa-times me-2"></i>Fechar
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} catch (Exception $e) {
    die('Erro ao buscar informações do cupom: ' . $e->getMessage());
}
?>