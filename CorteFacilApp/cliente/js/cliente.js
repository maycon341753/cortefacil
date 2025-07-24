// Variáveis globais para o modal de agendamento
let currentStep = 1;
let agendamentoModal;
let selectedSalao = null;
let selectedServico = null;
let selectedProfissional = null;
let selectedData = null;
let selectedHorario = null;

// Elementos DOM frequentemente utilizados
var loadingModal;
var successModal;
var errorModal;

// Inicializar modais quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM carregado, inicializando aplicação...');
    
    // Verificar se os elementos necessários existem
    const loadingModalElement = document.getElementById('loadingModal');
    const successModalElement = document.getElementById('successModal');
    const errorModalElement = document.getElementById('errorModal');
    const agendamentoModalElement = document.getElementById('agendamentoModal');
    const saloesList = document.getElementById('saloesList');
    
    console.log('Elementos encontrados:', {
        loadingModal: !!loadingModalElement,
        successModal: !!successModalElement,
        errorModal: !!errorModalElement,
        agendamentoModal: !!agendamentoModalElement,
        saloesList: !!saloesList
    });
    
    // Inicializar modais
    if (loadingModalElement) loadingModal = new bootstrap.Modal(loadingModalElement);
    if (successModalElement) successModal = new bootstrap.Modal(successModalElement);
    if (errorModalElement) errorModal = new bootstrap.Modal(errorModalElement);
    if (agendamentoModalElement) agendamentoModal = new bootstrap.Modal(agendamentoModalElement);

    // Carregar salões ao iniciar
    if (saloesList) {
        loadSaloes();
    } else {
        console.error('Elemento saloesList não encontrado no DOM!');
    }

    // Configurar eventos dos botões do modal
    const btnVoltar = document.getElementById('btnVoltar');
    const btnAvancar = document.getElementById('btnAvancar');
    const btnConfirmar = document.getElementById('btnConfirmar');
    
    if (btnVoltar) btnVoltar.addEventListener('click', voltarEtapa);
    if (btnAvancar) btnAvancar.addEventListener('click', avancarEtapa);
    if (btnConfirmar) btnConfirmar.addEventListener('click', confirmarAgendamento);

    // Configurar eventos dos links da navbar
    const meusAgendamentosLink = document.getElementById('meusAgendamentosLink');
    const novoAgendamentoLink = document.getElementById('novoAgendamentoLink');
    const logoutLink = document.getElementById('logoutLink');
    
    if (meusAgendamentosLink) {
        meusAgendamentosLink.addEventListener('click', (e) => {
            console.log('Link Meus Agendamentos clicado');
            e.preventDefault();
            showSection('meusAgendamentosSection');
            loadMeusAgendamentos();
        });
    } else {
        console.log('Link meusAgendamentosLink não encontrado');
    }
    
    if (novoAgendamentoLink) {
        novoAgendamentoLink.addEventListener('click', (e) => {
            e.preventDefault();
            showSection('saloesSection');
        });
    }

    // Configurar evento de logout
    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            e.preventDefault();
            realizarLogout();
        });
    }

    // Evento para resetar o modal quando for fechado
    document.getElementById('agendamentoModal').addEventListener('hidden.bs.modal', resetarModal);
});

function fecharModal(id){
    const modalElement = document.getElementById(id);
    const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    modalInstance.hide();
}

// Função para mostrar mensagem de erro
function showError(message) {
    console.error('Erro:', message);
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
    console.log('Mostrando loading...');
    if (loadingModal) {
        loadingModal.show();
    } else {
        console.error('Loading modal não está inicializado');
    }
}

// Função para esconder loading
function hideLoading() {
    console.log('Escondendo loading...');
    if (loadingModal) {
        loadingModal.hide();
    } else {
        console.error('Loading modal não está inicializado');
    }
}

