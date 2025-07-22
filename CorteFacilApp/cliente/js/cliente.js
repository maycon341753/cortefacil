// Variáveis globais para armazenar o estado do agendamento
var selectedSalao = null;
var selectedServico = null;
var selectedProfissional = null;
var selectedData = null;
var selectedHorario = null;

// Elementos DOM frequentemente utilizados
var loadingModal;
var successModal;
var errorModal;

// Inicializar modais quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar modais
    loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    successModal = new bootstrap.Modal(document.getElementById('successModal'));
    errorModal = new bootstrap.Modal(document.getElementById('errorModal'));

    // Carregar salões ao iniciar
    loadSaloes();
    showSection('saloesSection');

    // Logout
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = '../php/logout.php';
        });
    }
});

// Função para mostrar mensagem de erro
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    errorModal.show();
}

// Função para mostrar mensagem de sucesso
function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    successModal.show();
}

// Função para mostrar loading
function showLoading() {
    if (loadingModal) {
        loadingModal.show();
    }
}

// Função para esconder loading
function hideLoading() {
    if (loadingModal) {
        loadingModal.hide();
    }
}

// Função para mostrar/esconder seções
function showSection(sectionId) {
    // Esconder todas as seções
    document.querySelectorAll('.container > div').forEach(div => {
        if (div && div.style) {
            div.style.display = 'none';
        }
    });
    
    // Mostrar a seção solicitada
    const targetSection = document.getElementById(sectionId);
    if (targetSection && targetSection.style) {
        targetSection.style.display = 'block';
    } else {
        console.error(`Seção não encontrada: ${sectionId}`);
    }
}

