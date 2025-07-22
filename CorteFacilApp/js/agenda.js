// Variáveis globais
let calendar;
let selectedEvent = null;

// Função para carregar a agenda
async function loadAgenda() {
    try {
        // Inicializa o calendário se ainda não foi inicializado
        if (!calendar) {
            initializeCalendar();
        }
        
        // Carrega os eventos
        await loadEvents();
        
        // Carrega os profissionais para o filtro
        await loadProfissionaisFiltro();
    } catch (error) {
        console.error('Erro ao carregar agenda:', error);
        showError('Erro ao carregar agenda. Por favor, tente novamente.');
    }
}

// Inicializa o calendário
function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        allDaySlot: false,
        slotMinTime: '08:00:00',
        slotMaxTime: '20:00:00',
        slotDuration: '00:30:00',
        selectable: true,
        selectMirror: true,
        editable: true,
        dayMaxEvents: true,
        select: handleDateSelect,
        eventClick: handleEventClick,
        eventDrop: handleEventDrop,
        eventResize: handleEventResize,
        eventDidMount: function(info) {
            // Adiciona tooltip com informações detalhadas
            const tooltip = new bootstrap.Tooltip(info.el, {
                title: `${info.event.title}\nCliente: ${info.event.extendedProps.cliente}\nServiço: ${info.event.extendedProps.servico}\nProfissional: ${info.event.extendedProps.profissional}`,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });

    calendar.render();
}

// Carrega os eventos do calendário
async function loadEvents() {
    try {
        showLoading();
        const profissionalId = document.getElementById('filtroProfissional').value;
        const response = await fetch(`php/listar_agendamentos.php?profissional_id=${profissionalId}`);
        const data = await response.json();

        if (data.status === 'sucesso') {
            // Limpa eventos existentes
            calendar.removeAllEvents();

            // Adiciona os novos eventos
            const events = data.agendamentos.map(agendamento => ({
                id: agendamento.id,
                title: `${agendamento.cliente_nome} - ${agendamento.servico_nome}`,
                start: `${agendamento.data}T${agendamento.horario_inicio}`,
                end: `${agendamento.data}T${agendamento.horario_fim}`,
                backgroundColor: getStatusColor(agendamento.status),
                borderColor: getStatusColor(agendamento.status),
                extendedProps: {
                    cliente: agendamento.cliente_nome,
                    servico: agendamento.servico_nome,
                    profissional: agendamento.profissional_nome,
                    status: agendamento.status
                }
            }));

            calendar.addEventSource(events);
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
        showError('Erro ao carregar eventos. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Carrega os profissionais para o filtro
async function loadProfissionaisFiltro() {
    try {
        const response = await fetch('php/listar_profissionais.php');
        const data = await response.json();

        if (data.status === 'sucesso') {
            const select = document.getElementById('filtroProfissional');
            select.innerHTML = '<option value="">Todos os profissionais</option>';

            data.profissionais.forEach(profissional => {
                const option = document.createElement('option');
                option.value = profissional.id;
                option.textContent = profissional.nome;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar profissionais:', error);
        showError('Erro ao carregar profissionais. Por favor, tente novamente.');
    }
}

// Manipuladores de eventos do calendário
function handleDateSelect(selectInfo) {
    selectedEvent = null;
    const modal = new bootstrap.Modal(document.getElementById('modalAgendamento'));
    
    // Preenche os campos de data e hora
    document.getElementById('data').value = selectInfo.startStr.split('T')[0];
    document.getElementById('horario_inicio').value = selectInfo.startStr.split('T')[1].slice(0, 5);
    document.getElementById('horario_fim').value = selectInfo.endStr.split('T')[1].slice(0, 5);
    
    modal.show();
    calendar.unselect();
}

function handleEventClick(clickInfo) {
    selectedEvent = clickInfo.event;
    const modal = new bootstrap.Modal(document.getElementById('modalAgendamento'));
    
    // Preenche os campos com os dados do evento
    document.getElementById('agendamentoId').value = selectedEvent.id;
    document.getElementById('data').value = selectedEvent.startStr.split('T')[0];
    document.getElementById('horario_inicio').value = selectedEvent.startStr.split('T')[1].slice(0, 5);
    document.getElementById('horario_fim').value = selectedEvent.endStr.split('T')[1].slice(0, 5);
    // ... preencher outros campos
    
    modal.show();
}

async function handleEventDrop(dropInfo) {
    try {
        showLoading();
        const event = dropInfo.event;
        const response = await fetch('php/atualizar_agendamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: event.id,
                data: event.startStr.split('T')[0],
                horario_inicio: event.startStr.split('T')[1].slice(0, 5),
                horario_fim: event.endStr.split('T')[1].slice(0, 5)
            })
        });

        const data = await response.json();

        if (data.status !== 'sucesso') {
            dropInfo.revert();
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao atualizar agendamento:', error);
        dropInfo.revert();
        showError('Erro ao atualizar agendamento. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

function handleEventResize(resizeInfo) {
    handleEventDrop(resizeInfo);
}

// Função auxiliar para determinar a cor do evento baseado no status
function getStatusColor(status) {
    const colors = {
        'agendado': '#4e73df',      // Azul
        'confirmado': '#1cc88a',     // Verde
        'cancelado': '#e74a3b',      // Vermelho
        'concluido': '#858796',      // Cinza
        'default': '#4e73df'         // Azul (padrão)
    };
    return colors[status] || colors.default;
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Configura o filtro de profissionais
    const filtroProfissional = document.getElementById('filtroProfissional');
    if (filtroProfissional) {
        filtroProfissional.addEventListener('change', loadEvents);
    }

    // Configura o formulário de agendamento
    const formAgendamento = document.getElementById('formAgendamento');
    if (formAgendamento) {
        formAgendamento.addEventListener('submit', handleFormSubmit);
    }

    // Inicializa o calendário
    loadAgenda();
});

// Manipulador do formulário de agendamento
async function handleFormSubmit(e) {
    e.preventDefault();
    showLoading();

    try {
        const formData = new FormData(e.target);
        const response = await fetch('php/salvar_agendamento.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'sucesso') {
            showSuccess(data.mensagem);
            bootstrap.Modal.getInstance(document.getElementById('modalAgendamento')).hide();
            loadEvents();
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao salvar agendamento:', error);
        showError('Erro ao salvar agendamento. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}