// Função para mostrar/esconder seções
function showSection(sectionId) {
    console.log('Mostrando seção:', sectionId);
    
    // Lista de todas as seções que devem ser escondidas
    const sections = [
        'saloesSection', 'servicosSection', 'profissionaisSection',
        'dataSection', 'horarioSection', 'resumoSection',
        'meusAgendamentosSection'
    ];
    
    // Esconder todas as seções
    sections.forEach(id => {
        const section = document.getElementById(id);
        if (section) {
            section.style.display = 'none';
        }
    });
    
    // Esconder o banner se não for a seção de salões
    const banner = document.querySelector('.banner');
    if (banner) {
        banner.style.display = sectionId === 'saloesSection' ? 'block' : 'none';
    }
    
    // Mostrar a seção solicitada
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.style.display = 'block';
        console.log('Seção exibida com sucesso:', sectionId);
    } else {
        console.error(`Seção não encontrada: ${sectionId}`);
    }
}

// Função para carregar salões disponíveis
async function loadSaloes() {
    try {
        console.log('Iniciando carregamento de salões...');
        showLoading();
        console.log('Fazendo requisição para listar_saloes.php...');
        const response = await fetch('../php/listar_saloes.php');
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Dados recebidos:', data);
        console.log('Status da resposta:', data.status);
        console.log('Número de salões recebidos:', data.saloes ? data.saloes.length : 0);

        if (data.status !== 'success') {
            console.error('Status da resposta não é success:', data.status);
            throw new Error(data.message || 'Erro ao carregar salões');
        }
        console.log('Status da resposta é success, continuando...');

        const saloesContainer = document.getElementById('saloesList');
        console.log('Container encontrado:', saloesContainer);
        console.log('Container existe:', saloesContainer ? 'Sim' : 'Não');
        console.log('Número de salões:', data.saloes.length);
        
        if (!saloesContainer) {
            console.error('Container saloesList não encontrado!');
            hideLoading();
            return;
        }
        
        if (data.saloes.length === 0) {
            saloesContainer.innerHTML = '<div class="col-12"><p class="text-center">Nenhum salão disponível no momento.</p></div>';
            hideLoading();
            return;
        }
        
        console.log('Gerando HTML dos salões...');
        let saloesHtml = '';
        try {
            saloesHtml = data.saloes.map(salao => {
                console.log('Processando salão:', salao.nome);
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
                                <button class="btn btn-agendar w-100" onclick='selectSalao(${salao.id})'>
                                    Agendar Agora
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            console.log('HTML gerado com sucesso, tamanho:', saloesHtml.length);
        } catch (error) {
            console.error('Erro ao gerar HTML dos salões:', error);
            saloesHtml = '<div class="col-12"><p class="text-center">Erro ao processar salões. Por favor, tente novamente.</p></div>';
        }
        
        console.log('HTML gerado, inserindo no container...');
        try {
            if (saloesContainer) {
                saloesContainer.innerHTML = saloesHtml;
                console.log('HTML inserido com sucesso!');
            } else {
                console.error('Container saloesList não encontrado para inserir HTML!');
                throw new Error('Container saloesList não encontrado');
            }
        } catch (error) {
            console.error('Erro ao inserir HTML no container:', error);
        }
        
        // Inicializar pesquisa para o campo da navbar
        const searchElement = document.getElementById('searchSalao');
        if (searchElement) {
            searchElement.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterSaloes(searchTerm);
                // Sincronizar com o campo do banner
                const bannerInput = document.getElementById('bannerSearchInput');
                if (bannerInput && bannerInput.value.toLowerCase() !== searchTerm) {
                    bannerInput.value = e.target.value;
                }
            });
        }
        
        // Inicializar pesquisa para o campo do banner
        const bannerSearchInput = document.getElementById('bannerSearchInput');
        const bannerSearchButton = document.getElementById('bannerSearchButton');
        
        if (bannerSearchInput) {
            bannerSearchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterSaloes(searchTerm);
                // Sincronizar com o campo da navbar
                if (searchElement && searchElement.value.toLowerCase() !== searchTerm) {
                    searchElement.value = e.target.value;
                }
            });
            
            bannerSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const searchTerm = e.target.value.toLowerCase();
                    filterSaloes(searchTerm);
                }
            });
        }
        
        if (bannerSearchButton) {
            bannerSearchButton.addEventListener('click', function(e) {
                e.preventDefault();
                const searchInput = document.getElementById('bannerSearchInput');
                if (searchInput) {
                    const searchTerm = searchInput.value.toLowerCase();
                    filterSaloes(searchTerm);
                }
            });
        }
        
        // Adicionar botão de limpar busca na navbar
        const searchButton = document.getElementById('searchButton');
        if (searchButton && searchElement) {
            searchButton.addEventListener('click', function(e) {
                e.preventDefault();
                searchElement.value = '';
                const bannerInput = document.getElementById('bannerSearchInput');
                if (bannerInput) {
                    bannerInput.value = '';
                }
                filterSaloes('');
            });
        }
        
        hideLoading();
        console.log('Carregamento de salões concluído!');
    } catch (error) {
        console.error('Erro no carregamento de salões:', error);
        hideLoading();
        showError(error.message);
    }
}

