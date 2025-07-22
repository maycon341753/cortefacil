<?php
require_once 'conexao.php';
session_start();

$id_agendamento = $_GET['id'] ?? null;
$cliente_id = $_SESSION['id'] ?? null;

if (!$id_agendamento || !$cliente_id) {
    echo "Acesso inválido.";
    exit;
}

try {
    $conn = getConexao();
    
    $sql = "SELECT a.*, s.nome_fantasia AS nome_salao, p.nome AS nome_profissional, u.nome AS nome_cliente, srv.nome AS nome_servico
            FROM agendamentos a
            JOIN saloes s ON a.salao_id = s.id
            JOIN profissionais p ON a.profissional_id = p.id
            JOIN usuarios u ON a.cliente_id = u.id
            LEFT JOIN servicos srv ON a.servico_id = srv.id
            WHERE a.id = ? AND a.cliente_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_agendamento, $cliente_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        echo "Agendamento não encontrado.";
        exit;
    }
} catch (Exception $e) {
    echo "Erro ao buscar dados do agendamento: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recibo CorteFácil</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #e8f5e8 0%, #f0f9f0 100%);
      min-height: 100vh;
      padding: 20px;
      color: #2d5a2d;
    }

    .container {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(45, 90, 45, 0.1);
      overflow: hidden;
      border: 2px solid #e8f5e8;
    }

    .header {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 30px;
      text-align: center;
      position: relative;
    }

    .header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
    }

    .header-content {
      position: relative;
      z-index: 1;
    }

    .success-icon {
      font-size: 3rem;
      margin-bottom: 15px;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    .header h1 {
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 5px;
    }

    .header p {
      font-size: 1.1rem;
      opacity: 0.9;
    }

    .recibo-content {
      padding: 40px;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
      color: #155724;
      padding: 12px 20px;
      border-radius: 25px;
      font-weight: 600;
      margin-bottom: 30px;
      border: 2px solid #b8dabd;
    }

    .status-badge i {
      margin-right: 8px;
      font-size: 1.1rem;
    }

    .info-grid {
      display: grid;
      gap: 20px;
      margin-bottom: 30px;
    }

    .info-item {
      display: flex;
      align-items: center;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 10px;
      border-left: 4px solid #28a745;
      transition: all 0.3s ease;
    }

    .info-item:hover {
      background: #e8f5e8;
      transform: translateX(5px);
    }

    .info-icon {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      margin-right: 15px;
      font-size: 1.1rem;
    }

    .info-content {
      flex: 1;
    }

    .info-label {
      font-weight: 600;
      color: #495057;
      font-size: 0.9rem;
      margin-bottom: 2px;
    }

    .info-value {
      font-size: 1.1rem;
      color: #2d5a2d;
      font-weight: 500;
    }

    .valor-destaque {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border-left-color: #20c997;
    }

    .valor-destaque .info-icon {
      background: rgba(255, 255, 255, 0.2);
    }

    .valor-destaque .info-label,
    .valor-destaque .info-value {
      color: white;
    }

    .footer {
      background: #f8f9fa;
      padding: 25px;
      text-align: center;
      border-top: 1px solid #e9ecef;
    }

    .footer-text {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 15px;
    }

    .footer-logo {
      font-weight: 700;
      color: #28a745;
      font-size: 1.2rem;
    }

    .print-btn {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
      padding: 15px;
      border-radius: 50%;
      font-size: 1.2rem;
      cursor: pointer;
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .print-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    @media print {
      body {
        background: white;
        padding: 0;
      }
      
      .container {
        box-shadow: none;
        border: none;
      }
      
      .print-btn {
        display: none;
      }
    }

    @media (max-width: 768px) {
      .container {
        margin: 10px;
        border-radius: 10px;
      }
      
      .header {
        padding: 20px;
      }
      
      .header h1 {
        font-size: 1.5rem;
      }
      
      .recibo-content {
        padding: 20px;
      }
      
      .print-btn {
        bottom: 20px;
        right: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="header-content">
        <div class="success-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h1>Recibo de Pagamento</h1>
        <p>Agendamento Confirmado</p>
      </div>
    </div>

    <div class="recibo-content">
      <div class="status-badge">
        <i class="fas fa-shield-alt"></i>
        Pagamento Aprovado
      </div>

      <div class="info-grid">
        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-user"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Cliente</div>
            <div class="info-value"><?= htmlspecialchars($dados['nome_cliente']) ?></div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-store"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Salão</div>
            <div class="info-value"><?= htmlspecialchars($dados['nome_salao']) ?></div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-user-tie"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Profissional</div>
            <div class="info-value"><?= htmlspecialchars($dados['nome_profissional']) ?></div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-cut"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Serviço</div>
            <div class="info-value"><?= htmlspecialchars($dados['nome_servico'] ?? 'Não informado') ?></div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Data do Agendamento</div>
            <div class="info-value"><?= date('d/m/Y', strtotime($dados['data'])) ?></div>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Horário</div>
            <div class="info-value"><?= date('H:i', strtotime($dados['hora'])) ?></div>
          </div>
        </div>

        <div class="info-item valor-destaque">
          <div class="info-icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Taxa da Plataforma</div>
            <div class="info-value">R$ <?= number_format($dados['taxa_servico'], 2, ',', '.') ?></div>
          </div>
        </div>

        <?php if (isset($dados['multa']) && $dados['multa'] > 0): ?>
        <div class="info-item" style="border-left-color: #dc3545;">
          <div class="info-icon" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Multa</div>
            <div class="info-value">R$ <?= number_format($dados['multa'], 2, ',', '.') ?></div>
          </div>
        </div>
        <?php endif; ?>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-receipt"></i>
          </div>
          <div class="info-content">
            <div class="info-label">Data de Emissão</div>
            <div class="info-value"><?= date('d/m/Y H:i') ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="footer">
      <div class="footer-text">
        Este é um recibo válido emitido pela plataforma
      </div>
      <div class="footer-logo">
        <i class="fas fa-cut"></i> CorteFácil
      </div>
    </div>
  </div>

  <button class="print-btn" onclick="window.print()" title="Imprimir Recibo">
    <i class="fas fa-print"></i>
  </button>
</body>
</html>
