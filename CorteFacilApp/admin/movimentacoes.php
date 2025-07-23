<?php
include '../php/verificar_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações Financeiras - CorteFácil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 0;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stats-card .card-body {
            padding: 25px;
        }
        
        .stats-card h3 {
            color: #2c3e50;
            font-weight: 700;
        }
        
        .stats-card .card-title {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 25px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }
        
        .logout-btn {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .logout-btn:hover {
            color: white;
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pago {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-cancelado {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="p-4">
            <h4 class="text-white mb-4">
                <i class="fas fa-cut"></i> CorteFácil
            </h4>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="painel.php" class="nav-link">
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
                <a href="movimentacoes.php" class="nav-link active">
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
            <p class="mb-1 text-white" id="adminNome">Administrador</p>
            <a href="#" class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">
                        <i class="fas fa-exchange-alt text-primary"></i>
                        Movimentações Financeiras
                    </h2>
                    <p class="text-muted">Acompanhe as movimentações financeiras e relatórios dos salões</p>
                </div>
            </div>

            <!-- Cards de Resumo -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Faturamento Hoje</h6>
                                    <h3 class="mb-0" id="faturamentoHoje">R$ 0,00</h3>
                                </div>
                                <i class="fas fa-dollar-sign text-success fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Faturamento Mensal</h6>
                                    <h3 class="mb-0" id="faturamentoMensal">R$ 0,00</h3>
                                </div>
                                <i class="fas fa-chart-line text-primary fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Agendamentos Hoje</h6>
                                    <h3 class="mb-0" id="agendamentosHoje">0</h3>
                                </div>
                                <i class="fas fa-calendar-check text-info fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Média Clientes/Salão</h6>
                                    <h3 class="mb-0" id="mediaClientesSalao">0</h3>
                                </div>
                                <i class="fas fa-users text-warning fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-section">
                <div class="row">
                    <div class="col-md-3">
                        <label for="filtroDataInicio" class="form-label">Data Início</label>
                        <input type="date" class="form-control" id="filtroDataInicio">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroDataFim" class="form-label">Data Fim</label>
                        <input type="date" class="form-control" id="filtroDataFim">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroSalao" class="form-label">Salão</label>
                        <select class="form-select" id="filtroSalao">
                            <option value="">Todos os Salões</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtroStatus" class="form-label">Status</label>
                        <select class="form-select" id="filtroStatus">
                            <option value="">Todos</option>
                            <option value="pago">Pago</option>
                            <option value="pendente">Pendente</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary me-2" onclick="aplicarFiltros()">
                            <i class="fas fa-filter"></i> Aplicar Filtros
                        </button>
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="limparFiltros()">
                            <i class="fas fa-times"></i> Limpar
                        </button>
                        <button type="button" class="btn btn-success" onclick="gerarRelatorio()">
                            <i class="fas fa-file-pdf"></i> Gerar Relatório PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Faturamento -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-container">
                        <h5 class="mb-3">
                            <i class="fas fa-chart-area text-primary"></i>
                            Faturamento dos Últimos 30 Dias
                        </h5>
                        <canvas id="faturamentoChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabela de Movimentações -->
            <div class="row">
                <div class="col-12">
                    <div class="table-container">
                        <h5 class="mb-3">
                            <i class="fas fa-list text-primary"></i>
                            Movimentações Recentes
                        </h5>
                        <div class="table-responsive">
                            <table id="movimentacoesTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Salão</th>
                                        <th>Profissional</th>
                                        <th>Serviço</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados carregados via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estatísticas por Salão -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="table-container">
                        <h5 class="mb-3">
                            <i class="fas fa-chart-bar text-primary"></i>
                            Estatísticas por Salão
                        </h5>
                        <div class="table-responsive">
                            <table id="estatisticasSaloesTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Salão</th>
                                        <th>Total Agendamentos</th>
                                        <th>Agendamentos Pagos</th>
                                        <th>Faturamento Total</th>
                                        <th>Média Clientes/Dia</th>
                                        <th>Ticket Médio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados carregados via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        let movimentacoesTable;
        let estatisticasSaloesTable;
        // Definir faturamentoChart como uma propriedade global do window para evitar problemas de escopo
        window.faturamentoChart = null;

        $(document).ready(function() {
            // Inicializar DataTables
            movimentacoesTable = $('#movimentacoesTable').DataTable({
                language: {
                    url: '../i18n/pt-BR.json'
                },
                order: [[0, 'desc']],
                pageLength: 25
            });

            estatisticasSaloesTable = $('#estatisticasSaloesTable').DataTable({
                language: {
                    url: '../i18n/pt-BR.json'
                },
                order: [[3, 'desc']],
                pageLength: 10
            });

            // Definir datas padrão (últimos 30 dias)
            const hoje = new Date();
            const trintaDiasAtras = new Date();
            trintaDiasAtras.setDate(hoje.getDate() - 30);
            
            $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
            $('#filtroDataInicio').val(trintaDiasAtras.toISOString().split('T')[0]);

            // Carregar dados iniciais
            carregarEstatisticas();
            carregarSaloes();
            carregarMovimentacoes();
            carregarEstatisticasSaloes();
            carregarGraficoFaturamento();
        });

        function carregarEstatisticas() {
            fetch('../php/admin_movimentacoes_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 'erro') {
                        $('#faturamentoHoje').text(data.faturamentoHoje || 'R$ 0,00');
                        $('#faturamentoMensal').text(data.faturamentoMensal || 'R$ 0,00');
                        $('#agendamentosHoje').text(data.agendamentosHoje || '0');
                        $('#mediaClientesSalao').text(data.mediaClientesSalao || '0');
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar estatísticas:', error);
                });
        }

        function carregarSaloes() {
            fetch('../php/admin_listar_saloes.php')
                .then(response => response.json())
                .then(data => {
                    const select = $('#filtroSalao');
                    select.empty().append('<option value="">Todos os Salões</option>');
                    
                    if (data.status !== 'erro' && data.saloes) {
                        data.saloes.forEach(salao => {
                            select.append(`<option value="${salao.id}">${salao.nome_fantasia}</option>`);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar salões:', error);
                });
        }

        function carregarMovimentacoes() {
            const filtros = {
                dataInicio: $('#filtroDataInicio').val(),
                dataFim: $('#filtroDataFim').val(),
                salaoId: $('#filtroSalao').val(),
                status: $('#filtroStatus').val()
            };

            fetch('../php/admin_listar_movimentacoes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(filtros)
            })
            .then(response => response.json())
            .then(data => {
                movimentacoesTable.clear();
                
                if (data.status !== 'erro' && data.movimentacoes) {
                    data.movimentacoes.forEach(mov => {
                        const statusBadge = getStatusBadge(mov.status_pagamento);
                        const acoes = `
                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalhes(${mov.id})" title="Ver Detalhes">
                                <i class="fas fa-eye"></i>
                            </button>
                        `;
                        
                        movimentacoesTable.row.add([
                            mov.id,
                            formatarData(mov.data),
                            mov.hora,
                            mov.cliente_nome,
                            mov.salao_nome,
                            mov.profissional_nome,
                            mov.servico_nome,
                            formatarMoeda(mov.taxa_servico),
                            statusBadge,
                            acoes
                        ]);
                    });
                }
                
                movimentacoesTable.draw();
            })
            .catch(error => {
                console.error('Erro ao carregar movimentações:', error);
            });
        }

        function carregarEstatisticasSaloes() {
            const filtros = {
                dataInicio: $('#filtroDataInicio').val(),
                dataFim: $('#filtroDataFim').val()
            };

            fetch('../php/admin_estatisticas_saloes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(filtros)
            })
            .then(response => response.json())
            .then(data => {
                estatisticasSaloesTable.clear();
                
                if (data.status !== 'erro' && data.estatisticas) {
                    data.estatisticas.forEach(stat => {
                        estatisticasSaloesTable.row.add([
                            stat.salao_nome,
                            stat.total_agendamentos,
                            stat.agendamentos_pagos,
                            formatarMoeda(stat.faturamento_total),
                            stat.media_clientes_dia,
                            formatarMoeda(stat.ticket_medio)
                        ]);
                    });
                }
                
                estatisticasSaloesTable.draw();
            })
            .catch(error => {
                console.error('Erro ao carregar estatísticas dos salões:', error);
            });
        }

        function carregarGraficoFaturamento() {
            // Obter o container do gráfico
            const chartContainer = document.getElementById('faturamentoChart').parentNode;
            
            // Adicionar indicador de carregamento
            chartContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Carregando dados...</p></div>';
            
            // Recriar o canvas imediatamente (sem setTimeout)
            chartContainer.innerHTML = '<canvas id="faturamentoChart" height="100"></canvas>';
            
            // Carregar os dados do gráfico
            fetch('../php/admin_grafico_faturamento.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Resposta da rede não foi ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status !== 'erro' && data.dados && data.dados.length > 0) {
                        criarGraficoFaturamento(data.dados);
                    } else {
                        // Mostrar mensagem de erro se não houver dados
                        document.getElementById('faturamentoChart').parentNode.innerHTML = '<div class="alert alert-warning">Não foi possível carregar os dados do gráfico.</div>';
                        console.warn('Dados do gráfico não disponíveis:', data);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar dados do gráfico:', error);
                    // Mostrar mensagem de erro em caso de falha
                    document.getElementById('faturamentoChart').parentNode.innerHTML = '<div class="alert alert-danger">Erro ao carregar dados do gráfico: ' + error.message + '</div>';
                });
        }

        function criarGraficoFaturamento(dados) {
            try {
                // Verificar se há dados válidos primeiro
                if (!dados || !Array.isArray(dados) || dados.length === 0) {
                    console.error('Dados inválidos para o gráfico');
                    const container = document.getElementById('faturamentoChart').parentNode;
                    container.innerHTML = '<div class="alert alert-warning">Não há dados de faturamento para exibir.</div>';
                    return;
                }
                
                // Obter o elemento canvas
                const canvas = document.getElementById('faturamentoChart');
                if (!canvas) {
                    console.error('Elemento canvas não encontrado');
                    return;
                }
                
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    console.error('Não foi possível obter o contexto 2d do canvas');
                    return;
                }
                
                // Destruir o gráfico anterior se existir para evitar vazamentos de memória
                if (window.faturamentoChart instanceof Chart) {
                    window.faturamentoChart.destroy();
                    window.faturamentoChart = null;
                }
                
                // Formatar os dados para o gráfico
                const labels = [];
                const values = [];
                
                // Processar os dados com validação
                dados.forEach(d => {
                    if (d && d.data) {
                        labels.push(formatarData(d.data));
                        
                        const valor = parseFloat(d.faturamento);
                        values.push(isNaN(valor) ? 0 : valor);
                    }
                });
                
                // Verificar novamente se temos dados válidos após o processamento
                if (labels.length === 0 || values.length === 0) {
                    console.error('Dados processados inválidos para o gráfico');
                    canvas.parentNode.innerHTML = '<div class="alert alert-warning">Não há dados de faturamento válidos para exibir.</div>';
                    return;
                }
                
                // Criar o novo gráfico
                window.faturamentoChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Faturamento Diário',
                            data: values,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toFixed(2);
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Faturamento: R$ ' + context.parsed.y.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
                
                console.log('Gráfico de faturamento criado com sucesso');
            } catch (error) {
                console.error('Erro ao criar gráfico de faturamento:', error);
                const canvas = document.getElementById('faturamentoChart');
                if (canvas && canvas.parentNode) {
                    canvas.parentNode.innerHTML = '<div class="alert alert-danger">Erro ao criar o gráfico de faturamento: ' + error.message + '</div>';
                }
            }
        }

        function aplicarFiltros() {
            carregarMovimentacoes();
            carregarEstatisticasSaloes();
            carregarEstatisticas();
            carregarGraficoFaturamento(); // Recarregar o gráfico quando os filtros forem aplicados
        }

        function limparFiltros() {
            const hoje = new Date();
            const trintaDiasAtras = new Date();
            trintaDiasAtras.setDate(hoje.getDate() - 30);
            
            $('#filtroDataInicio').val(trintaDiasAtras.toISOString().split('T')[0]);
            $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
            $('#filtroSalao').val('');
            $('#filtroStatus').val('');
            
            aplicarFiltros();
        }

        function gerarRelatorio() {
            const filtros = {
                dataInicio: $('#filtroDataInicio').val(),
                dataFim: $('#filtroDataFim').val(),
                salaoId: $('#filtroSalao').val(),
                status: $('#filtroStatus').val()
            };

            const params = new URLSearchParams(filtros);
            window.open(`../php/admin_gerar_relatorio_pdf.php?${params}`, '_blank');
        }

        function verDetalhes(agendamentoId) {
            // Implementar modal com detalhes do agendamento
            alert('Funcionalidade de detalhes será implementada em breve. ID: ' + agendamentoId);
        }

        function getStatusBadge(status) {
            const badges = {
                'pago': '<span class="status-badge status-pago">Pago</span>',
                'pendente': '<span class="status-badge status-pendente">Pendente</span>',
                'cancelado': '<span class="status-badge status-cancelado">Cancelado</span>'
            };
            return badges[status] || '<span class="status-badge">Desconhecido</span>';
        }

        function formatarData(data) {
            if (!data) return '';
            
            try {
                // Verificar se a data já está no formato dd/mm/yyyy
                if (data.includes('/')) {
                    return data;
                }
                
                // Tentar converter a data para objeto Date
                const dataObj = new Date(data + 'T00:00:00');
                if (isNaN(dataObj.getTime())) {
                    // Se a data for inválida, tentar outro formato
                    const partes = data.split('-');
                    if (partes.length === 3) {
                        return `${partes[2]}/${partes[1]}/${partes[0]}`;
                    }
                    return data; // Retornar a string original se não conseguir converter
                }
                
                // Formatar a data no padrão brasileiro (dd/mm/yyyy)
                const dia = dataObj.getDate().toString().padStart(2, '0');
                const mes = (dataObj.getMonth() + 1).toString().padStart(2, '0');
                const ano = dataObj.getFullYear();
                
                return `${dia}/${mes}/${ano}`;
            } catch (e) {
                console.error('Erro ao formatar data:', e);
                return data;
            }
        }

        function formatarMoeda(valor) {
            if (valor === null || valor === undefined) return 'R$ 0,00';
            
            try {
                // Converter para número se for string
                const numero = typeof valor === 'string' ? parseFloat(valor.replace(/[^0-9,.-]/g, '').replace(',', '.')) : parseFloat(valor);
                
                if (isNaN(numero)) return 'R$ 0,00';
                
                // Formatar com separador de milhares e duas casas decimais
                return 'R$ ' + numero.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            } catch (e) {
                console.error('Erro ao formatar moeda:', e);
                return 'R$ 0,00';
            }
        }

        function logout() {
            if (confirm('Deseja realmente sair?')) {
                window.location.href = '../php/admin_logout.php';
            }
        }
    </script>
</body>
</html>