<?php
require_once '../php/conexao.php';
require_once '../php/verificar_sessao.php';

// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = getConexao();
    
    $sql = "SELECT s.*, 
           (SELECT COUNT(*) FROM profissionais p WHERE p.salao_id = s.id AND p.ativo = 1) as total_profissionais,
           (SELECT AVG(nota) FROM avaliacoes a WHERE a.salao_id = s.id) as media_avaliacoes
    FROM saloes s WHERE s.ativo = 1";

    $result = $pdo->query($sql);

    $saloes = [];

    if ($result && $result->rowCount() > 0) {
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // Buscar profissionais do salão
            $profissionais_sql = "SELECT id, nome FROM profissionais WHERE salao_id = ? AND ativo = 1";
            $stmt = $pdo->prepare($profissionais_sql);
            $stmt->bindValue(1, $row['id'], PDO::PARAM_INT);
            $stmt->execute();
            $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $row['profissionais'] = $profissionais;
            $saloes[] = $row;
        }
    }

} catch (Exception $e) {
    error_log("Erro no painel.php: " . $e->getMessage());
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Agende seu corte de cabelo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00a1ff;
            --secondary-color: #3e0d16;
            --accent-color: #f0f0f0;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            background-color: #f5f5f5;
        }
        
        /* Navbar */
        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
        }
        
        .nav-link.active {
            color: var(--primary-color) !important;
        }
        
        /* Banner */
        .banner {
            background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 30px;
            border-radius: 0;
        }
        
        .banner h1 {
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .banner p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        /* Cards de Salão */
        .salao-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 25px;
            border: none;
            height: 100%;
        }
        
        .salao-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .salao-img {
            height: 160px;
            background-size: cover;
            background-position: center;
        }
        
        .salao-info {
            padding: 15px;
        }
        
        .salao-nome {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: var(--text-color);
        }
        
        .salao-endereco {
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        
        .salao-avaliacao {
            color: #ff9800;
            margin-bottom: 8px;
        }
        
        .salao-profissionais {
            font-size: 0.85rem;
            color: var(--dark-gray);
        }
        
        .btn-agendar {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .btn-agendar:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        
        /* Seção de Serviços */
        .service-card {
            transition: transform 0.3s;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: none;
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        /* Profissionais */
        .professional-avatar {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
        }
        
        /* Horários */
        .time-slot {
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        
        .time-slot:hover {
            background-color: var(--medium-gray);
        }
        
        .time-slot.selected {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Agendamentos */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 12px;
            font-size: 0.8rem;
        }
        
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmado {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-realizado {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-cancelado {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .agendamento-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .agendamento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        /* Footer */
        .footer {
            background-color: #333;
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }
        
        .footer h5 {
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer ul {
            list-style: none;
            padding-left: 0;
        }
        
        .footer ul li {
            margin-bottom: 10px;
        }
        
        .footer ul li a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer ul li a:hover {
            color: white;
        }
        
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }
        
        .social-icons a:hover {
            color: var(--primary-color);
        }
        
        .copyright {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #444;
            font-size: 0.9rem;
            color: #aaa;
        }
        
        /* FullCalendar */
        .fc-theme-standard .fc-toolbar {
            margin-bottom: 1rem;
        }
        
        .fc .fc-button-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .fc .fc-button-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .fc .fc-daygrid-day.fc-day-today {
            background-color: rgba(234, 29, 44, 0.1);
        }
        
        .fc-day-future:not(.fc-day-other) {
            cursor: pointer;
        }
        
        .fc-day-future:not(.fc-day-other):hover {
            background-color: rgba(234, 29, 44, 0.05);
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .banner {
                padding: 40px 0;
            }
            
            .banner h1 {
                font-size: 1.8rem;
            }
            
            .banner p {
                font-size: 1rem;
            }
        }

        /* Agendamento Steps */
        .agendamento-steps {
            position: relative;
            padding: 0 20px;
        }
        
        .step {
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .step-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--light-gray);
            border: 2px solid var(--dark-gray);
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .step.active .step-circle {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-circle {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .step-text {
            font-size: 0.8rem;
            color: var(--dark-gray);
            margin-top: 5px;
        }
        
        .progress-line {
            position: absolute;
            top: 17px;
            left: 50px;
            right: 50px;
            height: 2px;
            background-color: var(--light-gray);
            z-index: 0;
        }
        
        .progress-line::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background-color: var(--primary-color);
            transition: width 0.3s;
            width: 0;
        }
        
        /* Modal de Agendamento */
        #agendamentoModal .modal-dialog {
            max-width: 800px;
        }
        
        #agendamentoModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        #agendamentoModal .modal-header {
            padding: 1.5rem;
            background-color: var(--light-gray);
            border-bottom: 1px solid var(--medium-gray);
        }
        
        #agendamentoModal .modal-body {
            padding: 1.5rem;
        }
        
        #agendamentoModal .modal-footer {
            padding: 1rem 1.5rem;
            background-color: var(--light-gray);
        }
        
        /* Etapas do agendamento */
        .agendamento-steps {
            margin-bottom: 2rem;
        }
        
        .step {
            position: relative;
            z-index: 1;
            flex: 1;
            text-align: center;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--light-gray);
            border: 2px solid var(--dark-gray);
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .step.active .step-circle {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }
        
        .step.completed .step-circle {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .step-text {
            font-size: 0.85rem;
            color: var(--dark-gray);
            margin-top: 8px;
            font-weight: 500;
        }
        
        .progress-line {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--medium-gray);
            z-index: 0;
        }
        
        .progress-line::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
            width: 0;
        }
        
        /* Cards no Modal */
        #agendamentoModal .service-card,
        #agendamentoModal .professional-card {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            height: 100%;
        }
        
        #agendamentoModal .service-card:hover,
        #agendamentoModal .professional-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        #agendamentoModal .service-card.selected,
        #agendamentoModal .professional-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(234, 29, 44, 0.05);
        }
        
        /* Calendário no Modal */
        #modalCalendar {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .fc-day-selected {
            background-color: rgba(234, 29, 44, 0.1) !important;
            border: 2px solid var(--primary-color) !important;
        }
        
        /* Horários no Modal */
        .time-slot {
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
        }
        
        .time-slot:hover {
            background-color: var(--light-gray);
            transform: translateY(-2px);
        }
        
        .time-slot.selected {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Resumo no Modal */
        #modalResumoAgendamento {
            background-color: var(--light-gray);
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        #modalResumoAgendamento h4 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        #modalResumoAgendamento .mb-3 {
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        #modalResumoAgendamento .mb-3:last-child {
            border-bottom: none;
        }
        
        /* Responsividade do Modal */
        @media (max-width: 768px) {
            #agendamentoModal .modal-dialog {
                margin: 0.5rem;
            }
            
            .step-text {
                font-size: 0.75rem;
            }
            
            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
            
            #agendamentoModal .service-card,
            #agendamentoModal .professional-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">CorteFácil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="novoAgendamentoLink"><i class="bi bi-calendar-plus"></i> Novo Agendamento</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="meusAgendamentosLink"><i class="bi bi-calendar-check"></i> Meus Agendamentos</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="input-group me-3">
                        <input type="text" class="form-control" id="searchSalao" placeholder="Buscar salão...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nome']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="perfilLink"><i class="bi bi-person"></i> Meu Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="logoutLink"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Banner Principal -->
    <div class="banner">
        <div class="container text-center">
            <h1>Agende seu corte de cabelo</h1>
            <p>Encontre os melhores salões e barbearias perto de você</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="bannerSearchInput" placeholder="Busque por salão ou serviço...">
                        <button class="btn btn-lg btn-agendar" type="button" id="bannerSearchButton">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border" style="color: var(--primary-color);" role="status"></div>
                    <p class="mt-2 mb-0">Carregando...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Agendamento -->
    <div class="modal fade" id="agendamentoModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agendar Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Etapas do agendamento -->
                    <div class="agendamento-steps mb-4">
                        <div class="d-flex justify-content-between position-relative">
                            <div class="step active" id="step1">
                                <div class="step-circle">1</div>
                                <div class="step-text">Serviço</div>
                            </div>
                            <div class="step" id="step2">
                                <div class="step-circle">2</div>
                                <div class="step-text">Profissional</div>
                            </div>
                            <div class="step" id="step3">
                                <div class="step-circle">3</div>
                                <div class="step-text">Data</div>
                            </div>
                            <div class="step" id="step4">
                                <div class="step-circle">4</div>
                                <div class="step-text">Horário</div>
                            </div>
                            <div class="step" id="step5">
                                <div class="step-circle">5</div>
                                <div class="step-text">Confirmação</div>
                            </div>
                            <div class="progress-line"></div>
                        </div>
                    </div>

                    <!-- Conteúdo das etapas -->
                    <div id="modalServicosSection">
                        <div class="row" id="modalServicosList"></div>
                    </div>

                    <div id="modalProfissionaisSection" style="display: none;">
                        <div class="row" id="modalProfissionaisList"></div>
                    </div>

                    <div id="modalDataSection" style="display: none;">
                        <div id="modalCalendar"></div>
                    </div>

                    <div id="modalHorarioSection" style="display: none;">
                        <div class="row" id="modalHorariosList"></div>
                    </div>

                    <div id="modalResumoSection" style="display: none;">
                        <div class="card">
                            <div class="card-body" id="modalResumoAgendamento"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="btnVoltar" style="display: none;">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnAvancar" style="display: none;">
                        Avançar <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="btnConfirmar" style="display: none;">
                        Confirmar Agendamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0" id="successMessage"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0" id="errorMessage"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pagamento do Agendamento</h5>
                </div>
                <div class="modal-body" id="paymentModalContent">
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Seção de Salões -->
        <div id="saloesSection">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Salões Disponíveis</h2>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort"></i> Ordenar por
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Mais Próximos</a></li>
                        <li><a class="dropdown-item" href="#">Melhor Avaliados</a></li>
                        <li><a class="dropdown-item" href="#">Ordem Alfabética</a></li>
                    </ul>
                </div>
            </div>
            <div class="row" id="saloesList"></div>
        </div>

        <!-- Seção de Serviços -->
        <div id="servicosSection" style="display: none;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-outline-secondary me-3" id="voltarParaSaloes">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
                <h2 class="mb-0">Serviços Disponíveis</h2>
            </div>
            <div class="row" id="servicosList"></div>
        </div>

        <!-- Seção de Profissionais -->
        <div id="profissionaisSection" style="display: none;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-outline-secondary me-3" id="voltarParaServicos">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
                <h2 class="mb-0">Escolha o Profissional</h2>
            </div>
            <div class="row" id="profissionaisList"></div>
        </div>

        <!-- Seção de Data -->
        <div id="dataSection" style="display: none;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-outline-secondary me-3" id="voltarParaProfissionais">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
                <h2 class="mb-0">Escolha a Data</h2>
            </div>
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div id="calendar" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Horário -->
        <div id="horarioSection" style="display: none;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-outline-secondary me-3" id="voltarParaData">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
                <h2 class="mb-0">Horários Disponíveis</h2>
            </div>
            <div class="row" id="horariosList"></div>
        </div>

        <!-- Seção de Resumo -->
        <div id="resumoSection" style="display: none;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-outline-secondary me-3" id="voltarParaHorarios">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
                <h2 class="mb-0">Resumo do Agendamento</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body" id="resumoAgendamento">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Meus Agendamentos -->
        <div id="meusAgendamentosSection" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Meus Agendamentos</h2>
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3" id="agendamentosInfo">Mostrando 0 de 0 agendamentos</span>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPrevPage" disabled>
                            <i class="bi bi-chevron-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnNextPage" disabled>
                            Próxima <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row" id="agendamentosList"></div>
            <div class="d-flex justify-content-center mt-4" id="paginationContainer" style="display: none;">
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination" id="paginationList">
                        <!-- Páginas serão inseridas dinamicamente -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <h5>CorteFácil</h5>
                    <p>A maneira mais fácil de agendar seu corte de cabelo e serviços de beleza.</p>
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Links Rápidos</h5>
                    <ul>
                        <li><a href="#">Início</a></li>
                        <li><a href="#">Meus Agendamentos</a></li>
                        <li><a href="#">Meu Perfil</a></li>
                        <li><a href="#">Ajuda</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Categorias</h5>
                    <ul>
                        <li><a href="#">Barbearia</a></li>
                        <li><a href="#">Salão de Beleza</a></li>
                        <li><a href="#">Manicure e Pedicure</a></li>
                        <li><a href="#">Depilação</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Contato</h5>
                    <ul>
                        <li><i class="bi bi-envelope me-2"></i> contato@cortefacil.com</li>
                        <li><i class="bi bi-telephone me-2"></i> (11) 99999-9999</li>
                        <li><i class="bi bi-geo-alt me-2"></i> São Paulo, SP</li>
                    </ul>
                </div>
            </div>
            <div class="copyright text-center">
                &copy; 2023 CorteFácil. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js"></script>
    <script src="js/cliente.js"></script>
</body>
</html>