// Função para carregar salões disponíveis
async function loadSaloes() {
    try {
        console.log('Iniciando carregamento de salões...');
        showLoading();
        const response = await fetch('../php/listar_saloes.php');
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Dados recebidos:', data);

        if (data.status !== 'success') {
            throw new Error(data.message || 'Erro ao carregar salões');
        }

        const saloesContainer = document.getElementById('saloesList');
        console.log('Container encontrado:', saloesContainer);
        console.log('Número de salões:', data.saloes.length);
        
        if (data.saloes.length === 0) {
            saloesContainer.innerHTML = '<div class="col-12"><p class="text-center">Nenhum salão disponível no momento.</p></div>';
            hideLoading();
            return;
        }
        saloesContainer.innerHTML = data.saloes.map(salao => {
            // Gerar estrelas de avaliação
            const avaliacao = parseFloat(salao.avaliacao) || 0;
            const estrelasHtml = gerarEstrelas(avaliacao);
            
            // Formatar endereço
            const endereco = formatarEndereco(salao);
            
            // Imagem padrão se não houver
            const imagemUrl = salao.imagem_url || 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=60';
            
            return `
                <div class="col-md-4 col-sm-6 mb-4 salao-item">
                    <div class="salao-card">
                        <div class="salao-img" style="background-image: url('${imagemUrl}');"></div>
                        <div class="salao-info">
                            <h5 class="salao-nome">${salao.nome}</h5>
                            <div class="salao-avaliacao mb-2">
                                ${estrelasHtml} <span class="text-muted small ms-1">(${avaliacao.toFixed(1)})</span>
                            </div>
                            <p class="salao-endereco">
                                <i class="bi bi-geo-alt"></i> ${endereco}
                            </p>
                            <p class="salao-profissionais">
                                <i class="bi bi-clock"></i> ${salao.horario_abertura} - ${salao.horario_fechamento}
                            </p>
                            <p class="salao-profissionais">
                                <i class="bi bi-people"></i> ${salao.profissionais.length} profissionais disponíveis
                            </p>
                            <button class="btn btn-agendar w-100" onclick="selectSalao(${salao.id})">
                                Agendar Agora
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Inicializar pesquisa
        document.getElementById('searchSalao').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterSaloes(searchTerm);
        });
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

// Função para filtrar salões por termo de pesquisa
function filterSaloes(searchTerm) {
    const saloes = document.querySelectorAll('.salao-item');
    
    saloes.forEach(salao => {
        const salaoNome = salao.querySelector('.salao-nome').textContent.toLowerCase();
        const salaoEndereco = salao.querySelector('.salao-endereco').textContent.toLowerCase();
        
        if (salaoNome.includes(searchTerm) || salaoEndereco.includes(searchTerm)) {
            salao.style.display = 'block';
        } else {
            salao.style.display = 'none';
        }
    });
}

// Função para gerar estrelas de avaliação
function gerarEstrelas(avaliacao) {
    let estrelasHtml = '';
    const estrelaCheia = '<i class="bi bi-star-fill"></i>';
    const estrelaMeia = '<i class="bi bi-star-half"></i>';
    const estrelaVazia = '<i class="bi bi-star"></i>';
    
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(avaliacao)) {
            estrelasHtml += estrelaCheia;
        } else if (i - 0.5 <= avaliacao) {
            estrelasHtml += estrelaMeia;
        } else {
            estrelasHtml += estrelaVazia;
        }
    }
    
    return estrelasHtml;
}

// Função para formatar endereço
function formatarEndereco(salao) {
    const partes = [];
    if (salao.endereco) partes.push(salao.endereco);
    if (salao.cidade) partes.push(salao.cidade);
    
    return partes.join(', ') || 'Endereço não disponível';
}

// Função para selecionar salão
async function selectSalao(salaoId) {
    try {
        if (loadingModal) {
            loadingModal.show();
        } else {
            console.error('Loading modal não inicializado');
        }
        selectedSalao = salaoId;
        
        // Limpar seleções anteriores
        selectedServico = null;
        selectedProfissional = null;
        selectedData = null;
        selectedHorario = null;
        
        // Esconder todas as seções e mostrar apenas a de serviços
        hideAllSections();
        const servicosSection1 = document.getElementById('servicosSection');
        if (servicosSection1 && servicosSection1.style) {
            servicosSection1.style.display = 'block';
        } else {
            console.error('Seção de serviços não encontrada');
        }
        
        // Adicionar botão voltar
        const servicosSection = document.getElementById('servicosSection');
        const backButton = document.createElement('button');
        backButton.className = 'btn btn-outline-secondary mb-3';
        backButton.innerHTML = '<i class="bi bi-arrow-left"></i> Voltar para Salões';
        backButton.onclick = voltarParaSaloes;
        
        // Remover botão anterior se existir
        const existingButton = servicosSection.querySelector('.btn-outline-secondary');
        if (existingButton) {
            servicosSection.removeChild(existingButton);
        }
        
        servicosSection.insertBefore(backButton, servicosSection.firstChild);
        
        await loadServicos(salaoId);
        if (loadingModal) {
            loadingModal.hide();
        }
    } catch (error) {
        if (loadingModal) {
            loadingModal.hide();
        }
        showError(error.message);
    }
}

// Função para voltar para a lista de salões
function voltarParaSaloes() {
    hideAllSections();
    
    // Mostrar as seções necessárias, verificando se existem
    const sectionsToShow = ['saloesSection', 'bannerSection', 'footerSection'];
    sectionsToShow.forEach(sectionId => {
        const section = document.getElementById(sectionId);
        if (section && section.style) {
            section.style.display = 'block';
        }
    });
}

// Função para esconder todas as seções
function hideAllSections() {
    // Lista de todas as seções que precisam ser escondidas
    const sections = [
        'saloesSection', 'servicosSection', 'profissionaisSection',
        'dataSection', 'horarioSection', 'resumoSection',
        'meusAgendamentosSection', 'bannerSection', 'footerSection'
    ];
    
    // Esconder cada seção se ela existir
    sections.forEach(sectionId => {
        const section = document.getElementById(sectionId);
        if (section && section.style) {
            section.style.display = 'none';
        }
    });
}

// Função para carregar serviços do salão
async function loadServicos(salaoId) {
    try {
        const response = await fetch(`../php/listar_servicos.php?salao_id=${salaoId}`);
        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Erro ao carregar serviços');
        }

        const servicosContainer = document.getElementById('servicosList');
        servicosContainer.innerHTML = data.data.map(servico => `
            <div class="col-md-4 mb-3">
                <div class="card service-card h-100" onclick="selectServico(${servico.id})">
                    <div class="card-body">
                        <h5 class="card-title">${servico.nome}</h5>
                        <p class="card-text">${servico.preco_formatado}</p>
                        <p class="card-text">${servico.duracao_minutos} minutos</p>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        showError(error.message);
    }
}

// Função para selecionar serviço
async function selectServico(servicoId) {
    selectedServico = servicoId;
    
    const profissionaisSection = document.getElementById('profissionaisSection');
    if (profissionaisSection && profissionaisSection.style) {
        profissionaisSection.style.display = 'block';
    } else {
        console.error('Seção de profissionais não encontrada');
    }
    
    await loadProfissionais(selectedSalao, servicoId);
}

// Função para carregar profissionais
async function loadProfissionais(salaoId, servicoId) {
    try {
        const response = await fetch(`../php/listar_profissionais.php?salao_id=${salaoId}`);
        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.mensagem || 'Erro ao carregar profissionais');
        }

        const profissionaisContainer = document.getElementById('profissionaisList');
        profissionaisContainer.innerHTML = data.profissionais.map(profissional => `
            <div class="col-md-4 mb-3">
                <div class="card service-card" onclick="selectProfissional(${profissional.id})">
                    <div class="card-body text-center">
                        <img src="${profissional.foto || '../assets/default-avatar.png'}" class="professional-avatar mb-3">
                        <h5 class="card-title">${profissional.nome}</h5>
                        <p class="card-text text-muted">${profissional.especialidade || ''}</p>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        showError(error.message);
    }
}

// Função para selecionar profissional
function selectProfissional(profissionalId) {
    selectedProfissional = profissionalId;
    
    const dataSection = document.getElementById('dataSection');
    if (dataSection && dataSection.style) {
        dataSection.style.display = 'block';
    } else {
        console.error('Seção de data não encontrada');
    }
    
    initializeDatePicker();
}

// Função para inicializar o calendário
function initializeDatePicker() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Elemento do calendário não encontrado');
        return;
    }
    
    // Limpar qualquer conteúdo existente
    calendarEl.innerHTML = '';
    
    const hoje = new Date();
    const trintaDiasDepois = new Date();
    trintaDiasDepois.setDate(hoje.getDate() + 30);

    try {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            height: 'auto',
            initialView: 'dayGridMonth',
            locale: 'pt-br',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            buttonText: {
                today: 'Hoje'
            },
            validRange: {
                start: hoje,
                end: trintaDiasDepois
            },
            selectable: true,
            selectMirror: true,
            unselectAuto: false,
            dateClick: async function(info) {
                // Remover seleção anterior
                document.querySelectorAll('.fc-day-selected').forEach(el => {
                    el.classList.remove('fc-day-selected');
                });
                
                // Adicionar classe à data selecionada
                info.dayEl.classList.add('fc-day-selected');
                
                // Atualizar data selecionada
                selectedData = info.date;
                await loadHorarios();
            },
            datesSet: function(dateInfo) {
                // Verificar se a data selecionada ainda está visível
                if (selectedData && (selectedData < dateInfo.start || selectedData > dateInfo.end)) {
                    selectedData = null;
                }
            }
        });

        calendar.render();
        console.log('Calendário inicializado com sucesso');

        // Adicionar estilos personalizados para a data selecionada
        const style = document.createElement('style');
        style.textContent = `
            .fc-day-selected {
                background-color: rgba(25, 135, 84, 0.2) !important;
                border: 2px solid #198754 !important;
            }
            .fc-daygrid-day-frame {
                min-height: 30px !important;
            }
            .fc-daygrid-day-top {
                padding: 2px !important;
            }
            .fc-col-header-cell {
                padding: 2px !important;
            }
            .fc .fc-toolbar {
                margin-bottom: 0.5rem !important;
            }
            .fc .fc-toolbar-title {
                font-size: 1.2em !important;
            }
            .fc-header-toolbar {
                margin-bottom: 0.5em !important;
            }
            .fc-button {
                padding: 0.2em 0.65em !important;
                font-size: 0.9em !important;
            }
        `;
        document.head.appendChild(style);
    } catch (error) {
        console.error('Erro ao inicializar o calendário:', error);
    }
}

// Função para carregar horários disponíveis
async function loadHorarios() {
    try {
        const formattedDate = selectedData.toISOString().split('T')[0];
        const response = await fetch(`../php/listar_horarios_disponiveis.php?salao_id=${selectedSalao}&profissional_id=${selectedProfissional}&data=${formattedDate}`);
        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Erro ao carregar horários');
        }

        document.getElementById('horarioSection').style.display = 'block';
        const horariosContainer = document.getElementById('horariosList');
        horariosContainer.innerHTML = data.data.map(horario => `
            <div class="col-md-2 mb-2">
                <div class="card time-slot" onclick="selectHorario('${horario}')">
                    <div class="card-body text-center">
                        <h5 class="card-title">${horario}</h5>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        showError(error.message);
    }
}

// Função para selecionar horário
function selectHorario(horario) {
    selectedHorario = horario;
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
    const resumoSection = document.getElementById('resumoSection');
    if (resumoSection && resumoSection.style) {
        resumoSection.style.display = 'block';
    } else {
        console.error('Seção de resumo não encontrada');
    }
    
    updateResumo();
}

// Função para atualizar o resumo do agendamento
function updateResumo() {
    const formattedDate = selectedData.toLocaleDateString('pt-BR');
    document.getElementById('resumoAgendamento').innerHTML = `
        <h4>Resumo do Agendamento</h4>
        <p><strong>Data:</strong> ${formattedDate}</p>
        <p><strong>Horário:</strong> ${selectedHorario}</p>
        <button class="btn btn-primary w-100" onclick="confirmarAgendamento()">
            Confirmar Agendamento
        </button>
    `;
}

// Função para mostrar confirmação
function showConfirmacao() {
    document.getElementById('confirmacaoSection').style.display = 'block';
    const resumo = document.getElementById('resumoAgendamento');
    const data = selectedData.toLocaleDateString('pt-BR');
    resumo.innerHTML = `
        <h5>Resumo do Agendamento</h5>
        <p><strong>Data:</strong> ${data}</p>
        <p><strong>Horário:</strong> ${selectedHorario}</p>
    `;
}

// Função para confirmar agendamento
async function confirmarAgendamento() {
    try {
        showLoading();
        const formattedDate = selectedData.toISOString().split('T')[0];
        const response = await fetch('../php/agendar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                salao_id: selectedSalao,
                servico_id: selectedServico,
                profissional_id: selectedProfissional,
                data: formattedDate,
                hora: selectedHorario
            })
        });

        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Erro ao criar agendamento');
        }

        // Criar pagamento PIX
        const pixResponse = await fetch('../php/criar_pagamento_pix.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                agendamento_id: data.data.agendamento_id
            })
        });

        const pixData = await pixResponse.json();

        if (!pixData.success) {
            throw new Error(pixData.error || 'Erro ao gerar QR Code PIX');
        }

        // Mostrar QR Code e aguardar pagamento
        const modalContent = `
            <div class="text-center">
                <h5>Pagamento via PIX</h5>
                <p>Valor: R$ ${pixData.valor.toFixed(2)}</p>
                <img src="data:image/png;base64,${pixData.qr_code_base64}" alt="QR Code PIX" style="max-width: 200px;">
                <p class="mt-2">Escaneie o QR Code acima com seu aplicativo de pagamento</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="${pixData.pix_copia_cola}" id="pixCode" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="copyPixCode()">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
                <div id="paymentStatus" class="alert alert-info mt-3">
                    Aguardando pagamento...
                </div>
            </div>
        `;

        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        document.getElementById('paymentModalContent').innerHTML = modalContent;
        hideLoading();
        paymentModal.show();

        // Função para copiar o código PIX
        window.copyPixCode = function() {
            const pixCode = document.getElementById('pixCode');
            pixCode.select();
            document.execCommand('copy');
            const copyButton = pixCode.nextElementSibling;
            copyButton.innerHTML = '<i class="bi bi-check"></i> Copiado';
            setTimeout(() => {
                copyButton.innerHTML = '<i class="bi bi-clipboard"></i> Copiar';
            }, 2000);
        };

        // Verificar status do pagamento a cada 5 segundos
        const checkPayment = async () => {
            try {
                const statusResponse = await fetch(`../php/verificar_pagamento_pix.php?agendamento_id=${data.data.agendamento_id}`);
                const statusData = await statusResponse.json();

                if (!statusData.success) {
                    throw new Error(statusData.error || 'Erro ao verificar pagamento');
                }

                const statusElement = document.getElementById('paymentStatus');
                statusElement.textContent = statusData.message;

                if (statusData.pago) {
                    statusElement.classList.remove('alert-info');
                    statusElement.classList.add('alert-success');
                    setTimeout(() => {
                        paymentModal.hide();
                        showSection('meusAgendamentosSection');
                        loadMeusAgendamentos();
                    }, 2000);
                    return;
                } else if (statusData.error) {
                    statusElement.classList.remove('alert-info');
                    statusElement.classList.add('alert-danger');
                    return;
                }

                setTimeout(checkPayment, 5000);
            } catch (error) {
                console.error('Erro ao verificar pagamento:', error);
            }
        };

        checkPayment();

    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