// Função para filtrar salões por termo de pesquisa
function filterSaloes(searchTerm) {
    console.log('filterSaloes chamada com termo:', searchTerm);
    
    const saloes = document.querySelectorAll('.salao-item');
    console.log('Número de salões encontrados:', saloes.length);
    
    let saloesVisiveis = 0;
    
    saloes.forEach((salao, index) => {
        const salaoNomeElement = salao.querySelector('.salao-nome');
        const salaoEnderecoElement = salao.querySelector('.salao-endereco');
        
        if (!salaoNomeElement || !salaoEnderecoElement) {
            console.warn('Elementos de nome ou endereço não encontrados no salão:', salao);
            return;
        }
        
        const salaoNome = salaoNomeElement.textContent.toLowerCase();
        const salaoEndereco = salaoEnderecoElement.textContent.toLowerCase();
        
        console.log(`Salão ${index + 1}: Nome="${salaoNome}", Endereço="${salaoEndereco}"`);
        
        // Buscar por nome do salão, endereço completo ou cidade
        const matchesSearch = salaoNome.includes(searchTerm) || 
                             salaoEndereco.includes(searchTerm) ||
                             // Extrair apenas a cidade do endereço (parte após a vírgula)
                             (salaoEndereco.includes(',') && 
                              salaoEndereco.split(',').pop().trim().includes(searchTerm));
        
        console.log(`Salão ${index + 1} matches search "${searchTerm}":`, matchesSearch);
        
        if (matchesSearch) {
            salao.style.display = 'block';
            saloesVisiveis++;
        } else {
            salao.style.display = 'none';
        }
    });
    
    // Mostrar mensagem se nenhum salão for encontrado
    const saloesContainer = document.getElementById('saloesList');
    if (saloesContainer) {
        let noResultsMessage = saloesContainer.querySelector('.no-results-message');
        
        if (saloesVisiveis === 0 && searchTerm.trim() !== '') {
            if (!noResultsMessage) {
                noResultsMessage = document.createElement('div');
                noResultsMessage.className = 'col-12 no-results-message';
                noResultsMessage.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">Nenhum salão encontrado</h4>
                        <p class="text-muted">Tente buscar por outro nome ou cidade</p>
                    </div>
                `;
                saloesContainer.appendChild(noResultsMessage);
            }
            noResultsMessage.style.display = 'block';
        } else if (noResultsMessage) {
            noResultsMessage.style.display = 'none';
        }
    }
    
    console.log(`Filtro aplicado: "${searchTerm}" - ${saloesVisiveis} salões visíveis`);
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

// Função para selecionar salão e iniciar agendamento
async function selectSalao(salaoId) {
    try {
        selectedSalao = salaoId;
        currentStep = 1;
        
        // Mostrar modal de agendamento
        agendamentoModal.show();
        
        // Atualizar indicador de progresso
        updateProgressIndicator(1);
        
        // Carregar serviços
        await loadServicos(salaoId);
        
        // Mostrar botões apropriados
        document.getElementById('btnVoltar').style.display = 'none';
        document.getElementById('btnAvancar').style.display = 'none';
        document.getElementById('btnConfirmar').style.display = 'none';

        fecharModal('loadingModal');
        
    } catch (error) {
        console.error('Erro ao iniciar agendamento:', error);
        showError(error.message || 'Erro ao iniciar agendamento');
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
    console.log('=== INICIANDO CARREGAMENTO DE SERVIÇOS ===');
    console.log('Salão ID:', salaoId);
    
    try {
        const url = `../php/listar_servicos.php?salao_id=${salaoId}`;
        console.log('URL da requisição:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Erro ao fazer parse do JSON:', parseError);
            console.error('Response text que causou erro:', responseText);
            throw new Error('Resposta inválida do servidor');
        }
        
        console.log('Dados recebidos:', data);
        
        if (data.status !== 'success') {
            console.error('Status não é success:', data.status);
            console.error('Mensagem de erro:', data.message);
            throw new Error(data.message || 'Erro ao carregar serviços');
        }

        const servicosContainer = document.getElementById('modalServicosList');
        console.log('Container encontrado:', !!servicosContainer);
        
        if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
            console.log('Nenhum serviço encontrado');
            console.log('data.data:', data.data);
            servicosContainer.innerHTML = '<div class="col-12"><p class="text-center">Nenhum serviço disponível para este salão.</p></div>';
            return;
        }

        console.log('Número de serviços encontrados:', data.data.length);
        console.log('Serviços:', data.data);

        const servicosHtml = data.data.map(servico => {
            console.log('Processando serviço:', servico);
            return `
                <div class="col-md-4 mb-3">
                    <div class="card service-card h-100" onclick="selectServicoModal(${servico.id}, this)">
                        <div class="card-body">
                            <h5 class="card-title">${servico.nome}</h5>
                            <p class="card-text">${servico.preco_formatado}</p>
                            <p class="card-text">${servico.duracao_minutos} minutos</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        console.log('HTML gerado:', servicosHtml);
        servicosContainer.innerHTML = servicosHtml;
        console.log('HTML inserido no container');

        fecharModal('loadingModal');

    } catch (error) {
        console.error('=== ERRO AO CARREGAR SERVIÇOS ===');
        console.error('Erro:', error);
        console.error('Stack trace:', error.stack);
        showError('Não foi possível carregar os serviços. Por favor, tente novamente.');
    } finally {
        hideLoading();
        console.log('=== FIM DO CARREGAMENTO DE SERVIÇOS ===');
    }
}

// Função para selecionar serviço no modal
function selectServicoModal(servicoId, element) {
    selectedServico = servicoId;
    
    // Remover seleção anterior
    document.querySelectorAll('.service-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Adicionar seleção ao card clicado
    element.classList.add('selected');
    
    // Mostrar botão avançar
    document.getElementById('btnAvancar').style.display = 'block';
}

// Função para carregar profissionais
async function loadProfissionais(salaoId) {
    try {
        const response = await fetch(`../php/listar_profissionais.php?salao_id=${salaoId}`);
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.mensagem || 'Erro ao carregar profissionais');
        }

        const profissionaisContainer = document.getElementById('modalProfissionaisList');
        if (!data.profissionais || !Array.isArray(data.profissionais) || data.profissionais.length === 0) {
            profissionaisContainer.innerHTML = '<div class="col-12"><p class="text-center">Nenhum profissional disponível.</p></div>';
            return;
        }

        profissionaisContainer.innerHTML = data.profissionais.map(profissional => `
            <div class="col-md-4 mb-3">
                <div class="card professional-card" onclick="selectProfissionalModal(${profissional.id}, this)">
                    <div class="card-body text-center">
                        <img src="${profissional.foto || '../assets/default-avatar.png'}" class="professional-avatar mb-3">
                        <h5 class="card-title">${profissional.nome}</h5>
                        <p class="card-text text-muted">${profissional.especialidade || ''}</p>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Erro ao carregar profissionais:', error);
        throw new Error('Não foi possível carregar os profissionais. Por favor, tente novamente.');
    }
}

// Função para selecionar profissional no modal
function selectProfissionalModal(profissionalId, element) {
    selectedProfissional = profissionalId;
    
    // Remover seleção anterior
    document.querySelectorAll('.professional-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Adicionar seleção ao card clicado
    element.classList.add('selected');
    
    // Mostrar botão avançar
    document.getElementById('btnAvancar').style.display = 'block';
}

// Função para inicializar o calendário no modal
function initializeModalCalendar() {
    const calendarEl = document.getElementById('modalCalendar');
    if (!calendarEl) {
        throw new Error('Elemento do calendário não encontrado');
    }
    
    // Limpar qualquer conteúdo existente
    calendarEl.innerHTML = '';
    
    const hoje = new Date();
    const trintaDiasDepois = new Date();
    trintaDiasDepois.setDate(hoje.getDate() + 30);

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
            
            // Mostrar botão avançar
            document.getElementById('btnAvancar').style.display = 'block';
        }
    });

    calendar.render();
}

// Função para carregar horários disponíveis
async function loadHorarios() {
    try {
        if (!selectedData) {
            throw new Error('Data não selecionada');
        }

        const formattedDate = selectedData.toISOString().split('T')[0];
        
        // Incluir servico_id na URL se estiver disponível
        let url = `../php/listar_horarios_disponiveis.php?salao_id=${selectedSalao}&profissional_id=${selectedProfissional}&data=${formattedDate}`;
        if (selectedServico) {
            url += `&servico_id=${selectedServico}`;
        }
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Erro ao carregar horários');
        }

        const horariosContainer = document.getElementById('modalHorariosList');
        if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
            horariosContainer.innerHTML = '<div class="col-12"><p class="text-center">Nenhum horário disponível para esta data.</p></div>';
            return;
        }

        horariosContainer.innerHTML = data.data.map(horario => `
            <div class="col-md-3 col-sm-4 col-6 mb-2">
                <div class="card time-slot" onclick="selectHorarioModal('${horario}', this)">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-0">${horario}</h5>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Erro ao carregar horários:', error);
        throw new Error('Não foi possível carregar os horários. Por favor, tente novamente.');
    }
}

// Função para selecionar horário no modal
function selectHorarioModal(horario, element) {
    selectedHorario = horario;
    
    // Remover seleção anterior
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Adicionar seleção ao slot clicado
    element.classList.add('selected');
    
    // Mostrar botão avançar
    document.getElementById('btnAvancar').style.display = 'block';
}

// Função para atualizar o resumo do agendamento
function updateResumo() {
    const formattedDate = selectedData.toLocaleDateString('pt-BR');
    document.getElementById('resumoAgendamento').innerHTML = `
        <h4>Resumo do Agendamento</h4>
        <p><strong>Data:</strong> ${formattedDate}</p>
        <p><strong>Horário:</strong> ${selectedHorario}</p>
        <p><strong>Valor:</strong> R$ 0,99</p>
        <div class="alert alert-info mb-3">
            <small><strong>Observação:</strong> Este valor refere-se apenas à taxa de agendamento. O pagamento do serviço será realizado diretamente no salão.</small>
        </div>
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
                <div class="alert alert-info mb-3">
                    <strong>Observação:</strong> Este valor refere-se apenas à taxa de agendamento. O pagamento do serviço será realizado diretamente no salão.
                </div>
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
                        fecharModal('paymentModal');
                        fecharModal('agendamentoModal');
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
// Variáveis para paginação de agendamentos
let currentPage = 1;
let agendamentosPerPage = 6;
let totalAgendamentos = [];

async function loadMeusAgendamentos() {
    console.log('loadMeusAgendamentos chamada');
    try {
        console.log('Fazendo fetch para historico_agendamentos.php');
        const response = await fetch('../php/historico_agendamentos.php');
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Data recebida:', data);

        if (data.status === 'error') {
            throw new Error(data.message || 'Erro ao carregar agendamentos');
        }

        // Armazenar todos os agendamentos
        totalAgendamentos = data.agendamentos || [];
        
        // Resetar para primeira página
        currentPage = 1;
        
        // Renderizar agendamentos paginados
        renderAgendamentosPaginados();
        
    } catch (error) {
        const agendamentosContainer = document.getElementById('agendamentosList');
        agendamentosContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> ${error.message}
                </div>
            </div>
        `;
        updatePaginationInfo(0, 0, 0);
    }
}

function renderAgendamentosPaginados() {
    const agendamentosContainer = document.getElementById('agendamentosList');
    
    if (totalAgendamentos.length === 0) {
        agendamentosContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Você não possui agendamentos.
                </div>
            </div>
        `;
        updatePaginationInfo(0, 0, 0);
        return;
    }

    // Calcular índices para a página atual
    const startIndex = (currentPage - 1) * agendamentosPerPage;
    const endIndex = startIndex + agendamentosPerPage;
    const agendamentosPagina = totalAgendamentos.slice(startIndex, endIndex);
    
    // Renderizar agendamentos da página atual
    agendamentosContainer.innerHTML = agendamentosPagina.map(agendamento => {
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

    // Atualizar informações de paginação
    const totalPages = Math.ceil(totalAgendamentos.length / agendamentosPerPage);
    updatePaginationInfo(startIndex + 1, Math.min(endIndex, totalAgendamentos.length), totalAgendamentos.length);
    updatePaginationControls(totalPages);
}

function updatePaginationInfo(start, end, total) {
    const infoElement = document.getElementById('agendamentosInfo');
    if (total === 0) {
        infoElement.textContent = 'Nenhum agendamento encontrado';
    } else {
        infoElement.textContent = `Mostrando ${start} a ${end} de ${total} agendamentos`;
    }
}

function updatePaginationControls(totalPages) {
    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationList = document.getElementById('paginationList');
    
    // Atualizar botões anterior/próximo
    btnPrev.disabled = currentPage === 1;
    btnNext.disabled = currentPage === totalPages || totalPages === 0;
    
    // Mostrar/esconder paginação
    if (totalPages <= 1) {
        paginationContainer.style.display = 'none';
    } else {
        paginationContainer.style.display = 'flex';
        
        // Gerar números das páginas
        let paginationHTML = '';
        
        // Botão anterior
        paginationHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${currentPage - 1})" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;
        
        // Números das páginas
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
                    </li>
                `;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }
        
        // Botão próximo
        paginationHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${currentPage + 1})" aria-label="Próximo">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;
        
        paginationList.innerHTML = paginationHTML;
    }
}

