<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../php/conexao.php';

// Verifica se o usuário está autenticado e é do tipo salão
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    header('Location: ../parceiro_login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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

        #nomeUsuario {
            color: white !important;
            font-weight: 600;
            text-decoration: none !important;
            cursor: pointer;
            transition: var(--transition);
        }

        #nomeUsuario:hover {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
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

        /* Dashboard Cards Modernos */
        .dashboard-card {
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: none;
            background: white;
            overflow: hidden;
            position: relative;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
        }

        .dashboard-card .card-body {
            padding: 2rem;
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-title {
            font-weight: 600;
            color: #64748b;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .card-text {
            font-weight: 700;
            font-size: 2.2rem;
            color: #1e293b;
            margin: 0;
        }

        /* Cores específicas para cada card */
        .card-agendamentos::before { background: var(--primary-gradient); }
        .card-agendamentos .stat-icon { background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .card-faturamento::before { background: var(--success-gradient); }
        .card-faturamento .stat-icon { background: var(--success-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .card-clientes::before { background: var(--secondary-gradient); }
        .card-clientes .stat-icon { background: var(--secondary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .card-meta::before { background: var(--warning-gradient); }
        .card-meta .stat-icon { background: var(--warning-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* Container principal */
        .main-container {
            padding: 2.5rem 0;
        }

        .page-title {
            font-weight: 700;
            font-size: 2.5rem;
            color: #1e293b;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Tabelas e gráficos */
        .table-card {
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: none;
            overflow: hidden;
        }

        .table-card .card-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 1.5rem;
        }

        .table-card .card-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .table {
            margin: 0;
        }

        .table th {
            border: none;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }

        .table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
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
            
            .dashboard-card .card-body {
                padding: 1.5rem;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="metas.php">
                            <i class="bi bi-target me-1"></i>Metas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="funcionarios.php">
                            <i class="bi bi-people me-1"></i>Funcionários
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="user-info me-3">
                        <i class="bi bi-person-circle me-2"></i>
                        <span id="nomeUsuario" title="Clique para alterar sua senha">
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
        <h1 class="page-title">Dashboard</h1>
        
        <!-- Cards de Estatísticas -->
        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-agendamentos h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check stat-icon"></i>
                        <h6 class="card-title">Agendamentos Hoje</h6>
                        <p class="card-text" id="agendamentosHoje">0</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-faturamento h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar stat-icon"></i>
                        <h6 class="card-title">Faturamento Hoje</h6>
                        <p class="card-text" id="faturamentoHoje">R$ 0,00</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-clientes h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people stat-icon"></i>
                        <h6 class="card-title">Clientes no Mês</h6>
                        <p class="card-text" id="clientesMes">0</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card dashboard-card card-meta h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up stat-icon"></i>
                        <h6 class="card-title">Meta Mensal</h6>
                        <p class="card-text" id="metaMensal">0%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Últimos Agendamentos e Gráfico -->
        <div class="row g-4">
            <!-- Últimos Agendamentos -->
            <div class="col-lg-8">
                <div class="card table-card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history me-2"></i>Últimos Agendamentos</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Serviço</th>
                                        <th>Data/Hora</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="ultimosAgendamentos">
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faturamento Semanal -->
            <div class="col-lg-4">
                <div class="card table-card">
                    <div class="card-header">
                        <h5><i class="bi bi-bar-chart me-2"></i>Faturamento Semanal</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="faturamentoChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alterar Senha -->
    <div class="modal fade" id="modalAlterarSenha" tabindex="-1" aria-labelledby="modalAlterarSenhaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlterarSenhaLabel">
                        <i class="bi bi-key me-2"></i>Alterar Senha
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAlterarSenha">
                        <div class="mb-3">
                            <label for="senhaAtual" class="form-label">Senha Atual</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="senhaAtual" name="senha_atual" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('senhaAtual')">
                                    <i class="bi bi-eye" id="iconSenhaAtual"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="novaSenha" class="form-label">Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="novaSenha" name="nova_senha" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('novaSenha')">
                                    <i class="bi bi-eye" id="iconNovaSenha"></i>
                                </button>
                            </div>
                            <div class="form-text">A senha deve ter pelo menos 6 caracteres.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmarSenha" class="form-label">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmarSenha" name="confirmar_senha" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmarSenha')">
                                    <i class="bi bi-eye" id="iconConfirmarSenha"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cadastro de Funcionários -->
    <div class="modal fade" id="modalFuncionarios" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastro de Funcionário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formFuncionario">
                        <div class="mb-3">
                            <label for="nomeFuncionario" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nomeFuncionario" required>
                        </div>
                        <div class="mb-3">
                            <label for="especialidadeFuncionario" class="form-label">Especialidade</label>
                            <input type="text" class="form-control" id="especialidadeFuncionario" required>
                        </div>
                        <div class="mb-3">
                            <label for="valorFuncionario" class="form-label">Valor do Serviço (R$)</label>
                            <input type="number" class="form-control" id="valorFuncionario" step="0.01" min="0" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Função para carregar os dados do dashboard
        async function carregarDadosDashboard() {
            try {
                const response = await fetch('../php/parceiro_dashboard_stats.php');
                const data = await response.json();

                if (data.status === 'success') {
                    // Atualiza os cards
                    document.getElementById('agendamentosHoje').textContent = data.agendamentos_hoje;
                    document.getElementById('faturamentoHoje').textContent = 
                        new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })
                        .format(data.faturamento_hoje);
                    document.getElementById('clientesMes').textContent = data.clientes_mes;
                    document.getElementById('metaMensal').textContent = `${data.meta_mensal}%`;

                    // Atualiza a tabela de últimos agendamentos
                    const tbody = document.getElementById('ultimosAgendamentos');
                    tbody.innerHTML = '';
                    
                    data.ultimos_agendamentos.forEach(agendamento => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${agendamento.cliente}</td>
                            <td>${agendamento.servico}</td>
                            <td>${agendamento.data_hora}</td>
                            <td><span class="badge bg-${agendamento.status === 'Concluído' ? 'success' : 'warning'}">${agendamento.status}</span></td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Atualiza o gráfico de faturamento
                    const ctx = document.getElementById('graficoFaturamento').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.faturamento_semanal.map(item => item.dia),
                            datasets: [{
                                label: 'Faturamento',
                                data: data.faturamento_semanal.map(item => item.valor),
                                borderColor: 'rgb(75, 192, 192)',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('pt-BR', {
                                                style: 'currency',
                                                currency: 'BRL'
                                            }).format(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar dados:', error);
            }
        }

        // Carrega os dados quando a página carregar
        document.addEventListener('DOMContentLoaded', carregarDadosDashboard);

        // Função para cadastrar funcionário
        document.getElementById('formFuncionario').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const funcionario = {
                nome: document.getElementById('nomeFuncionario').value,
                especialidade: document.getElementById('especialidadeFuncionario').value,
                valor: document.getElementById('valorFuncionario').value
            };

            try {
                const response = await fetch('../php/parceiro_salvar_profissional.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(funcionario)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    alert('Funcionário cadastrado com sucesso!');
                    document.getElementById('formFuncionario').reset();
                    const modalFuncionarios = bootstrap.Modal.getInstance(document.getElementById('modalFuncionarios'));
                    modalFuncionarios.hide();
                } else {
                    alert('Erro ao cadastrar funcionário: ' + data.message);
                }
            } catch (error) {
                console.error('Erro ao cadastrar funcionário:', error);
                alert('Erro ao cadastrar funcionário. Tente novamente.');
            }
        });

        // Funcionalidade de alteração de senha
    $(document).on('click', '#nomeUsuario', function(e) {
        e.preventDefault();
        console.log('Clique no nome do usuário detectado');
        $('#modalAlterarSenha').modal('show');
    });

    // Função para alternar visibilidade da senha
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById('icon' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1));
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    // Submissão do formulário de alteração de senha
    $('#formAlterarSenha').on('submit', function(e) {
        e.preventDefault();
        
        const senhaAtual = $('#senhaAtual').val();
        const novaSenha = $('#novaSenha').val();
        const confirmarSenha = $('#confirmarSenha').val();
        
        // Validações
        if (novaSenha.length < 6) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'A nova senha deve ter pelo menos 6 caracteres.'
            });
            return;
        }
        
        if (novaSenha !== confirmarSenha) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'As senhas não coincidem.'
            });
            return;
        }
        
        // Enviar dados via AJAX
        $.ajax({
            url: '../php/parceiro_alterar_senha.php',
            type: 'POST',
            data: {
                senha_atual: senhaAtual,
                nova_senha: novaSenha,
                confirmar_senha: confirmarSenha
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: response.message
                    }).then(() => {
                        $('#modalAlterarSenha').modal('hide');
                        $('#formAlterarSenha')[0].reset();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao alterar senha. Tente novamente.'
                });
            }
        });
    });
    </script>
</body>
</html>