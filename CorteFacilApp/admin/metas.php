<?php
include '../php/verificar_admin.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas dos Salões - CorteFácil</title>
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

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
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
            margin-bottom: 20px;
        }

        .progress {
            height: 10px;
            margin-top: 10px;
        }

        .bonus-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .bonus-50 {
            background-color: #ffd700;
            color: #000;
        }

        .bonus-150 {
            background-color: #00b894;
            color: #fff;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            
            .main-content {
                margin-left: 0;
            }
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
                <a href="metas.php" class="nav-link active">
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
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="h3">Metas dos Salões</h1>
                    <p class="text-muted">Acompanhamento de metas e bonificações dos ciclos de 30 dias</p>
                </div>
            </div>

            <!-- Cards de Resumo -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total de Salões</h5>
                            <h2 class="mb-0" id="totalSaloes">0</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Salões com Meta 50+</h5>
                            <h2 class="mb-0" id="saloesMeta50">0</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Salões com Meta 100+</h5>
                            <h2 class="mb-0" id="saloesMeta100">0</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Salões -->
            <div class="row" id="listaSaloes">
                <!-- Preenchido via JavaScript -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para carregar as metas
        function carregarMetas() {
            fetch('../php/admin_listar_metas.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalSaloes').textContent = data.resumo.total_saloes;
                document.getElementById('saloesMeta50').textContent = data.resumo.meta_50;
                document.getElementById('saloesMeta100').textContent = data.resumo.meta_100;

                const container = document.getElementById('listaSaloes');
                container.innerHTML = '';

                data.saloes.forEach(salao => {
                    const progresso = (salao.agendamentos_mes / 100) * 100;
                    const bonusClass = salao.meta_100_atingida ? 'bonus-150' : 
                                     salao.meta_50_atingida ? 'bonus-50' : '';
                    const bonusText = salao.meta_100_atingida ? 'Bônus R$ 150,00' : 
                                    salao.meta_50_atingida ? 'Bônus R$ 50,00' : '';
                    
                    container.innerHTML += `
                        <div class="col-md-6 mb-4">
                            <div class="card position-relative">
                                ${bonusText ? `<span class="bonus-badge ${bonusClass}">${bonusText}</span>` : ''}
                                <div class="card-body">
                                    <h5 class="card-title">${salao.nome_fantasia}</h5>
                                    <p class="mb-2">
                                        <i class="fas fa-calendar-check me-2"></i>
                                        ${salao.agendamentos_mes} agendamentos confirmados
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-clock me-2"></i>
                                        ${salao.dias_restantes} dias restantes no ciclo
                                    </p>
                                    <div class="progress">
                                        <div class="progress-bar ${progresso >= 100 ? 'bg-success' : 'bg-primary'}" 
                                             role="progressbar" 
                                             style="width: ${Math.min(progresso, 100)}%">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <small class="text-muted">Meta 50: ${salao.meta_50_atingida ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>'}</small>
                                        <small class="text-muted">Meta 100: ${salao.meta_100_atingida ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>'}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            })
            .catch(error => console.error('Erro:', error));
        }

        // Carrega os dados quando a página carregar
        document.addEventListener('DOMContentLoaded', carregarMetas);
    </script>
</body>
</html>
