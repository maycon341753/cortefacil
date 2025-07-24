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
                    <span id="nomeUsuario" style="cursor: pointer; text-decoration: underline; color: #0d6efd;" 
                          title="Clique para alterar sua senha">Carregando...</span>
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div id="conteudo"></div>
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
                            <div class="input-group">
                                <input type="password" class="form-control" id="senhaAtual" name="senha_atual" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('senhaAtual')">
                                    <i class="fas fa-eye" id="iconSenhaAtual"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="novaSenha" class="form-label">Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="novaSenha" name="nova_senha" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('novaSenha')">
                                    <i class="fas fa-eye" id="iconNovaSenha"></i>
                                </button>
                            </div>
                            <div class="form-text">A senha deve ter pelo menos 6 caracteres.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmarSenha" class="form-label">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmarSenha" name="confirmar_senha" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmarSenha')">
                                    <i class="fas fa-eye" id="iconConfirmarSenha"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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

            // Evento para abrir modal de alterar senha
            $(document).on('click', '#nomeUsuario', function(e) {
                e.preventDefault();
                console.log('Clicou no nome do usuário - abrindo modal');
                $('#modalAlterarSenha').modal('show');
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

            // Função para alterar senha
            $('#formAlterarSenha').submit(function(e) {
                e.preventDefault();
                
                const senhaAtual = $('#senhaAtual').val();
                const novaSenha = $('#novaSenha').val();
                const confirmarSenha = $('#confirmarSenha').val();
                
                // Validações
                if (novaSenha !== confirmarSenha) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'A nova senha e a confirmação não coincidem!'
                    });
                    return;
                }
                
                if (novaSenha.length < 6) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'A nova senha deve ter pelo menos 6 caracteres!'
                    });
                    return;
                }
                
                // Enviar dados para o servidor
                $.ajax({
                    url: 'php/parceiro_alterar_senha.php',
                    method: 'POST',
                    data: {
                        senha_atual: senhaAtual,
                        nova_senha: novaSenha,
                        confirmar_senha: confirmarSenha
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'sucesso') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: 'Senha alterada com sucesso!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#modalAlterarSenha').modal('hide');
                            $('#formAlterarSenha')[0].reset();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.mensagem || 'Erro ao alterar senha'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Erro de comunicação com o servidor'
                        });
                    }
                });
            });
        });

        // Função para mostrar/ocultar senha
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('icon' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1));
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>