function goToPage(page) {
    const totalPages = Math.ceil(totalAgendamentos.length / agendamentosPerPage);
    if (page >= 1 && page <= totalPages && page !== currentPage) {
        currentPage = page;
        renderAgendamentosPaginados();
    }
}

// Event listeners para os botões de navegação
document.addEventListener('DOMContentLoaded', function() {
    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');
    
    if (btnPrev) {
        btnPrev.addEventListener('click', function() {
            if (currentPage > 1) {
                goToPage(currentPage - 1);
            }
        });
    }
    
    if (btnNext) {
        btnNext.addEventListener('click', function() {
            const totalPages = Math.ceil(totalAgendamentos.length / agendamentosPerPage);
            if (currentPage < totalPages) {
                goToPage(currentPage + 1);
            }
        });
    }
});

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
            throw new Error(data.error || 'Erro ao cancelar agendamento');
        }

        showSuccess('Agendamento cancelado com sucesso!');
        await loadMeusAgendamentos();
    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

// Função para filtrar salões por cards
function filterSaloesCards(searchTerm) {
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

// Função para atualizar indicador de progresso
function updateProgressIndicator(step) {
    // Atualizar círculos
    for (let i = 1; i <= 5; i++) {
        const stepElement = document.getElementById(`step${i}`);
        if (i < step) {
            stepElement.classList.add('completed');
            stepElement.classList.remove('active');
        } else if (i === step) {
            stepElement.classList.add('active');
            stepElement.classList.remove('completed');
        } else {
            stepElement.classList.remove('active', 'completed');
        }
    }
    
    // Atualizar linha de progresso
    const progressLine = document.querySelector('.progress-line');
    const progress = ((step - 1) / 4) * 100;
    progressLine.style.width = `${progress}%`;
}

// Função para mostrar seção apropriada
function showModalSection(step) {
    const sections = {
        1: 'modalServicosSection',
        2: 'modalProfissionaisSection',
        3: 'modalDataSection',
        4: 'modalHorarioSection',
        5: 'modalResumoSection'
    };
    
    if(step == 3){
        let interval = setInterval(function () { 
            clearInterval(interval);
            document.querySelector('.fc-next-button ').click();
            document.querySelector('.fc-prev-button ').click();
        }, 100)

    }

    // Esconder todas as seções
    Object.values(sections).forEach(sectionId => {
        document.getElementById(sectionId).style.display = 'none';
    });
    
    // Mostrar seção atual
    document.getElementById(sections[step]).style.display = 'block';
}

// Função para avançar etapa
async function avancarEtapa() {
    if (!validarEtapaAtual()) {
        return;
    }
    
    currentStep++;
    updateProgressIndicator(currentStep);
    
    // Mostrar/esconder botões apropriados
    document.getElementById('btnVoltar').style.display = 'block';
    document.getElementById('btnAvancar').style.display = currentStep < 5 ? 'block' : 'none';
    document.getElementById('btnConfirmar').style.display = currentStep === 5 ? 'block' : 'none';
    
    try {
        showLoading();
        switch(currentStep) {
            case 2:
                await loadProfissionais(selectedSalao);
                break;
            case 3:
                initializeModalCalendar();
                break;
            case 4:
                await loadHorarios();
                break;
            case 5:
                updateModalResumo();
                break;
        }
    } catch (error) {
        console.error('Erro ao carregar dados da etapa:', error);
        showError(error.message);
        voltarEtapa();
    } finally {
        hideLoading();
    }
    
    showModalSection(currentStep);
}

// Função para voltar etapa
function voltarEtapa() {
    if (currentStep > 1) {
        currentStep--;
        updateProgressIndicator(currentStep);
        
        // Mostrar/esconder botões apropriados
        document.getElementById('btnVoltar').style.display = currentStep > 1 ? 'block' : 'none';
        document.getElementById('btnAvancar').style.display = 'block';
        document.getElementById('btnConfirmar').style.display = 'none';
        
        showModalSection(currentStep);
    }
}

// Função para validar etapa atual
function validarEtapaAtual() {
    switch(currentStep) {
        case 1:
            if (!selectedServico) {
                showError('Por favor, selecione um serviço para continuar.');
                return false;
            }
            break;
        case 2:
            if (!selectedProfissional) {
                showError('Por favor, selecione um profissional para continuar.');
                return false;
            }
            break;
        case 3:
            if (!selectedData) {
                showError('Por favor, selecione uma data para continuar.');
                return false;
            }
            break;
        case 4:
            if (!selectedHorario) {
                showError('Por favor, selecione um horário para continuar.');
                return false;
            }
            break;
    }
    return true;
}

// Função para resetar modal
function resetarModal() {
    currentStep = 1;
    selectedServico = null;
    selectedProfissional = null;
    selectedData = null;
    selectedHorario = null;
    
    updateProgressIndicator(1);
    showModalSection(1);
    
    // Resetar seleções visuais
    document.querySelectorAll('.service-card, .professional-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Resetar botões
    document.getElementById('btnVoltar').style.display = 'none';
    document.getElementById('btnAvancar').style.display = 'none';
    document.getElementById('btnConfirmar').style.display = 'none';
}

// Função para atualizar resumo no modal
function updateModalResumo() {
    const formattedDate = selectedData.toLocaleDateString('pt-BR');
    document.getElementById('modalResumoAgendamento').innerHTML = `
        <h4 class="mb-4">Resumo do Agendamento</h4>
        <div class="mb-3">
            <strong>Serviço:</strong> ${document.querySelector('.service-card.selected .card-title').textContent}
        </div>
        <div class="mb-3">
            <strong>Profissional:</strong> ${document.querySelector('.professional-card.selected .card-title').textContent}
        </div>
        <div class="mb-3">
            <strong>Data:</strong> ${formattedDate}
        </div>
        <div class="mb-3">
            <strong>Horário:</strong> ${selectedHorario}
        </div>
        <div class="mb-3">
            <strong>Valor:</strong> R$ 0,99
        </div>
        <div class="alert alert-info mb-3">
            <small><strong>Observação:</strong> Este valor refere-se apenas à taxa de agendamento. O pagamento do serviço será realizado diretamente no salão.</small>
        </div>
    `;
}

// Função para realizar logout
async function realizarLogout() {
    try {
        showLoading();
        
        const response = await fetch('../php/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Redirecionar para a página de login
            window.location.href = 'login.php';
        } else {
            throw new Error(data.mensagem || 'Erro ao realizar logout');
        }
    } catch (error) {
        hideLoading();
        showError('Erro ao realizar logout: ' + error.message);
    }
}