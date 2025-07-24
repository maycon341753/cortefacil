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
    <title>Gerenciar Serviços - CorteFácil</title>
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
            text-align: center;
        }

        /* Cards de serviços */
        .service-card {
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            border: none;
            background: white;
            overflow: hidden;
            position: relative;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
        }

        .service-card .card-body {
            padding: 2rem;
        }

        .service-name {
            font-weight: 600;
            font-size: 1.2rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .service-price {
            font-weight: 700;
            font-size: 1.5rem;
            color: #059669;
            margin-bottom: 0.5rem;
        }

        .service-duration {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .service-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .status-ativo {
            background: #dcfce7;
            color: #166534;
        }

        .status-inativo {
            background: #fef2f2;
            color: #991b1b;
        }

        /* Botões modernos */
        .btn-modern {
            border-radius: 12px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
            color: white;
        }

        .btn-warning-modern {
            background: var(--warning-gradient);
            color: white;
        }

        .btn-warning-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 233, 123, 0.4);
            color: white;
        }

        .btn-danger-modern {
            background: var(--secondary-gradient);
            color: white;
        }

        .btn-danger-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(240, 147, 251, 0.4);
            color: white;
        }

        /* Modal moderno */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--card-shadow-hover);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 0.8rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        /* Loading */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading.active {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Alert flutuante */
        .alert-float {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            display: none;
            min-width: 300px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
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
            
            .service-card .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Loading -->
    <div class="loading">
        <div class="spinner"></div>
    </div>

    <!-- Alert flutuante -->
    <div class="alert alert-float" role="alert"></div>

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
                        <a class="nav-link" href="metas.php">
                            <i class="bi bi-target me-1"></i>Metas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="servicos.php">
                            <i class="bi bi-scissors me-1"></i>Serviços
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title mb-0">Gerenciar Serviços</h1>
            <button class="btn btn-primary-modern btn-modern" data-bs-toggle="modal" data-bs-target="#modalServico">
                <i class="bi bi-plus-circle me-2"></i>Novo Serviço
            </button>
        </div>

        <!-- Lista de Serviços -->
        <div class="row g-4" id="servicosList">
            <!-- Os serviços serão carregados aqui via JavaScript -->
        </div>
    </div>

    <!-- Modal para Adicionar/Editar Serviço -->
    <div class="modal fade" id="modalServico" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalServicoTitle">Novo Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formServico">
                        <input type="hidden" id="servicoId" name="id">
                        
                        <div class="mb-3">
                            <label for="nomeServico" class="form-label">Nome do Serviço</label>
                            <input type="text" class="form-control" id="nomeServico" name="nome" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="precoServico" class="form-label">Preço (R$)</label>
                                    <input type="number" class="form-control" id="precoServico" name="preco" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duracaoServico" class="form-label">Duração (minutos)</label>
                                    <input type="number" class="form-control" id="duracaoServico" name="duracao_minutos" min="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descricaoServico" class="form-label">Descrição (opcional)</label>
                            <textarea class="form-control" id="descricaoServico" name="descricao" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ativoServico" name="ativo" checked>
                                <label class="form-check-label" for="ativoServico">
                                    Serviço ativo (visível para clientes)
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-modern btn-modern" onclick="salvarServico()">
                        <i class="bi bi-check-circle me-1"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/servicos.js"></script>
</body>
</html>