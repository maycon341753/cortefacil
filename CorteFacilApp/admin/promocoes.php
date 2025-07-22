<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cupons - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        .btn-gerar-cupom {
            background-color: #5e72e4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .btn-gerar-cupom:hover {
            background-color: #324cdd;
            color: white;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
        .status-disponivel {
            background-color: #2dce89;
            color: white;
        }
        .status-utilizado {
            background-color: #11cdef;
            color: white;
        }
        .status-expirado {
            background-color: #fb6340;
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
                <a href="painel.html" class="nav-link">
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
                <a href="promocoes.php" class="nav-link active">
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
        <!-- Botão toggle menu responsivo -->
        <button class="btn btn-primary d-md-none mb-3" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-3">Gerenciamento de Cupons</h2>
                <button class="btn btn-gerar-cupom mb-4" data-bs-toggle="modal" data-bs-target="#modalGerarCupom">
                    <i class="fas fa-plus-circle me-2"></i>Gerar Novo Cupom
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="tabelaCupons" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Salão</th>
                            <th>Data Geração</th>
                            <th>Data Expiração</th>
                            <th>Status</th>
                            <th>Valor Ressarcimento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dados serão carregados via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Gerar Cupom -->
    <div class="modal fade" id="modalGerarCupom" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Gerar Novo Cupom</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formGerarCupom">
                        <div class="mb-3">
                            <label class="form-label">Salão</label>
                            <select class="form-select" name="salao_id" required>
                                <option value="">Selecione um salão</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data de Expiração</label>
                            <input type="date" class="form-control" name="data_expiracao" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valor de Ressarcimento (R$)</label>
                            <input type="number" class="form-control" name="valor_ressarcimento" step="0.01" min="0" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-ticket-alt me-2"></i>Gerar Cupom
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializa DataTable
            const table = $('#tabelaCupons').DataTable({
                language: {
                    url: '../i18n/pt-BR.json'
                },
                ajax: {
                    url: '../php/admin_listar_cupons.php',
                    dataSrc: ''
                },
                columns: [
                    { data: 'codigo' },
                    { data: 'nome_salao' },
                    { 
                        data: 'data_geracao',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('pt-BR');
                        }
                    },
                    { 
                        data: 'data_expiracao',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('pt-BR');
                        }
                    },
                    { 
                        data: 'status',
                        render: function(data) {
                            const statusClasses = {
                                'disponivel': 'status-disponivel',
                                'utilizado': 'status-utilizado',
                                'expirado': 'status-expirado'
                            };
                            return `<span class="status-badge ${statusClasses[data]}">${data}</span>`;
                        }
                    },
                    { 
                        data: 'valor_ressarcimento',
                        render: function(data) {
                            return `R$ ${parseFloat(data).toFixed(2)}`;
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-info" onclick="visualizarCupom('${data.codigo}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            `;
                        }
                    }
                ]
            });

            // Carrega lista de salões
            console.log('Iniciando carregamento de salões...');
            $.ajax({
                url: '../php/admin_listar_saloes.php',
                method: 'GET',
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function(xhr) {
                    console.log('Enviando requisição para:', this.url);
                },
                success: function(response) {
                    console.log('Resposta do servidor:', response);
                    const select = $('select[name="salao_id"]');
                    if (response && response.data && Array.isArray(response.data)) {
                        response.data.forEach(salao => {
                            select.append(`<option value="${salao.id}">${salao.nome_fantasia}</option>`);
                        });
                        console.log('Salões carregados com sucesso');
                    } else {
                        console.error('Resposta inválida do servidor');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Erro ao carregar salões:', {
                        status: jqXHR.status,
                        statusText: jqXHR.statusText,
                        responseText: jqXHR.responseText,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                }
            });

            // Manipula o envio do formulário de geração de cupom
            $('#formGerarCupom').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: '../php/admin_gerar_cupom.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'sucesso') {

        // Funções do menu lateral e perfil
        function logout() {
            if (confirm('Deseja realmente sair?')) {
                fetch('../php/admin_logout.php', {
                    method: 'POST',
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'sucesso') {
                        window.location.href = '../admin_login.html';
                    }
                })
                .catch(error => console.error('Erro ao fazer logout:', error));
            }
        }

        function abrirModalPerfil() {
            // Implementar função de abrir modal de perfil
            alert('Funcionalidade em desenvolvimento');
        }

        function abrirModalSenha() {
            // Implementar função de abrir modal de senha
            alert('Funcionalidade em desenvolvimento');
        }

        // Controle do menu responsivo
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('active');
        }

        // Carregar nome do administrador
        fetch('../php/admin_obter_perfil.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    document.getElementById('adminNome').textContent = data.nome;
                }
            })
            .catch(error => console.error('Erro ao carregar perfil:', error));

                            alert('Cupom gerado com sucesso!');
                            $('#modalGerarCupom').modal('hide');
                            table.ajax.reload();
                        } else {
                            alert('Erro ao gerar cupom: ' + response.mensagem);
                        }
                    },
                    error: function() {
                        alert('Erro ao comunicar com o servidor');
                    }
                });
            });
        });

        function visualizarCupom(codigo) {
            window.open(`../php/visualizar_cupom.php?codigo=${codigo}`, '_blank');
        }
    </script>
</body>
</html>