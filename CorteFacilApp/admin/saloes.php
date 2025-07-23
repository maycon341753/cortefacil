<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Salões - CorteFácil</title>
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
        }

        .form-control:focus, .form-select:focus {
            border-color: #2e86de;
            box-shadow: 0 0 0 0.2rem rgba(46,134,222,0.25);
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        .btn-primary {
            background-color: #2e86de;
            border-color: #2e86de;
        }

        .btn-primary:hover {
            background-color: #1b4f72;
            border-color: #1b4f72;
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
                <a href="saloes.php" class="nav-link active">
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
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h1 class="h3">Gerenciar Salões</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSalao">
                        <i class="fas fa-plus"></i> Novo Salão
                    </button>
                </div>
            </div>

            <!-- Lista de Salões -->
            <div class="card">
                <div class="card-body">
                    <table id="tabelaSaloes" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome Fantasia</th>
                                <th>CNPJ/CPF</th>
                                <th>Cidade</th>
                                <th>WhatsApp</th>
                                <th>Funcionários</th>
                                <th>Média Diária</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Preenchido via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cadastro/Edição de Salão -->
    <div class="modal fade" id="modalSalao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Salão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formSalao">
                        <input type="hidden" id="salaoId">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome Fantasia*</label>
                                <input type="text" class="form-control" id="nomeFantasia" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CNPJ/CPF*</label>
                                <input type="text" class="form-control" id="documento" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cidade*</label>
                                <input type="text" class="form-control" id="cidade" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp*</label>
                                <input type="text" class="form-control" id="whatsapp" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Endereço Completo*</label>
                                <input type="text" class="form-control" id="endereco" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Número de Funcionários*</label>
                                <input type="number" class="form-control" id="numFuncionarios" required min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Média Diária Agendamentos*</label>
                                <input type="number" class="form-control" id="mediaDiaria" required min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Média Semanal Agendamentos*</label>
                                <input type="number" class="form-control" id="mediaSemanal" required min="0">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Chave PIX (opcional)</label>
                                <input type="text" class="form-control" id="pixChave">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Horário de Abertura*</label>
                                <input type="time" class="form-control" id="horarioAbertura" required value="09:00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horário de Fechamento*</label>
                                <input type="time" class="form-control" id="horarioFechamento" required value="18:00">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Intervalo entre Agendamentos (minutos)*</label>
                                <input type="number" class="form-control" id="intervaloAgendamento" required value="30" min="15" step="15">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dias de Funcionamento*</label>
                                <div class="form-control" style="height: auto;">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="dia1" value="1" checked>
                                        <label class="form-check-label">Seg</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="dia2" value="2" checked>
                                        <label class="form-check-label">Ter</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="dia3" value="3" checked>
                                        <label class="form-check-label">Qua</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="dia4" value="4" checked>
                                        <label class="form-check-label">Qui</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="dia5" value="5" checked>
                                        <label class="form-check-label">Sex</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="dia6" value="6" checked>
                                        <label class="form-check-label">Sáb</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="dia0" value="0">
                                        <label class="form-check-label">Dom</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarSalao()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <script>
        // Máscaras para os campos
        $(document).ready(function() {
            $('#documento').mask('000.000.000-00');
            $('#whatsapp').mask('(00) 00000-0000');
            
            // Inicializa DataTable
            $('#tabelaSaloes').DataTable({
                language: {
                    url: '../i18n/pt-BR.json'
                }
            });

            // Carrega os salões
            carregarSaloes();
        });

        // Alterna máscara CNPJ/CPF
        $('#documento').on('keydown', function() {
            try {
                let valor = $(this).val().replace(/\D/g, '');
                if (valor.length > 11) {
                    $(this).mask('00.000.000/0000-00');
                } else {
                    $(this).mask('000.000.000-00');
                }
            } catch(e) {}
        });

        // Carrega lista de salões
        function carregarSaloes() {
            $.ajax({
                url: '../php/admin_listar_saloes.php',
                type: 'GET',
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function(data) {
                    const tabela = $('#tabelaSaloes').DataTable();
                    tabela.clear();

                    data.forEach(salao => {
                        tabela.row.add([
                            salao.nome_fantasia || '-',
                            salao.documento || '-',
                            salao.cidade || '-',
                            salao.whatsapp || '-',
                            salao.num_funcionarios || '0',
                            salao.media_diaria || '0',
                            `<button class="btn btn-sm btn-primary me-1" onclick="editarSalao(${salao.id})">
                                <i class="fas fa-edit"></i>
                             </button>
                             <button class="btn btn-sm btn-danger" onclick="excluirSalao(${salao.id})">
                                <i class="fas fa-trash"></i>
                             </button>`
                        ]);
                    });

                    tabela.draw();
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar salões:', error);
                    alert('Erro ao carregar lista de salões');
                }
            });
        }

        // Salva o salão (novo ou edição)
        function salvarSalao() {
            const formData = {
                id: $('#salaoId').val(),
                nome_fantasia: $('#nomeFantasia').val(),
                documento: $('#documento').val(),
                cidade: $('#cidade').val(),
                endereco: $('#endereco').val(),
                whatsapp: $('#whatsapp').val(),
                num_funcionarios: $('#numFuncionarios').val(),
                media_diaria: $('#mediaDiaria').val(),
                media_semanal: $('#mediaSemanal').val(),
                pix_chave: $('#pixChave').val(),
                horario_abertura: $('#horarioAbertura').val(),
                horario_fechamento: $('#horarioFechamento').val(),
                intervalo_agendamento: $('#intervaloAgendamento').val(),
                dias_funcionamento: [
                    $('#dia0').prop('checked') ? '0' : '',
                    $('#dia1').prop('checked') ? '1' : '',
                    $('#dia2').prop('checked') ? '2' : '',
                    $('#dia3').prop('checked') ? '3' : '',
                    $('#dia4').prop('checked') ? '4' : '',
                    $('#dia5').prop('checked') ? '5' : '',
                    $('#dia6').prop('checked') ? '6' : ''
                ].filter(Boolean)
            };

            const url = formData.id ? '../php/admin_atualizar_salao.php' : '../php/admin_cadastrar_salao.php';
            const isEdicao = !!formData.id;
            
            console.log('Salvando salão:', formData);
            console.log('URL:', url);
            console.log('É edição:', isEdicao);

            $.ajax({
                url: url,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    console.log('Resposta recebida:', response);
                    
                    if (response && response.status === 'ok') {
                        alert('Salão ' + (isEdicao ? 'atualizado' : 'cadastrado') + ' com sucesso!');
                        $('#modalSalao').modal('hide');
                        carregarSaloes();
                        
                        // Se for cadastro e tiver credenciais, mostra para o usuário em um modal mais amigável
                        if (!isEdicao && response.credenciais) {
                            // Cria um modal para exibir as credenciais de forma mais amigável
                            let credenciaisHtml = `
                                <div class="alert alert-success mb-3">
                                    <h5 class="alert-heading">Salão cadastrado com sucesso!</h5>
                                    <p>O salão <strong>${formData.nome_fantasia}</strong> foi cadastrado com sucesso. Anote as credenciais de acesso:</p>
                                </div>
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Credenciais de Acesso</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Email/Login:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="${response.credenciais.email}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto(this.previousElementSibling)"><i class="fas fa-copy"></i></button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Senha:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="${response.credenciais.senha}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto(this.previousElementSibling)"><i class="fas fa-copy"></i></button>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i> Importante: Guarde estas credenciais em um local seguro. Elas não serão exibidas novamente.
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            // Cria o modal dinamicamente
                            let modalCredenciais = document.createElement('div');
                            modalCredenciais.className = 'modal fade';
                            modalCredenciais.id = 'modalCredenciais';
                            modalCredenciais.setAttribute('tabindex', '-1');
                            modalCredenciais.setAttribute('aria-hidden', 'true');
                            modalCredenciais.innerHTML = `
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Credenciais do Salão</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                        </div>
                                        <div class="modal-body">
                                            ${credenciaisHtml}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            // Adiciona o modal ao corpo do documento
                            document.body.appendChild(modalCredenciais);
                            
                            // Função para copiar texto para a área de transferência
                            window.copiarTexto = function(input) {
                                input.select();
                                document.execCommand('copy');
                                
                                // Feedback visual
                                let originalText = input.nextElementSibling.innerHTML;
                                input.nextElementSibling.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                                setTimeout(() => {
                                    input.nextElementSibling.innerHTML = originalText;
                                }, 1500);
                            };
                            
                            // Exibe o modal
                            let modal = new bootstrap.Modal(document.getElementById('modalCredenciais'));
                            modal.show();
                            
                            // Remove o modal do DOM quando for fechado
                            document.getElementById('modalCredenciais').addEventListener('hidden.bs.modal', function() {
                                document.body.removeChild(modalCredenciais);
                            });
                        }
                    } else {
                        console.error('Erro na resposta:', response);
                        alert(response && response.mensagem ? response.mensagem : 'Erro ao salvar salão');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', error);
                    console.error('Status:', status);
                    console.error('Response Text:', xhr.responseText);
                    
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        alert(errorResponse.mensagem || 'Erro ao salvar salão');
                    } catch (e) {
                        alert('Erro ao salvar salão: ' + error);
                    }
                }
            });
        }

        // Limpa o formulário do modal
        function limparFormulario() {
            $('#salaoId').val('');
            $('#nomeFantasia').val('');
            $('#documento').val('');
            $('#cidade').val('');
            $('#endereco').val('');
            $('#whatsapp').val('');
            $('#numFuncionarios').val('1');
            $('#mediaDiaria').val('0');
            $('#mediaSemanal').val('0');
            $('#pixChave').val('');
            $('#horarioAbertura').val('09:00');
            $('#horarioFechamento').val('18:00');
            $('#intervaloAgendamento').val('30');
            $('[id^=dia]').prop('checked', false);
        }

        // Edita um salão
        function editarSalao(id) {
            console.log('Editando salão ID:', id);
            
            // Limpa o formulário primeiro
            limparFormulario();
            
            $.ajax({
                url: '../php/admin_obter_salao.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                xhrFields: {
                    withCredentials: true
                },
                success: function(salao) {
                    console.log('Dados do salão recebidos:', salao);
                    
                    // Verifica se há erro na resposta
                    if (salao.status === 'erro') {
                        alert(salao.mensagem || 'Erro ao carregar dados do salão');
                        return;
                    }
                    
                    // Preenche os campos do formulário
                    $('#salaoId').val(salao.id || '');
                    $('#nomeFantasia').val(salao.nome_fantasia || '');
                    $('#documento').val(salao.documento || '');
                    $('#cidade').val(salao.cidade || '');
                    $('#endereco').val(salao.endereco || '');
                    $('#whatsapp').val(salao.whatsapp || '');
                    $('#numFuncionarios').val(salao.num_funcionarios || '1');
                    $('#mediaDiaria').val(salao.media_diaria || '0');
                    $('#mediaSemanal').val(salao.media_semanal || '0');
                    $('#pixChave').val(salao.pix_chave || '');
                    $('#horarioAbertura').val(salao.horario_abertura || '09:00');
                    $('#horarioFechamento').val(salao.horario_fechamento || '18:00');
                    $('#intervaloAgendamento').val(salao.intervalo_agendamento || '30');
                    
                    // Limpa todos os checkboxes primeiro
                    $('[id^=dia]').prop('checked', false);
                    
                    // Marca os dias de funcionamento
                    if (salao.dias_funcionamento) {
                        let diasArray = [];
                        
                        // Se for string, converte para array
                        if (typeof salao.dias_funcionamento === 'string') {
                            diasArray = salao.dias_funcionamento.split(',');
                        } else if (Array.isArray(salao.dias_funcionamento)) {
                            diasArray = salao.dias_funcionamento;
                        }
                        
                        console.log('Dias de funcionamento:', diasArray);
                        
                        diasArray.forEach(dia => {
                            if (dia !== '') {
                                $('#dia' + dia.trim()).prop('checked', true);
                            }
                        });
                    }
                    
                    // Atualiza o título do modal
                    $('.modal-title').text('Editar Salão');
                    
                    // Exibe o modal
                    $('#modalSalao').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar salão:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    alert('Erro ao carregar dados do salão: ' + error);
                }
            });
        }

        // Exclui um salão
        function excluirSalao(id) {
            if (confirm('Tem certeza que deseja excluir este salão?')) {
                $.ajax({
                    url: '../php/admin_excluir_salao.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: id }),
                    success: function(response) {
                        if (response.status === 'ok') {
                            alert('Salão excluído com sucesso!');
                            carregarSaloes();
                        } else {
                            alert(response.mensagem || 'Erro ao excluir salão');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro:', error);
                        alert('Erro ao excluir salão');
                    }
                });
            }
        }

        // Limpa o formulário quando o modal é fechado
        $('#modalSalao').on('hidden.bs.modal', function () {
            $('#formSalao')[0].reset();
            $('#salaoId').val('');
            
            // Resetar dias de funcionamento
            $('[id^=dia]').prop('checked', false);
        });

        // Evento para o botão "Novo Salão"
        $(document).ready(function() {
            // Quando clicar no botão "Novo Salão"
            $('[data-bs-target="#modalSalao"]').on('click', function() {
                // Limpa o formulário
                limparFormulario();
                
                // Define o título como "Cadastrar Salão"
                $('.modal-title').text('Cadastrar Salão');
            });
            
            // Carrega os salões ao inicializar a página
            carregarSaloes();
        });
    </script>
</body>
</html>