// Função para carregar meus agendamentos
async function loadMeusAgendamentos() {
    try {
        const response = await fetch('../php/historico_agendamentos.php');
        const data = await response.json();

        if (data.status === 'error') {
            throw new Error(data.message || 'Erro ao carregar agendamentos');
        }

        const agendamentosContainer = document.getElementById('agendamentosList');
        agendamentosContainer.innerHTML = data.agendamentos.map(agendamento => {
            const statusClass = {
                'pendente': 'status-pendente',
                'confirmado': 'status-confirmado',
                'realizado': 'status-realizado',
                'cancelado': 'status-cancelado'
            }[agendamento.status] || 'status-pendente';

            return `
                <div class="col-md-4 mb-3">
                    <div class="card agendamento-card">
                        <div class="card-body">
                            <span class="status-badge ${statusClass}">${agendamento.status}</span>
                            <h5 class="card-title">${agendamento.servico}</h5>
                            <p class="card-text">
                                <i class="bi bi-shop"></i> ${agendamento.salao}<br>
                                <i class="bi bi-person"></i> ${agendamento.profissional}<br>
                                <i class="bi bi-calendar-event"></i> ${agendamento.data}<br>
                                <i class="bi bi-clock"></i> ${agendamento.hora}<br>
                                <i class="bi bi-currency-dollar"></i> R$ ${agendamento.valor}
                            </p>
                            ${agendamento.status === 'pendente' ? `
                                <button class="btn btn-outline-danger w-100" onclick="cancelarAgendamento(${agendamento.id})">
                                    <i class="bi bi-x-circle"></i> Cancelar Agendamento
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        if (data.agendamentos.length === 0) {
            agendamentosContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Você não possui agendamentos.
                    </div>
                </div>
            `;
        }
    } catch (error) {
        const agendamentosContainer = document.getElementById('agendamentosList');
        agendamentosContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> ${error.message}
                </div>
            </div>
        `;
    }
}

// Função para cancelar agendamento
async function cancelarAgendamento(agendamentoId) {
    if (!confirm('Tem certeza que deseja cancelar este agendamento?')) {
        return;
    }

    try {
        showLoading();
        const response = await fetch('../php/cancelar_agendamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                agendamento_id: agendamentoId
            })
        });

        const data = await response.json();
        hideLoading();

        if (!data.success) {
            throw new Error(data.message || 'Erro ao cancelar agendamento');
        }

        showSuccess('Agendamento cancelado com sucesso!');
        await loadMeusAgendamentos();
    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

// Função para filtrar salões
function filterSaloes(searchTerm) {
    const cards = document.querySelectorAll('#saloesList .col-md-4');
    const term = searchTerm.toLowerCase();

    cards.forEach(card => {
        const title = card.querySelector('.card-title').textContent.toLowerCase();
        if (title.includes(term)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Função para resetar seleção
function resetSelection() {
    selectedSalao = null;
    selectedServico = null;
    selectedProfissional = null;
    selectedData = null;
    selectedHorario = null;

    document.getElementById('servicosSection').style.display = 'none';
    document.getElementById('profissionaisSection').style.display = 'none';
    document.getElementById('dataSection').style.display = 'none';
    document.getElementById('horarioSection').style.display = 'none';
    document.getElementById('resumoSection').style.display = 'none';
}

// Função para carregar perfil
async function loadPerfil() {
    try {
        const response = await fetch('../php/get_profile.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar perfil');
        }

        document.getElementById('nome').value = data.usuario.nome;
        document.getElementById('email').value = data.usuario.email;
        document.getElementById('telefone').value = data.usuario.telefone;
    } catch (error) {
        showError(error.message);
    }
}

// Função para salvar perfil
async function salvarPerfil() {
    try {
        showLoading();
        const response = await fetch('../php/atualizar_perfil.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nome: document.getElementById('nome').value,
                email: document.getElementById('email').value,
                telefone: document.getElementById('telefone').value
            })
        });

        const data = await response.json();
        hideLoading();

        if (!data.success) {
            throw new Error(data.message || 'Erro ao salvar perfil');
        }

        showSuccess('Perfil atualizado com sucesso!');
    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}