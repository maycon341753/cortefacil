// Inicializa os modais
let modalServico;
let modalProfissional;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os modais do Bootstrap
    modalServico = new bootstrap.Modal(document.getElementById('modalServico'));
    modalProfissional = new bootstrap.Modal(document.getElementById('modalProfissional'));

    // Configura os formulários
    setupFormServico();
    setupFormProfissional();
});

// Configuração do formulário de serviço
function setupFormServico() {
    const form = document.getElementById('formServico');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        showLoading();

        try {
            const formData = new FormData(form);
            const response = await fetch('php/salvar_servico.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status === 'sucesso') {
                showSuccess(data.mensagem);
                modalServico.hide();
                loadServicos(); // Recarrega a lista de serviços
            } else {
                showError(data.mensagem);
            }
        } catch (error) {
            console.error('Erro ao salvar serviço:', error);
            showError('Erro ao salvar serviço. Por favor, tente novamente.');
        } finally {
            hideLoading();
        }
    });
}

// Configuração do formulário de profissional
function setupFormProfissional() {
    const form = document.getElementById('formProfissional');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        showLoading();

        try {
            const formData = new FormData(form);
            const response = await fetch('php/salvar_profissional.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status === 'sucesso') {
                showSuccess(data.mensagem);
                modalProfissional.hide();
                loadProfissionais(); // Recarrega a lista de profissionais
            } else {
                showError(data.mensagem);
            }
        } catch (error) {
            console.error('Erro ao salvar profissional:', error);
            showError('Erro ao salvar profissional. Por favor, tente novamente.');
        } finally {
            hideLoading();
        }
    });
}

// Funções para manipulação do modal de serviço
function abrirModalServico(id = null) {
    const form = document.getElementById('formServico');
    form.reset();
    document.getElementById('servicoId').value = '';

    if (id) {
        carregarDadosServico(id);
    }

    modalServico.show();
}

async function carregarDadosServico(id) {
    try {
        showLoading();
        const response = await fetch(`php/get_servico.php?id=${id}`);
        const data = await response.json();

        if (data.status === 'sucesso') {
            document.getElementById('servicoId').value = data.servico.id;
            document.getElementById('nome').value = data.servico.nome;
            document.getElementById('duracao_minutos').value = data.servico.duracao_minutos;
            document.getElementById('preco').value = data.servico.preco;
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao carregar dados do serviço:', error);
        showError('Erro ao carregar dados do serviço. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

async function excluirServico(id) {
    if (!confirm('Tem certeza que deseja excluir este serviço?')) return;

    try {
        showLoading();
        const response = await fetch(`php/excluir_servico.php?id=${id}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.status === 'sucesso') {
            showSuccess(data.mensagem);
            loadServicos(); // Recarrega a lista de serviços
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao excluir serviço:', error);
        showError('Erro ao excluir serviço. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Funções para manipulação do modal de profissional
function abrirModalProfissional(id = null) {
    const form = document.getElementById('formProfissional');
    form.reset();
    document.getElementById('profissionalId').value = '';

    if (id) {
        carregarDadosProfissional(id);
    }

    modalProfissional.show();
}

async function carregarDadosProfissional(id) {
    try {
        showLoading();
        const response = await fetch(`php/get_profissional.php?id=${id}`);
        const data = await response.json();

        if (data.status === 'sucesso') {
            document.getElementById('profissionalId').value = data.profissional.id;
            document.getElementById('nome').value = data.profissional.nome;
            document.getElementById('especialidade').value = data.profissional.especialidade;
            document.getElementById('telefone').value = data.profissional.telefone;
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao carregar dados do profissional:', error);
        showError('Erro ao carregar dados do profissional. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

async function excluirProfissional(id) {
    if (!confirm('Tem certeza que deseja excluir este profissional?')) return;

    try {
        showLoading();
        const response = await fetch(`php/excluir_profissional.php?id=${id}`, {
            method: 'DELETE'
        });
        const data = await response.json();

        if (data.status === 'sucesso') {
            showSuccess(data.mensagem);
            loadProfissionais(); // Recarrega a lista de profissionais
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao excluir profissional:', error);
        showError('Erro ao excluir profissional. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}