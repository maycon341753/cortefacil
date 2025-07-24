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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">CorteFácil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="metas.php">Metas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="funcionarios.php">Funcionários</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                        Olá, <span id="nomeUsuario" style="cursor: pointer; text-decoration: underline; color: #ffffff;" 
                                   title="Clique para alterar sua senha"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                    </span>
                    <a href="../php/parceiro_login.php?logout=true" class="btn btn-outline-light">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4">Dashboard</h2>
        
        <div class="row g-4">
            <!-- Card de Agendamentos do Dia -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card h-100 bg-white">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check text-primary stat-icon"></i>
                        <h5 class="card-title">Agendamentos Hoje</h5>
                        <h3 class="card-text" id="agendamentosHoje">0</h3>
                    </div>
                </div>
            </div>

            <!-- Card de Faturamento do Dia -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card h-100 bg-white">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar text-success stat-icon"></i>
                        <h5 class="card-title">Faturamento Hoje</h5>
                        <h3 class="card-text" id="faturamentoHoje">R$ 0,00</h3>
                    </div>
                </div>
            </div>

            <!-- Card de Clientes Atendidos no Mês -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card h-100 bg-white">
                    <div class="card-body text-center">
                        <i class="bi bi-people text-info stat-icon"></i>
                        <h5 class="card-title">Clientes no Mês</h5>
                        <h3 class="card-text" id="clientesMes">0</h3>
                    </div>
                </div>
            </div>

            <!-- Card de Meta Mensal -->
            <div class="col-md-6 col-lg-3">
                <div class="card dashboard-card h-100 bg-white">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up text-warning stat-icon"></i>
                        <h5 class="card-title">Meta Mensal</h5>
                        <h3 class="card-text" id="metaMensal">0%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos e Tabelas -->
        <div class="row mt-5 g-4">
            <!-- Últimos Agendamentos -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Últimos Agendamentos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Serviço</th>
                                        <th>Data/Hora</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="ultimosAgendamentos">
                                    <!-- Será preenchido via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faturamento Semanal -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Faturamento Semanal</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoFaturamento"></canvas>
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