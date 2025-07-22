// Variáveis globais
let modalServico;
let modalProfissional;

// Funções de utilidade
function showLoading() {
    document.querySelector('.loading').classList.add('active');
}

function hideLoading() {
    document.querySelector('.loading').classList.remove('active');
}

function showAlert(message, type = 'success') {
    const alert = document.querySelector('.alert-float');
    alert.className = `alert alert-float alert-${type}`;
    alert.textContent = message;
    alert.style.display = 'block';
    
    setTimeout(() => {
        alert.style.display = 'none';
    }, 3000);
}

// Arquivo mantido para compatibilidade com versões anteriores
// Todas as funções foram movidas para arquivos específicos:
// - Autenticação: auth.js
// - Dashboard: dashboard.js
// - Serviços: servicos.js
// - Profissionais: profissionais.js
// - Agenda: agenda.js
// - Configurações: configuracoes.js
// - Perfil: perfil.js
// - Utilitários: utils.js
// - Modais: modals.js

// Função para carregar seções de conteúdo
async function loadContent(section) {
    try {
        const response = await fetch(`components/${section}.html`);
        const content = await response.text();
        document.getElementById('content').innerHTML = content;

        // Atualiza a classe ativa no menu
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-section') === section) {
                link.classList.add('active');
            }
        });

        // Inicializa os componentes específicos da seção
        switch (section) {
            case 'dashboard':
                await loadDashboardData();
                break;
            case 'servicos':
                await loadServicos();
                break;
            case 'profissionais':
                await loadProfissionais();
                break;
            case 'agenda':
                await loadAgenda();
                break;
            case 'configuracoes':
                await loadConfiguracoes();
                break;
        }
    } catch (error) {
        console.error(`Erro ao carregar seção ${section}:`, error);
        showError('Erro ao carregar conteúdo. Por favor, tente novamente.');
    }
}

// Funções do Dashboard
function carregarDashboard() {
    $.ajax({
        url: 'php/parceiro_dashboard_stats.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                document.getElementById('agendamentosHoje').textContent = response.data.agendamentos_hoje;
                document.getElementById('totalServicos').textContent = response.data.total_servicos;
                document.getElementById('totalProfissionais').textContent = response.data.total_profissionais;
                document.getElementById('faturamentoMensal').textContent = 
                    `R$ ${response.data.faturamento_mensal.toFixed(2).replace('.', ',')}`;
            }
        }
    });
}

