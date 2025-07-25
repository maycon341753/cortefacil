<?php
include '../php/verificar_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background-color: #2e86de;
            padding: 20px;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        .stats-card {
            background: linear-gradient(45deg, #2e86de, #54a0ff);
            color: white;
        }

        .stats-card i {
            font-size: 2rem;
            opacity: 0.8;
        }

        .user-info {
            padding: 15px 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.active {
                margin-left: var(--sidebar-width);
            }
        }

        .logout-btn {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            background-color: rgba(255,255,255,0.1);
            margin-top: 10px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>CorteFácil</h3>
            <p class="mb-0">Painel Administrativo</p>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="painel.php" class="nav-link active">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="saloes.php" class="nav-link">
                    <i class="fas fa-store"></i>
                    Salões
                </a>
            </li>
            <li class="nav-item">
                <a href="metas.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    Metas
                </a>
            </li>
            <li class="nav-item">
                <a href="movimentacoes.php" class="nav-link">
                    <i class="fas fa-exchange-alt"></i>
                    Movimentações
                </a>
            </li>
            <li class="nav-item">
                <a href="promocoes.php" class="nav-link">
                    <i class="fas fa-tags"></i>
                    Promoções
                </a>
            </li>
        </ul>

        <div class="user-info mt-auto">
            <div class="dropdown">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-2"></i>
                    <span id="adminNome">Administrador</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="perfilDropdown">
                    <li><a class="dropdown-item" href="#" onclick="abrirModalPerfil()">
                        <i class="fas fa-user-edit me-2"></i>Editar Perfil
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="abrirModalSenha()">
                        <i class="fas fa-key me-2"></i>Alterar Senha
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="logout()">
                        <i class="fas fa-sign-out-alt me-2"></i>Sair
                    </a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3">Dashboard</h1>
                    <p class="text-muted">Bem-vindo ao painel administrativo</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Total de Salões</h6>
                                    <h3 class="mb-0" id="totalSaloes">Carregando...</h3>
                                </div>
                                <i class="fas fa-store"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Agendamentos Pagos Hoje</h6>
                                    <h3 class="mb-0" id="agendamentosHoje">0</h3>
                                </div>
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Valor Hoje</h6>
                                    <h3 class="mb-0" id="valorAgendamentosHoje">R$ 0,00</h3>
                                </div>
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Promoções Ativas</h6>
                                    <h3 class="mb-0" id="promocoesAtivas">0</h3>
                                </div>
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Faturamento Mensal</h6>
                                    <h3 class="mb-0" id="faturamentoMensal">R$ 0,00</h3>
                                </div>
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Últimos Agendamentos</h5>
                            <a href="movimentacoes.php" class="btn btn-sm btn-primary">Ver Todos</a>
                        </div>
                        <div class="card-body" id="ultimosAgendamentos">
                            <p class="text-muted text-center">Carregando...</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Salões Destaque</h5>
                            <a href="metas.php" class="btn btn-sm btn-primary">Ver Todas</a>
                        </div>
                        <div class="card-body" id="metasMes">
                            <p class="text-muted text-center">Carregando...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarPerfilLabel">
                        <i class="fas fa-user-edit me-2"></i>Editar Perfil
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPerfil">
                        <div class="mb-3">
                            <label for="perfilNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="perfilNome" required>
                        </div>
                        <div class="mb-3">
                            <label for="perfilEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="perfilEmail" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarPerfil()">
                        <i class="fas fa-save me-2"></i>Salvar
                    </button>
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
                        <i class="fas fa-key me-2"></i>Alterar Senha
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAlterarSenha">
                        <div class="mb-3">
                            <label for="senhaAtual" class="form-label">Senha Atual</label>
                            <input type="password" class="form-control" id="senhaAtual" required>
                        </div>
                        <div class="mb-3">
                            <label for="novaSenha" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="novaSenha" required minlength="6">
                            <div class="form-text">A senha deve ter pelo menos 6 caracteres</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmarSenha" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="confirmarSenha" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="alterarSenha()">
                        <i class="fas fa-key me-2"></i>Alterar Senha
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Carregar dashboard quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            carregarDashboard();
        });

        // Função para fazer logout
        function logout() {
            // Limpa a sessão
            fetch('http://localhost:8000/CorteFacilApp/php/admin_logout.php', {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'ok') {
                    window.location.href = '../admin_login.html';
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        // Função para carregar os dados do dashboard
        function carregarDashboard() {
            // Carregar dados do administrador
            carregarPerfilAdmin();

            // Carregar dados das estatísticas
            console.log('Iniciando carregamento das estatísticas...');
            fetch('../php/admin_dashboard_stats.php', {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Status da resposta:', response.status);
                console.log('Headers da resposta:', response.headers);
                return response.text().then(text => {
                    console.log('Resposta bruta:', text);
                    return JSON.parse(text);
                });
            })
            .then(data => {
                console.log('Dados processados:', data);
                document.getElementById('totalSaloes').textContent = data.totalSaloes || '0';
                document.getElementById('agendamentosHoje').textContent = data.agendamentosHoje || '0';
                document.getElementById('valorAgendamentosHoje').textContent = data.valorAgendamentosHoje || 'R$ 0,00';
                document.getElementById('promocoesAtivas').textContent = data.promocoesAtivas || '0';
                document.getElementById('faturamentoMensal').textContent = data.faturamentoMensal || 'R$ 0,00';
            })
            .catch(error => {
                console.error('Erro detalhado ao carregar estatísticas:', error);
                document.getElementById('totalSaloes').textContent = '0';
                document.getElementById('agendamentosHoje').textContent = '0';
                document.getElementById('valorAgendamentosHoje').textContent = 'R$ 0,00';
                document.getElementById('promocoesAtivas').textContent = '0';
                document.getElementById('faturamentoMensal').textContent = 'R$ 0,00';
            });

            // Carregar últimos agendamentos
            fetch('../php/admin_ultimos_agendamentos.php', {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('ultimosAgendamentos');
                
                // Verifica se data é um array válido
                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">Nenhum agendamento recente</p>';
                    return;
                }
                
                const html = data.map(agendamento => {
                    // Determinar a cor do badge para status de pagamento
                    let badgeClass = 'bg-secondary';
                    if (agendamento.status_pagamento === 'pago') {
                        badgeClass = 'bg-success';
                    } else if (agendamento.status_pagamento === 'pendente') {
                        badgeClass = 'bg-warning text-dark';
                    }
                    
                    return `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">${agendamento.cliente_nome}</h6>
                            <small class="text-muted">${agendamento.salao_nome}</small>
                        </div>
                        <div class="text-end">
                            <div class="mb-1">${agendamento.data} ${agendamento.hora}</div>
                            <div>
                                <span class="badge bg-primary me-1">${agendamento.status}</span>
                                <span class="badge ${badgeClass}">${agendamento.status_pagamento || 'pendente'}</span>
                            </div>
                        </div>
                    </div>
                    `;
                }).join('');
                
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar agendamentos:', error);
                document.getElementById('ultimosAgendamentos').innerHTML = '<p class="text-muted text-center">Erro ao carregar dados</p>';
            });

            // Carregar metas do mês
            fetch('../php/admin_metas_mes.php', {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('metasMes');
                
                // Verifica se data é um array válido
                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">Nenhum salão com mais de 5 cortes este mês</p>';
                    return;
                }
                
                const html = data.map(salao => `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">${salao.salao_nome}</h6>
                            <small class="text-muted">Salão destaque do mês</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success">${salao.total_cortes} cortes</span>
                        </div>
                    </div>
                `).join('');
                
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar metas:', error);
                document.getElementById('metasMes').innerHTML = '<p class="text-muted text-center">Erro ao carregar dados</p>';
            });
        }

        // Função para carregar dados do perfil do administrador
        function carregarPerfilAdmin() {
            fetch('../php/admin_obter_perfil.php', {
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    document.getElementById('adminNome').textContent = data.admin.nome;
                    // Armazenar dados para uso nos modais
                    window.adminData = data.admin;
                } else {
                    console.error('Erro ao carregar perfil:', data.message);
                    document.getElementById('adminNome').textContent = 'Administrador';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar perfil:', error);
                document.getElementById('adminNome').textContent = 'Administrador';
            });
        }

        // Função para abrir modal de edição de perfil
        function abrirModalPerfil() {
            if (window.adminData) {
                document.getElementById('perfilNome').value = window.adminData.nome;
                document.getElementById('perfilEmail').value = window.adminData.email;
            }
            new bootstrap.Modal(document.getElementById('modalEditarPerfil')).show();
        }

        // Função para abrir modal de alteração de senha
        function abrirModalSenha() {
            // Limpar campos
            document.getElementById('formAlterarSenha').reset();
            new bootstrap.Modal(document.getElementById('modalAlterarSenha')).show();
        }

        // Função para salvar perfil
        function salvarPerfil() {
            const nome = document.getElementById('perfilNome').value.trim();
            const email = document.getElementById('perfilEmail').value.trim();

            if (!nome || !email) {
                alert('Por favor, preencha todos os campos');
                return;
            }

            if (!email.includes('@')) {
                alert('Por favor, insira um email válido');
                return;
            }

            const dados = {
                nome: nome,
                email: email
            };

            fetch('../php/admin_atualizar_perfil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    alert('Perfil atualizado com sucesso!');
                    // Atualizar dados na tela
                    document.getElementById('adminNome').textContent = data.admin.nome;
                    window.adminData = data.admin;
                    // Fechar modal
                    bootstrap.Modal.getInstance(document.getElementById('modalEditarPerfil')).hide();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar perfil');
            });
        }

        // Função para alterar senha
        function alterarSenha() {
            const senhaAtual = document.getElementById('senhaAtual').value;
            const novaSenha = document.getElementById('novaSenha').value;
            const confirmarSenha = document.getElementById('confirmarSenha').value;

            if (!senhaAtual || !novaSenha || !confirmarSenha) {
                alert('Por favor, preencha todos os campos');
                return;
            }

            if (novaSenha.length < 6) {
                alert('A nova senha deve ter pelo menos 6 caracteres');
                return;
            }

            if (novaSenha !== confirmarSenha) {
                alert('A confirmação da senha não confere');
                return;
            }

            const dados = {
                nome: window.adminData ? window.adminData.nome : '',
                email: window.adminData ? window.adminData.email : '',
                senha_atual: senhaAtual,
                nova_senha: novaSenha
            };

            fetch('../php/admin_atualizar_perfil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    alert('Senha alterada com sucesso!');
                    // Fechar modal e limpar campos
                    bootstrap.Modal.getInstance(document.getElementById('modalAlterarSenha')).hide();
                    document.getElementById('formAlterarSenha').reset();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao alterar senha');
            });
        }

        // Toggle sidebar em dispositivos móveis (apenas se o elemento existir)
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
                document.querySelector('.main-content').classList.toggle('active');
            });
        }
    </script>
</body>
</html>
