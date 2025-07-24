<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    header('Location: ../parceiro_login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --bronze-gradient: linear-gradient(135deg, #cd7f32 0%, #b8860b 100%);
            --gold-gradient: linear-gradient(135deg, #ffd700 0%, #ffb347 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --card-shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Navbar Moderna e Tecnológica */
        .modern-navbar {
            background: var(--primary-gradient);
            backdrop-filter: blur(20px);
            border: none;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: relative;
            overflow: hidden;
        }

        .modern-navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
            pointer-events: none;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: white !important;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #fff, transparent);
            transition: width 0.3s ease;
        }

        .navbar-brand:hover::after {
            width: 100%;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.8rem 1.2rem !important;
            border-radius: 12px;
            transition: var(--transition);
            position: relative;
            margin: 0 0.2rem;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white !important;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            transform: translateY(-2px);
        }

        .user-info {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 0.8rem 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .user-info:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Container principal */
        .main-container {
            padding: 2.5rem 0;
        }

        .page-title {
            font-weight: 700;
            font-size: 2.5rem;
            color: #1e293b;
            margin-bottom: 2rem;
        }

        /* Cards modernos */
        .modern-card {
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: none;
            background: white;
            overflow: hidden;
            position: relative;
            transition: var(--transition);
        }

        .modern-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
        }

        /* Cards de progresso especiais */
        .progress-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .progress-card.cycle::before {
            background: var(--success-gradient);
        }

        .progress-card.bronze::before {
            background: var(--bronze-gradient);
        }

        .progress-card.gold::before {
            background: var(--gold-gradient);
        }

        /* Barras de progresso modernas */
        .progress {
            height: 12px;
            border-radius: 10px;
            background: rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 10px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.3) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0.3) 75%, transparent 75%);
            background-size: 20px 20px;
            animation: progress-animation 1s linear infinite;
        }

        @keyframes progress-animation {
            0% { transform: translateX(-20px); }
            100% { transform: translateX(20px); }
        }

        .progress-cycle .progress-bar {
            background: var(--success-gradient);
        }

        .progress-bronze .progress-bar {
            background: var(--bronze-gradient);
        }

        .progress-gold .progress-bar {
            background: var(--gold-gradient);
        }

        /* Ícones e textos */
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .icon-cycle {
            background: var(--success-gradient);
        }

        .icon-bronze {
            background: var(--bronze-gradient);
        }

        .icon-gold {
            background: var(--gold-gradient);
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .card-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        /* Histórico de metas */
        .history-item {
            padding: 1.5rem;
            border-radius: 15px;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        .history-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .history-date {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .history-description {
            color: #64748b;
            font-size: 0.85rem;
            margin: 0.5rem 0;
        }

        .history-value {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .history-value.achieved {
            color: #10b981;
        }

        .history-value.pending {
            color: #f59e0b;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .user-info {
                margin-top: 1rem;
                text-align: center;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .card-value {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg modern-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-scissors me-2"></i>CorteFácil
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="funcionarios.php">
                            <i class="bi bi-people me-1"></i>Funcionários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="metas.php">
                            <i class="bi bi-target me-1"></i>Metas
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="user-info me-3">
                        <i class="bi bi-person-circle me-2"></i>
                        <span style="color: white; font-weight: 600;">
                            <?php echo htmlspecialchars($_SESSION['nome']); ?>
                        </span>
                    </div>
                    <a href="../php/parceiro_login.php?logout=true" class="btn btn-logout">
                        <i class="bi bi-box-arrow-right me-1"></i>Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <h1 class="page-title">Metas</h1>

        <!-- Card Principal de Metas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card modern-card progress-card cycle">
                    <div class="card-body p-4">
                        <div id="metaBadge"></div>
                        <h4 class="card-title mb-4">Progresso do Ciclo (30 dias)</h4>
                        
                        <div class="progress progress-cycle">
                            <div class="progress-bar" id="progressoBar" role="progressbar"></div>
                        </div>

                        <div class="row text-center mt-4">
                            <div class="col-4">
                                <h5 id="totalAgendamentos">0</h5>
                                <small class="text-muted">Agendamentos</small>
                            </div>
                            <div class="col-4">
                                <h5 id="diasRestantes">0</h5>
                                <small class="text-muted">Dias Restantes</small>
                            </div>
                            <div class="col-4">
                                <h5 id="bonusAtual">R$ 0,00</h5>
                                <small class="text-muted">Bônus Atual</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Metas -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card modern-card progress-card bronze">
                    <div class="card-body p-4">
                        <div class="card-icon icon-bronze">
                            <i class="bi bi-award"></i>
                        </div>
                        <div class="card-value">R$ 50,00</div>
                        <div class="card-label">Meta Bronze</div>
                        <div class="card-subtitle">50 agendamentos</div>
                        <div id="meta50Status"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card modern-card progress-card gold">
                    <div class="card-body p-4">
                        <div class="card-icon icon-gold">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div class="card-value">R$ 150,00</div>
                        <div class="card-label">Meta Ouro</div>
                        <div class="card-subtitle">100 agendamentos</div>
                        <div id="meta100Status"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico de Metas -->
        <div class="card modern-card">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-clock-history me-2"></i>Histórico de Metas
                </h5>
                <div id="historicoMetas">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-3 text-muted">Carregando histórico...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function carregarMetas() {
            fetch('../php/salao_obter_metas.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    const meta = data.meta_atual;
                    const historico = data.historico;

                    // Atualiza o progresso
                    const progresso = (meta.agendamentos_confirmados / 100) * 100;
                    document.getElementById('progressoBar').style.width = Math.min(progresso, 100) + '%';

                    // Atualiza os números
                    document.getElementById('totalAgendamentos').textContent = meta.agendamentos_confirmados;
                    document.getElementById('diasRestantes').textContent = meta.dias_restantes;
                    document.getElementById('bonusAtual').textContent = 
                        'R$ ' + (meta.bonus_pago || '0,00');

                    // Atualiza o badge com design moderno
                    const metaBadge = document.getElementById('metaBadge');
                    if (meta.meta_100_atingida) {
                        metaBadge.innerHTML = `
                            <div class="alert alert-success border-0 rounded-4 mb-3" style="background: var(--gold-gradient); color: white;">
                                <i class="bi bi-trophy me-2"></i><strong>Meta Ouro Alcançada!</strong>
                            </div>
                        `;
                    } else if (meta.meta_50_atingida) {
                        metaBadge.innerHTML = `
                            <div class="alert alert-warning border-0 rounded-4 mb-3" style="background: var(--bronze-gradient); color: white;">
                                <i class="bi bi-award me-2"></i><strong>Meta Bronze Alcançada!</strong>
                            </div>
                        `;
                    }

                    // Atualiza status das metas com design moderno
                    document.getElementById('meta50Status').innerHTML = meta.meta_50_atingida ?
                        '<div class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i>Alcançada!</div>' :
                        '<div class="badge bg-light text-dark rounded-pill px-3 py-2">Faltam ' + (50 - meta.agendamentos_confirmados) + ' agendamentos</div>';

                    document.getElementById('meta100Status').innerHTML = meta.meta_100_atingida ?
                        '<div class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i>Alcançada!</div>' :
                        '<div class="badge bg-light text-dark rounded-pill px-3 py-2">Faltam ' + (100 - meta.agendamentos_confirmados) + ' agendamentos</div>';

                    // Atualiza histórico com design moderno
                    const historicoContainer = document.getElementById('historicoMetas');
                    historicoContainer.innerHTML = '';

                    if (historico.length === 0) {
                        historicoContainer.innerHTML = `
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                                <p class="text-muted">Nenhum histórico disponível</p>
                            </div>
                        `;
                    } else {
                        historico.forEach(item => {
                            const dataInicio = new Date(item.data_inicio).toLocaleDateString('pt-BR');
                            const dataFim = new Date(item.data_fim).toLocaleDateString('pt-BR');
                            
                            const statusClass = item.meta_100_atingida ? 'achieved' : 
                                              item.meta_50_atingida ? 'achieved' : 'pending';
                            const statusText = item.meta_100_atingida ? 'Meta Ouro' : 
                                             item.meta_50_atingida ? 'Meta Bronze' : 'Meta não atingida';
                            const statusIcon = item.meta_100_atingida ? 'bi-trophy' : 
                                             item.meta_50_atingida ? 'bi-award' : 'bi-x-circle';
                            
                            historicoContainer.innerHTML += `
                                <div class="history-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="history-date">
                                                <i class="bi bi-calendar3 me-2"></i>
                                                Ciclo: ${dataInicio} - ${dataFim}
                                            </div>
                                            <div class="history-description">
                                                <i class="bi bi-calendar-check me-1"></i>
                                                ${item.agendamentos_confirmados} agendamentos confirmados
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="history-value ${statusClass}">
                                                R$ ${item.bonus_pago}
                                            </div>
                                            <div class="badge bg-light text-dark rounded-pill px-2 py-1 mt-1">
                                                <i class="${statusIcon} me-1"></i>
                                                ${statusText}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    }
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        // Carrega os dados quando a página carregar
        document.addEventListener('DOMContentLoaded', carregarMetas);
    </script>
</body>
</html>