// Funções de Serviços
function carregarServicos() {
    $.ajax({
        url: 'php/parceiro_listar_servicos.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                const tbody = document.getElementById('tabelaServicos');
                tbody.innerHTML = '';
                
                response.data.forEach(servico => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${servico.nome}</td>
                        <td>${servico.duracao} min</td>
                        <td>R$ ${parseFloat(servico.valor).toFixed(2).replace('.', ',')}</td>
                        <td>
                            <button class="btn btn-sm btn-primary me-2" onclick="editarServico(${servico.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="excluirServico(${servico.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }
    });
}

function editarServico(id) {
    $.ajax({
        url: 'php/parceiro_obter_servico.php',
        method: 'POST',
        data: { id },
        success: function(response) {
            if (response.status === 'success') {
                document.getElementById('servicoId').value = response.data.id;
                document.getElementById('nomeServico').value = response.data.nome;
                document.getElementById('descricaoServico').value = response.data.descricao;
                document.getElementById('duracaoServico').value = response.data.duracao;
                document.getElementById('valorServico').value = response.data.valor;
                modalServico.show();
            }
        }
    });
}

function abrirModalServico() {
    document.getElementById('formServico').reset();
    document.getElementById('servicoId').value = '';
    modalServico.show();
}

function salvarServico() {
    const dados = {
        id: document.getElementById('servicoId').value,
        nome: document.getElementById('nomeServico').value,
        descricao: document.getElementById('descricaoServico').value,
        duracao: document.getElementById('duracaoServico').value,
        valor: document.getElementById('valorServico').value
    };

    $.ajax({
        url: 'php/parceiro_salvar_servico.php',
        method: 'POST',
        data: dados,
        success: function(response) {
            if (response.status === 'success') {
                modalServico.hide();
                carregarServicos();
                carregarDashboard();
                alert('Serviço salvo com sucesso!');
            } else {
                alert(response.mensagem);
            }
        }
    });
}

function excluirServico(id) {
    if (confirm('Tem certeza que deseja excluir este serviço?')) {
        $.ajax({
            url: 'php/parceiro_excluir_servico.php',
            method: 'POST',
            data: { id },
            success: function(response) {
                if (response.status === 'success') {
                    carregarServicos();
                    carregarDashboard();
                    alert('Serviço excluído com sucesso!');
                } else {
                    alert(response.mensagem);
                }
            }
        });
    }
}

// Funções de Profissionais
async function carregarProfissionais() {
    showLoading();
    try {
        const response = await fetch('php/parceiro_listar_profissionais.php');
        const data = await response.json();
        
        const tbody = document.querySelector('#tabelaProfissionais');
        const emptyState = document.querySelector('#profissionaisEmpty');
        
        if (data.status === 'success') {
            if (data.data && data.data.length > 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'none';
                
                data.data.forEach(profissional => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${profissional.nome}</td>
                            <td>${profissional.especialidade || '-'}</td>
                            <td>${profissional.telefone || '-'}</td>
                            <td>
                                <button onclick="editarProfissional(${profissional.id})" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="excluirProfissional(${profissional.id})" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '';
                emptyState.style.display = 'block';
            }
        } else {
            showAlert('Erro ao carregar profissionais: ' + data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar profissionais:', error);
        showAlert('Erro ao carregar profissionais. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

function editarProfissional(id) {
    $.ajax({
        url: 'php/parceiro_obter_profissional.php',
        method: 'GET',  // Alterado para GET conforme o PHP espera
        data: { id },
        success: function(response) {
            if (response.status === 'success') {
                // Usando os IDs corretos do formulário HTML
                document.getElementById('profissionalId').value = response.profissional.id;
                document.querySelector('#formProfissional #nome').value = response.profissional.nome;
                document.querySelector('#formProfissional #especialidade').value = response.profissional.especialidade || '';
                document.querySelector('#formProfissional #telefone').value = response.profissional.telefone || '';
                modalProfissional.show();
            } else {
                showAlert('Erro ao carregar profissional: ' + response.mensagem, 'danger');
            }
        },
        error: function() {
            showAlert('Erro ao carregar profissional. Tente novamente.', 'danger');
        }
    });
}

function abrirModalProfissional() {
    document.getElementById('formProfissional').reset();
    document.getElementById('profissionalId').value = '';
    modalProfissional.show();
}

function salvarProfissional() {
    const dados = {
        id: document.getElementById('profissionalId').value,
        nome: document.querySelector('#formProfissional #nome').value,
        especialidade: document.querySelector('#formProfissional #especialidade').value,
        telefone: document.querySelector('#formProfissional #telefone').value
    };

    $.ajax({
        url: 'php/parceiro_salvar_profissional.php',
        method: 'POST',
        data: dados,
        success: function(response) {
            if (response.status === 'success') {
                modalProfissional.hide();
                carregarProfissionais();
                carregarDashboard();
                alert('Profissional salvo com sucesso!');
            } else {
                alert(response.mensagem);
            }
        }
    });
}

function excluirProfissional(id) {
    if (confirm('Tem certeza que deseja excluir este profissional?')) {
        $.ajax({
            url: 'php/parceiro_excluir_profissional.php',
            method: 'POST',
            data: { id },
            success: function(response) {
                if (response.status === 'success') {
                    carregarProfissionais();
                    carregarDashboard();
                    alert('Profissional excluído com sucesso!');
                } else {
                    alert(response.mensagem);
                }
            }
        });
    }
}

// Funções de Configurações
function carregarConfiguracoes() {
    $.ajax({
        url: 'php/parceiro_obter_configuracoes.php',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                document.getElementById('nomeFantasia').value = response.data.nome_fantasia;
                document.getElementById('documento').value = response.data.documento;
                document.getElementById('horarioAbertura').value = response.data.horario_abertura;
                document.getElementById('horarioFechamento').value = response.data.horario_fechamento;
                
                // Marca os dias de funcionamento
                const dias = response.data.dias_funcionamento.split(',');
                dias.forEach(dia => {
                    document.querySelector(`input[value="${dia}"]`).checked = true;
                });
            }
        }
    });
}

function salvarConfiguracoes() {
    const diasSelecionados = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
        .map(checkbox => checkbox.value)
        .join(',');

    const dados = {
        horario_abertura: document.getElementById('horarioAbertura').value,
        horario_fechamento: document.getElementById('horarioFechamento').value,
        dias_funcionamento: diasSelecionados
    };

    $.ajax({
        url: 'php/parceiro_salvar_configuracoes.php',
        method: 'POST',
        data: dados,
        success: function(response) {
            if (response.status === 'sucesso') {
                alert('Configurações salvas com sucesso!');
            } else {
                alert(response.mensagem);
            }
        }
    });
} 

// Funções para gerenciar serviços
async function salvarServico(event) {
    event.preventDefault();
    showLoading();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('php/parceiro_salvar_servico.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert(data.mensagem);
            form.reset();
            await carregarServicos();
            // Fecha o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalServico'));
            if (modal) modal.hide();
        } else {
            showAlert(data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar serviço:', error);
        showAlert('Erro ao salvar serviço. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

async function carregarServicos() {
    showLoading();
    try {
        const response = await fetch('php/parceiro_listar_servicos.php');
        const data = await response.json();
        
        const tbody = document.querySelector('#tabelaServicos');
        const emptyState = document.querySelector('#servicosEmpty');
        
        if (data.status === 'success') {
            if (data.servicos && data.servicos.length > 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'none';
                
                data.servicos.forEach(servico => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${servico.nome}</td>
                            <td>${servico.duracao_minutos} min</td>
                            <td>R$ ${parseFloat(servico.preco).toFixed(2)}</td>
                            <td>
                                <button onclick="editarServico(${servico.id})" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="excluirServico(${servico.id})" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '';
                emptyState.style.display = 'block';
            }
        } else {
            showAlert('Erro ao carregar serviços: ' + data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        showAlert('Erro ao carregar serviços. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

async function editarServico(id) {
    showLoading();
    try {
        const response = await fetch(`php/parceiro_obter_servico.php?id=${id}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const form = document.getElementById('formServico');
            form.id.value = data.servico.id;
            form.nome.value = data.servico.nome;
            form.duracao_minutos.value = data.servico.duracao_minutos;
            form.preco.value = data.servico.preco;
            
            // Abre o modal
            const modal = new bootstrap.Modal(document.getElementById('modalServico'));
            modal.show();
        } else {
            showAlert('Erro ao carregar serviço: ' + data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar serviço:', error);
        showAlert('Erro ao carregar serviço. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

async function excluirServico(id) {
    if (!confirm('Tem certeza que deseja excluir este serviço?')) {
        return;
    }
    
    showLoading();
    try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('php/parceiro_excluir_servico.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert(data.mensagem);
            await carregarServicos();
        } else {
            showAlert('Erro ao excluir serviço: ' + data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao excluir serviço:', error);
        showAlert('Erro ao excluir serviço. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

function abrirModalServico() {
    const form = document.getElementById('formServico');
    form.reset();
    form.id.value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalServico'));
    modal.show();
}

// Funções para gerenciar profissionais
async function salvarProfissional(event) {
    event.preventDefault();
    showLoading();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('php/parceiro_salvar_profissional.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert(data.mensagem);
            form.reset();
            await carregarProfissionais();
            // Fecha o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalProfissional'));
            if (modal) modal.hide();
        } else {
            showAlert(data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar profissional:', error);
        showAlert('Erro ao salvar profissional. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

async function editarProfissional(id) {
    showLoading();
    try {
        const response = await fetch(`php/parceiro_obter_profissional.php?id=${id}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const form = document.getElementById('formProfissional');
            form.id.value = data.profissional.id;
            form.nome.value = data.profissional.nome;
            form.especialidade.value = data.profissional.especialidade || '';
            form.telefone.value = data.profissional.telefone || '';
            
            // Abre o modal
            const modal = new bootstrap.Modal(document.getElementById('modalProfissional'));
            modal.show();
        } else {
            showAlert('Erro ao carregar profissional: ' + data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar profissional:', error);
        showAlert('Erro ao carregar profissional. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

async function excluirProfissional(id) {
    if (!confirm('Tem certeza que deseja excluir este profissional?')) {
        return;
    }
    
    showLoading();
    try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('php/parceiro_excluir_profissional.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert(data.mensagem);
            await carregarProfissionais();
        } else {
            showAlert('Erro ao excluir profissional: ' + data.mensagem, 'danger');
        }
    } catch (error) {
        console.error('Erro ao excluir profissional:', error);
        showAlert('Erro ao excluir profissional. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

function abrirModalProfissional() {
    const form = document.getElementById('formProfissional');
    form.reset();
    form.id.value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalProfissional'));
    modal.show();
}

// Funções de inicialização
function initServicos() {
    carregarServicos();
    
    // Adiciona o handler de submit ao formulário
    const form = document.getElementById('formServico');
    if (form) {
        form.addEventListener('submit', salvarServico);
    }
    
    // Limpa o formulário quando o modal é fechado
    const modal = document.getElementById('modalServico');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', () => {
            document.getElementById('formServico').reset();
            document.getElementById('servicoId').value = '';
        });
    }
}

function initProfissionais() {
    carregarProfissionais();
    
    // Adiciona o handler de submit ao formulário
    const form = document.getElementById('formProfissional');
    if (form) {
        form.addEventListener('submit', salvarProfissional);
    }
    
    // Limpa o formulário quando o modal é fechado
    const modal = document.getElementById('modalProfissional');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', () => {
            document.getElementById('formProfissional').reset();
            document.getElementById('profissionalId').value = '';
        });
    }
}

// Inicialização quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', async function() {
    try {
        // Verifica autenticação
        await verificarAutenticacao();

        // Configura a navegação
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', function() {
                const section = this.getAttribute('data-section');
                loadContent(section);
            });
        });

        // Configura o toggle do sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
    } catch (error) {
        console.error('Erro na inicialização:', error);
        showError('Erro ao inicializar a aplicação. Por favor, recarregue a página.');
    }
});