// Variáveis globais
let modalServico;
let servicoEditando = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    modalServico = new bootstrap.Modal(document.getElementById('modalServico'));
    carregarServicos();
});

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
    }, 5000);
}

// Função para carregar serviços
async function carregarServicos() {
    showLoading();
    try {
        const response = await fetch('../php/parceiro_listar_servicos.php');
        const data = await response.json();
        
        if (data.status === 'sucesso') {
            renderizarServicos(data.data);
        } else {
            showAlert(data.mensagem || 'Erro ao carregar serviços', 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        showAlert('Erro ao carregar serviços. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

// Função para renderizar serviços
function renderizarServicos(servicos) {
    const container = document.getElementById('servicosList');
    
    if (!servicos || servicos.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-scissors" style="font-size: 4rem; color: #64748b;"></i>
                    <h4 class="mt-3 text-muted">Nenhum serviço cadastrado</h4>
                    <p class="text-muted">Comece adicionando seu primeiro serviço!</p>
                    <button class="btn btn-primary-modern btn-modern" data-bs-toggle="modal" data-bs-target="#modalServico">
                        <i class="bi bi-plus-circle me-2"></i>Adicionar Primeiro Serviço
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
    const servicosHtml = servicos.map(servico => `
        <div class="col-lg-4 col-md-6">
            <div class="card service-card h-100">
                <div class="card-body">
                    <div class="service-name">${servico.nome}</div>
                    <div class="service-price">R$ ${parseFloat(servico.preco).toFixed(2)}</div>
                    <div class="service-duration">
                        <i class="bi bi-clock me-1"></i>${servico.duracao_minutos} minutos
                    </div>
                    <div class="service-status ${servico.ativo == 1 ? 'status-ativo' : 'status-inativo'}">
                        ${servico.ativo == 1 ? 'Ativo' : 'Inativo'}
                    </div>
                    ${servico.descricao ? `<div class="text-muted small mb-3">${servico.descricao}</div>` : ''}
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-warning-modern btn-modern btn-sm flex-fill" onclick="editarServico(${servico.id})">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </button>
                        <button class="btn ${servico.ativo == 1 ? 'btn-secondary' : 'btn-success-modern'} btn-modern btn-sm" 
                                onclick="toggleStatusServico(${servico.id}, ${servico.ativo})">
                            <i class="bi bi-${servico.ativo == 1 ? 'eye-slash' : 'eye'} me-1"></i>
                            ${servico.ativo == 1 ? 'Desativar' : 'Ativar'}
                        </button>
                        <button class="btn btn-danger-modern btn-modern btn-sm" onclick="excluirServico(${servico.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = servicosHtml;
}

// Função para abrir modal de novo serviço
function novoServico() {
    servicoEditando = null;
    document.getElementById('modalServicoTitle').textContent = 'Novo Serviço';
    document.getElementById('formServico').reset();
    document.getElementById('servicoId').value = '';
    document.getElementById('ativoServico').checked = true;
    modalServico.show();
}

// Função para editar serviço
async function editarServico(id) {
    try {
        showLoading();
        const response = await fetch(`../php/parceiro_obter_servico.php?id=${id}`);
        const data = await response.json();
        
        if (data.status === 'sucesso') {
            servicoEditando = id;
            document.getElementById('modalServicoTitle').textContent = 'Editar Serviço';
            document.getElementById('servicoId').value = data.data.id;
            document.getElementById('nomeServico').value = data.data.nome;
            document.getElementById('precoServico').value = data.data.preco;
            document.getElementById('duracaoServico').value = data.data.duracao_minutos;
            document.getElementById('descricaoServico').value = data.data.descricao || '';
            document.getElementById('ativoServico').checked = data.data.ativo == 1;
            
            modalServico.show();
        } else {
            showAlert(data.mensagem || 'Erro ao carregar dados do serviço', 'danger');
        }
    } catch (error) {
        console.error('Erro ao carregar serviço:', error);
        showAlert('Erro ao carregar dados do serviço', 'danger');
    } finally {
        hideLoading();
    }
}

// Função para salvar serviço
async function salvarServico() {
    const form = document.getElementById('formServico');
    const formData = new FormData(form);
    
    // Ajustar checkbox
    formData.set('ativo', document.getElementById('ativoServico').checked ? '1' : '0');
    
    try {
        showLoading();
        const response = await fetch('../php/parceiro_salvar_servico.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'sucesso') {
            showAlert(servicoEditando ? 'Serviço atualizado com sucesso!' : 'Serviço criado com sucesso!', 'success');
            modalServico.hide();
            carregarServicos();
        } else {
            showAlert(data.mensagem || 'Erro ao salvar serviço', 'danger');
        }
    } catch (error) {
        console.error('Erro ao salvar serviço:', error);
        showAlert('Erro ao salvar serviço. Tente novamente.', 'danger');
    } finally {
        hideLoading();
    }
}

// Função para alternar status do serviço
async function toggleStatusServico(id, statusAtual) {
    const novoStatus = statusAtual == 1 ? 0 : 1;
    const acao = novoStatus == 1 ? 'ativar' : 'desativar';
    
    const result = await Swal.fire({
        title: `${acao.charAt(0).toUpperCase() + acao.slice(1)} serviço?`,
        text: `Tem certeza que deseja ${acao} este serviço?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Sim, ${acao}!`,
        cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
        try {
            showLoading();
            const formData = new FormData();
            formData.append('id', id);
            formData.append('ativo', novoStatus);
            
            const response = await fetch('../php/parceiro_salvar_servico.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'sucesso') {
                showAlert(`Serviço ${novoStatus == 1 ? 'ativado' : 'desativado'} com sucesso!`, 'success');
                carregarServicos();
            } else {
                showAlert(data.mensagem || 'Erro ao alterar status do serviço', 'danger');
            }
        } catch (error) {
            console.error('Erro ao alterar status:', error);
            showAlert('Erro ao alterar status do serviço', 'danger');
        } finally {
            hideLoading();
        }
    }
}

// Função para excluir serviço
async function excluirServico(id) {
    const result = await Swal.fire({
        title: 'Excluir serviço?',
        text: 'Esta ação não pode ser desfeita!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
        try {
            showLoading();
            const response = await fetch('../php/parceiro_excluir_servico.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            
            if (data.status === 'sucesso') {
                showAlert('Serviço excluído com sucesso!', 'success');
                carregarServicos();
            } else {
                showAlert(data.mensagem || 'Erro ao excluir serviço', 'danger');
            }
        } catch (error) {
            console.error('Erro ao excluir serviço:', error);
            showAlert('Erro ao excluir serviço', 'danger');
        } finally {
            hideLoading();
        }
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('modalServico');
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function () {
            document.getElementById('formServico').reset();
            servicoEditando = null;
        });
    }

    // Formatação de preço
    const precoInput = document.getElementById('precoServico');
    if (precoInput) {
        precoInput.addEventListener('input', function(e) {
            let value = e.target.value;
            if (value < 0) {
                e.target.value = 0;
            }
        });
    }

    // Formatação de duração
    const duracaoInput = document.getElementById('duracaoServico');
    if (duracaoInput) {
        duracaoInput.addEventListener('input', function(e) {
            let value = e.target.value;
            if (value < 1) {
                e.target.value = 1;
            }
        });
    }
});