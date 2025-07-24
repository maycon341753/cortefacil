<?php
session_start();
require_once '../php/verificar_autenticacao.php';
require_once '../php/conexao.php';

// Verifica se é um usuário do tipo salão
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    header('Location: ../parceiro_login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        }

        /* Cards modernos */
        .modern-card {
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: none;
            background: white;
            overflow: hidden;
            position: relative;
            transition: var(--transition);
        }

        .modern-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
        }

        /* Botões modernos */
        .btn-modern {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.8rem 2rem;
            border-radius: 15px;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            margin: 0 0.2rem;
        }

        .btn-edit {
            background: var(--warning-gradient);
            color: white;
        }

        .btn-delete {
            background: var(--secondary-gradient);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Tabelas modernas */
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
            padding: 1.5rem 1rem;
            background: #f8fafc;
        }

        .table td {
            border: none;
            padding: 1.5rem 1rem;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
        }

        /* Badges modernos */
        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .badge-success {
            background: var(--success-gradient);
        }

        .badge-danger {
            background: var(--secondary-gradient);
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
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
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
            
            .btn-modern {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
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
                        <a class="nav-link active" href="funcionarios.php">
                            <i class="bi bi-people me-1"></i>Funcionários
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="user-info me-3">
                        <i class="bi bi-person-circle me-2"></i>
                        <span style="color: white; font-weight: 600;">
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
            <h1 class="page-title">Funcionários</h1>
            <button class="btn btn-modern" data-bs-toggle="modal" data-bs-target="#modalFuncionarios">
                <i class="bi bi-plus-lg me-2"></i>Novo Funcionário
            </button>
        </div>

        <div class="card modern-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Especialidade</th>
                                <th>Valor do Serviço</th>
                                <th>Horário/Dias</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="listaFuncionarios">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Carregando funcionários...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cadastro/Edição de Funcionários -->
    <div class="modal fade" id="modalFuncionarios" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>Cadastro de Funcionário
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formFuncionario">
                        <input type="hidden" id="idFuncionario">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nomeFuncionario" class="form-label">
                                    <i class="bi bi-person me-1"></i>Nome
                                </label>
                                <input type="text" class="form-control" id="nomeFuncionario" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emailFuncionario" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Email
                                </label>
                                <input type="email" class="form-control" id="emailFuncionario" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="senhaFuncionario" class="form-label">
                                    <i class="bi bi-lock me-1"></i>Senha
                                </label>
                                <input type="password" class="form-control" id="senhaFuncionario">
                                <small class="text-muted">Deixe em branco para manter a senha atual ao editar</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefoneFuncionario" class="form-label">
                                    <i class="bi bi-telephone me-1"></i>Telefone
                                </label>
                                <input type="tel" class="form-control" id="telefoneFuncionario">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="especialidadeFuncionario" class="form-label">
                                    <i class="bi bi-scissors me-1"></i>Especialidade
                                </label>
                                <input type="text" class="form-control" id="especialidadeFuncionario" placeholder="Ex: Corte Masculino, Barba, Coloração">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="valorServicoFuncionario" class="form-label">
                                    <i class="bi bi-currency-dollar me-1"></i>Valor do Serviço (R$)
                                </label>
                                <input type="number" class="form-control" id="valorServicoFuncionario" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="horarioInicio" class="form-label">
                                    <i class="bi bi-clock me-1"></i>Horário Início
                                </label>
                                <input type="time" class="form-control" id="horarioInicio" value="09:00">
                            </div>
                            <div class="col-md-6">
                                <label for="horarioFim" class="form-label">
                                    <i class="bi bi-clock-fill me-1"></i>Horário Fim
                                </label>
                                <input type="time" class="form-control" id="horarioFim" value="18:00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-calendar-week me-1"></i>Dias de Trabalho
                            </label>
                            <div class="btn-group d-flex flex-wrap gap-2" role="group">
                                <input type="checkbox" class="btn-check" id="dia1" value="1" checked>
                                <label class="btn btn-outline-primary" for="dia1">Seg</label>
                                <input type="checkbox" class="btn-check" id="dia2" value="2" checked>
                                <label class="btn btn-outline-primary" for="dia2">Ter</label>
                                <input type="checkbox" class="btn-check" id="dia3" value="3" checked>
                                <label class="btn btn-outline-primary" for="dia3">Qua</label>
                                <input type="checkbox" class="btn-check" id="dia4" value="4" checked>
                                <label class="btn btn-outline-primary" for="dia4">Qui</label>
                                <input type="checkbox" class="btn-check" id="dia5" value="5" checked>
                                <label class="btn btn-outline-primary" for="dia5">Sex</label>
                                <input type="checkbox" class="btn-check" id="dia6" value="6" checked>
                                <label class="btn btn-outline-primary" for="dia6">Sáb</label>
                                <input type="checkbox" class="btn-check" id="dia0" value="0">
                                <label class="btn btn-outline-primary" for="dia0">Dom</label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ativoFuncionario" checked>
                                <label class="form-check-label" for="ativoFuncionario">
                                    <i class="bi bi-toggle-on me-1"></i>Funcionário Ativo
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-modern">
                                <i class="bi bi-check-lg me-1"></i>Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Carrega a lista de funcionários quando a página é carregada
        document.addEventListener('DOMContentLoaded', carregarFuncionarios);
        // Função para formatar horário
        function formatarHorario(horario) {
            return horario ? horario.substring(0, 5) : '';
        }

        // Função para formatar dias de trabalho
        function formatarDiasTrabalho(dias) {
            const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            return dias.split(',').map(d => diasSemana[parseInt(d)]).join(', ');
        }

        // Função para carregar a lista de funcionários
        async function carregarFuncionarios() {
            try {
                const response = await fetch('../php/funcionarios.php');
                const data = await response.json();

                if (data.status === 'sucesso') {
                    const tbody = document.getElementById('listaFuncionarios');
                    tbody.innerHTML = '';
                    
                    data.funcionarios.forEach(funcionario => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${funcionario.nome}</td>
                            <td>${funcionario.email}</td>
                            <td>${funcionario.telefone || '-'}</td>
                            <td>${funcionario.especialidade || '-'}</td>
                            <td>R$ ${parseFloat(funcionario.valor_servico || 0).toFixed(2)}</td>
                            <td>${formatarHorario(funcionario.horario_trabalho_inicio)} - ${formatarHorario(funcionario.horario_trabalho_fim)}<br>
                                <small class="text-muted">${formatarDiasTrabalho(funcionario.dias_trabalho)}</small></td>
                            <td>
                                <span class="badge badge-modern ${funcionario.ativo == 1 ? 'badge-success' : 'badge-danger'}">
                                    ${funcionario.ativo == 1 ? 'Ativo' : 'Inativo'}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-action btn-edit" onclick='editarFuncionario(${JSON.stringify(funcionario)})' title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-action btn-delete" onclick="excluirFuncionario(${funcionario.id})" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar funcionários:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao carregar funcionários. Tente novamente.'
                });
            }
        }

        // Função para editar funcionário
        function editarFuncionario(funcionario) {
            document.getElementById('idFuncionario').value = funcionario.id;
            document.getElementById('nomeFuncionario').value = funcionario.nome;
            document.getElementById('emailFuncionario').value = funcionario.email;
            document.getElementById('telefoneFuncionario').value = funcionario.telefone || '';
            document.getElementById('especialidadeFuncionario').value = funcionario.especialidade || '';
            document.getElementById('valorServicoFuncionario').value = funcionario.valor_servico || '';
            document.getElementById('horarioInicio').value = formatarHorario(funcionario.horario_trabalho_inicio);
            document.getElementById('horarioFim').value = formatarHorario(funcionario.horario_trabalho_fim);
            document.getElementById('ativoFuncionario').checked = funcionario.ativo == 1;

            // Marca os dias de trabalho
            const diasTrabalho = funcionario.dias_trabalho.split(',');
            for (let i = 0; i <= 6; i++) {
                document.getElementById(`dia${i}`).checked = diasTrabalho.includes(i.toString());
            }

            document.getElementById('senhaFuncionario').required = false;
            document.querySelector('#modalFuncionarios .modal-title').textContent = 'Editar Funcionário';
            const modal = new bootstrap.Modal(document.getElementById('modalFuncionarios'));
            modal.show();
        }

        // Função para limpar o formulário
        function limparFormulario() {
            document.getElementById('formFuncionario').reset();
            document.getElementById('idFuncionario').value = '';
            document.getElementById('senhaFuncionario').required = true;
            document.querySelector('#modalFuncionarios .modal-title').textContent = 'Cadastro de Funcionário';
        }

        // Evento para abrir o modal de cadastro
        document.querySelector('[data-bs-target="#modalFuncionarios"]').addEventListener('click', limparFormulario);

        // Função para cadastrar/editar funcionário
        document.getElementById('formFuncionario').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const diasTrabalho = [];
            for (let i = 0; i <= 6; i++) {
                if (document.getElementById(`dia${i}`).checked) {
                    diasTrabalho.push(i);
                }
            }

            const funcionario = {
                id: document.getElementById('idFuncionario').value,
                nome: document.getElementById('nomeFuncionario').value,
                email: document.getElementById('emailFuncionario').value,
                senha: document.getElementById('senhaFuncionario').value,
                telefone: document.getElementById('telefoneFuncionario').value,
                especialidade: document.getElementById('especialidadeFuncionario').value,
                valor_servico: document.getElementById('valorServicoFuncionario').value,
                horario_trabalho_inicio: document.getElementById('horarioInicio').value,
                horario_trabalho_fim: document.getElementById('horarioFim').value,
                dias_trabalho: diasTrabalho.join(','),
                ativo: document.getElementById('ativoFuncionario').checked ? 1 : 0,
                acao: document.getElementById('idFuncionario').value ? 'atualizar' : 'cadastrar'
            };

            try {
                const response = await fetch('../php/funcionarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(funcionario)
                });

                const data = await response.json();

                if (data.status === 'sucesso') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: data.mensagem
                    });
                    document.getElementById('formFuncionario').reset();
                    const modalFuncionarios = bootstrap.Modal.getInstance(document.getElementById('modalFuncionarios'));
                    modalFuncionarios.hide();
                    carregarFuncionarios();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: data.mensagem
                    });
                }
            } catch (error) {
                console.error('Erro ao salvar funcionário:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao salvar funcionário. Tente novamente.'
                });
            }
        });

        // Função para excluir funcionário
        async function excluirFuncionario(id) {
            const result = await Swal.fire({
                title: 'Tem certeza?',
                text: 'Esta ação não poderá ser revertida!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../php/funcionarios.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: id })
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: data.mensagem
                        });
                        carregarFuncionarios();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: data.mensagem
                        });
                    }
                } catch (error) {
                    console.error('Erro ao excluir funcionário:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao excluir funcionário. Tente novamente.'
                    });
                }
            }
        }

        // Carrega os funcionários quando a página carregar
        document.addEventListener('DOMContentLoaded', carregarFuncionarios);
    </script>
</body>
</html>