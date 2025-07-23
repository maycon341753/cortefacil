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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="metas.php">Metas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="funcionarios.php">Funcionários</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                        Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>
                    </span>
                    <a href="../php/parceiro_login.php?logout=true" class="btn btn-outline-light">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Funcionários</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFuncionarios">
                <i class="bi bi-plus-lg"></i> Novo Funcionário
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
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
                            <!-- Será preenchido via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cadastro/Edição de Funcionários -->
    <div class="modal fade" id="modalFuncionarios" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastro de Funcionário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formFuncionario">
                        <input type="hidden" id="idFuncionario">
                        <div class="mb-3">
                            <label for="nomeFuncionario" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nomeFuncionario" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailFuncionario" class="form-label">Email</label>
                            <input type="email" class="form-control" id="emailFuncionario" required>
                        </div>
                        <div class="mb-3">
                            <label for="senhaFuncionario" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senhaFuncionario">
                            <small class="text-muted">Deixe em branco para manter a senha atual ao editar</small>
                        </div>
                        <div class="mb-3">
                            <label for="telefoneFuncionario" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefoneFuncionario">
                        </div>
                        <div class="mb-3">
                            <label for="especialidadeFuncionario" class="form-label">Especialidade</label>
                            <input type="text" class="form-control" id="especialidadeFuncionario" placeholder="Ex: Corte Masculino, Barba, Coloração">
                        </div>
                        <div class="mb-3">
                            <label for="valorServicoFuncionario" class="form-label">Valor do Serviço (R$)</label>
                            <input type="number" class="form-control" id="valorServicoFuncionario" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="horarioInicio" class="form-label">Horário Início</label>
                                <input type="time" class="form-control" id="horarioInicio" value="09:00">
                            </div>
                            <div class="col">
                                <label for="horarioFim" class="form-label">Horário Fim</label>
                                <input type="time" class="form-control" id="horarioFim" value="18:00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dias de Trabalho</label>
                            <div class="btn-group" role="group">
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
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ativoFuncionario" checked>
                                <label class="form-check-label" for="ativoFuncionario">Ativo</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar</button>
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
                                <span class="badge ${funcionario.ativo == 1 ? 'bg-success' : 'bg-danger'}">
                                    ${funcionario.ativo == 1 ? 'Ativo' : 'Inativo'}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary me-2" onclick='editarFuncionario(${JSON.stringify(funcionario)})'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="excluirFuncionario(${funcionario.id})">
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