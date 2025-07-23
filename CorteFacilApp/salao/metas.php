<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Metas - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .progress {
            height: 20px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .meta-card {
            position: relative;
            overflow: hidden;
        }

        .meta-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }

        .meta-50 {
            background-color: #ffd700;
            color: #000;
        }

        .meta-100 {
            background-color: #00b894;
            color: #fff;
        }

        .meta-info {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }

        .meta-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .bonus-alert {
            background: linear-gradient(45deg, #2e86de, #54a0ff);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .historico-card {
            margin-top: 30px;
        }

        .historico-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .historico-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Barra de Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">CorteFácil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="funcionarios.php"><i class="fas fa-users"></i> Funcionários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="metas.php"><i class="fas fa-star"></i> Metas</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-12 mb-4">
                <h1 class="h3">Minhas Metas</h1>
                <p class="text-muted">Acompanhe seu progresso e bonificações do ciclo</p>
            </div>
        </div>

        <!-- Card Principal de Metas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card meta-card">
                    <div class="card-body">
                        <div id="metaBadge"></div>
                        <h4 class="card-title mb-4">Progresso do Ciclo (30 dias)</h4>
                        
                        <div class="progress">
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
                <div class="card">
                    <div class="card-body meta-info">
                        <i class="fas fa-star meta-icon text-warning"></i>
                        <h5>Meta Bronze</h5>
                        <p class="mb-2">50 agendamentos</p>
                        <h4 class="text-primary">R$ 50,00</h4>
                        <div id="meta50Status"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body meta-info">
                        <i class="fas fa-crown meta-icon text-success"></i>
                        <h5>Meta Ouro</h5>
                        <p class="mb-2">100 agendamentos</p>
                        <h4 class="text-primary">R$ 150,00</h4>
                        <div id="meta100Status"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico de Metas -->
        <div class="card historico-card">
            <div class="card-header">
                <h5 class="mb-0">Histórico de Metas</h5>
            </div>
            <div class="card-body" id="historicoMetas">
                <!-- Preenchido via JavaScript -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            fetch('../php/logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = '../parceiro_login.html';
                    }
                })
                .catch(error => console.error('Erro:', error));
        }


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
                    document.getElementById('progressoBar').className = 
                        `progress-bar ${progresso >= 100 ? 'bg-success' : 'bg-primary'}`;

                    // Atualiza os números
                    document.getElementById('totalAgendamentos').textContent = meta.agendamentos_confirmados;
                    document.getElementById('diasRestantes').textContent = meta.dias_restantes;
                    document.getElementById('bonusAtual').textContent = 
                        'R$ ' + (meta.bonus_pago || '0,00');

                    // Atualiza o badge
                    const metaBadge = document.getElementById('metaBadge');
                    if (meta.meta_100_atingida) {
                        metaBadge.innerHTML = '<span class="meta-badge meta-100">Meta Ouro Alcançada!</span>';
                    } else if (meta.meta_50_atingida) {
                        metaBadge.innerHTML = '<span class="meta-badge meta-50">Meta Bronze Alcançada!</span>';
                    }

                    // Atualiza status das metas
                    document.getElementById('meta50Status').innerHTML = meta.meta_50_atingida ?
                        '<span class="text-success"><i class="fas fa-check-circle"></i> Alcançada!</span>' :
                        '<span class="text-muted">Faltam ' + (50 - meta.agendamentos_confirmados) + ' agendamentos</span>';

                    document.getElementById('meta100Status').innerHTML = meta.meta_100_atingida ?
                        '<span class="text-success"><i class="fas fa-check-circle"></i> Alcançada!</span>' :
                        '<span class="text-muted">Faltam ' + (100 - meta.agendamentos_confirmados) + ' agendamentos</span>';

                    // Atualiza histórico
                    const historicoContainer = document.getElementById('historicoMetas');
                    historicoContainer.innerHTML = '';

                    if (historico.length === 0) {
                        historicoContainer.innerHTML = '<p class="text-center text-muted">Nenhum histórico disponível</p>';
                    } else {
                        historico.forEach(item => {
                            const dataInicio = new Date(item.data_inicio).toLocaleDateString('pt-BR');
                            const dataFim = new Date(item.data_fim).toLocaleDateString('pt-BR');
                            
                            historicoContainer.innerHTML += `
                                <div class="historico-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Ciclo: ${dataInicio} - ${dataFim}</h6>
                                            <small class="text-muted">${item.agendamentos_confirmados} agendamentos confirmados</small>
                                        </div>
                                        <div class="text-end">
                                            <h6 class="mb-1">R$ ${item.bonus_pago}</h6>
                                            <small class="text-${item.meta_100_atingida ? 'success' : 
                                                               item.meta_50_atingida ? 'warning' : 'danger'}">
                                                ${item.meta_100_atingida ? 'Meta Ouro' : 
                                                  item.meta_50_atingida ? 'Meta Bronze' : 'Meta não atingida'}
                                            </small>
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