<?php
session_start();

// Verificar se o usuário está logado e é do tipo 'salao'
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    header('Location: parceiro_login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Painel do Parceiro</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            position: fixed;
            left: 0;
        }
        .sidebar .nav-link {
            color: white;
            margin-bottom: 10px;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar {
            margin-left: 250px;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .main-content, .navbar {
                margin-left: 0;
            }
            .sidebar.show {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" style="width: 250px;">
        <div class="d-flex justify-content-center mb-4">
            <h3 class="text-white">CorteFácil</h3>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#" data-section="dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="profissionais">
                    <i class="fas fa-users me-2"></i>Profissionais
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="servicos">
                    <i class="fas fa-cut me-2"></i>Serviços
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="agendamentos">
                    <i class="fas fa-calendar-alt me-2"></i>Agendamentos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="configuracoes">
                    <i class="fas fa-cog me-2"></i>Configurações
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-section="perfil">
                    <i class="fas fa-user me-2"></i>Meu Perfil
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="#" id="btnSair">
                    <i class="fas fa-sign-out-alt me-2"></i>Sair
                </a>
            </li>
        </ul>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <button class="btn btn-outline-dark" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="ms-auto">
                <span class="me-3">
                    <i class="fas fa-user me-2"></i>
                    <span id="nomeUsuario">Carregando...</span>
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div id="conteudo"></div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="js/utils.js"></script>
    <script>
        $(document).ready(function() {
            // Verificar autenticação
            $.get('php/verificar_sessao.php')
                .done(function(response) {
                    if (!response.logado || response.tipo !== 'salao') {
                        window.location.href = 'parceiro_login.html';
                        return;
                    }
                    $('#nomeUsuario').text(response.nome);
                    carregarSecao('dashboard');
                })
                .fail(function() {
                    window.location.href = 'parceiro_login.html';
                });

            // Toggle Sidebar
            $('#sidebarToggle').click(function() {
                $('.sidebar').toggleClass('show');
                $('.main-content, .navbar').toggleClass('margin-left-0');
            });

            // Navegação
            $('[data-section]').click(function(e) {
                e.preventDefault();
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                carregarSecao($(this).data('section'));
            });

            // Logout
            $('#btnSair').click(function(e) {
                e.preventDefault();
                $.post('php/logout.php')
                    .done(function(response) {
                        window.location.href = 'parceiro_login.html';
                    })
                    .fail(function() {
                        showError('Erro ao fazer logout');
                    });
            });

            // Função para carregar seções
            function carregarSecao(secao) {
                const arquivos = {
                    dashboard: 'components/dashboard.php',
                    profissionais: 'components/profissionais.php',
                    servicos: 'components/servicos.php',
                    agendamentos: 'components/agenda.php',
                    configuracoes: 'components/configuracoes.php',
                    perfil: 'components/perfil.php'
                };

                if (arquivos[secao]) {
                    $('#conteudo').load(arquivos[secao]);
                }
            }
        });
    </script>
</body>
</html>