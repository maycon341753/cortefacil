// Função para carregar notificações
async function loadNotifications() {
    try {
        const response = await fetch('php/get_notifications.php');
        const data = await response.json();

        if (data.status === 'sucesso') {
            updateNotificationBadge(data.notifications);
            updateNotificationDropdown(data.notifications);
        }
    } catch (error) {
        console.error('Erro ao carregar notificações:', error);
    }
}

// Atualiza o badge de notificações
function updateNotificationBadge(notifications) {
    const unreadCount = notifications.filter(n => !n.lida).length;
    const badge = document.getElementById('notificationBadge');
    
    if (badge) {
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'block' : 'none';
    }
}

// Atualiza o dropdown de notificações
function updateNotificationDropdown(notifications) {
    const container = document.getElementById('notificationDropdown');
    if (!container) return;

    if (notifications.length === 0) {
        container.innerHTML = '<div class="dropdown-item text-center">Nenhuma notificação</div>';
        return;
    }

    container.innerHTML = '';
    notifications.slice(0, 5).forEach(notification => {
        const item = document.createElement('a');
        item.href = '#';
        item.className = `dropdown-item d-flex align-items-center ${notification.lida ? '' : 'bg-light'}`;
        item.onclick = (e) => {
            e.preventDefault();
            handleNotificationClick(notification);
        };

        const icon = getNotificationIcon(notification.tipo);
        
        item.innerHTML = `
            <div class="mr-3">
                <div class="icon-circle bg-${getNotificationColor(notification.tipo)}">
                    <i class="fas ${icon} text-white"></i>
                </div>
            </div>
            <div>
                <div class="small text-gray-500">${formatDateTime(notification.data_criacao)}</div>
                <span class="${notification.lida ? '' : 'font-weight-bold'}">${notification.mensagem}</span>
            </div>
        `;

        container.appendChild(item);
    });

    if (notifications.length > 5) {
        const viewAll = document.createElement('a');
        viewAll.href = '#';
        viewAll.className = 'dropdown-item text-center small text-gray-500';
        viewAll.textContent = 'Ver todas as notificações';
        viewAll.onclick = (e) => {
            e.preventDefault();
            loadContent('notifications');
        };
        container.appendChild(viewAll);
    }
}

// Manipula o clique em uma notificação
async function handleNotificationClick(notification) {
    if (!notification.lida) {
        try {
            const response = await fetch('php/marcar_notificacao_lida.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: notification.id })
            });

            const data = await response.json();
            if (data.status === 'sucesso') {
                loadNotifications(); // Recarrega as notificações
            }
        } catch (error) {
            console.error('Erro ao marcar notificação como lida:', error);
        }
    }

    // Redireciona para a seção apropriada com base no tipo de notificação
    switch (notification.tipo) {
        case 'agendamento':
            loadContent('agenda');
            break;
        case 'servico':
            loadContent('servicos');
            break;
        case 'profissional':
            loadContent('profissionais');
            break;
        default:
            loadContent('dashboard');
    }
}

// Retorna o ícone apropriado para o tipo de notificação
function getNotificationIcon(tipo) {
    const icons = {
        'agendamento': 'fa-calendar-alt',
        'servico': 'fa-cut',
        'profissional': 'fa-user',
        'sistema': 'fa-cog',
        'default': 'fa-bell'
    };
    return icons[tipo] || icons.default;
}

// Retorna a cor apropriada para o tipo de notificação
function getNotificationColor(tipo) {
    const colors = {
        'agendamento': 'primary',
        'servico': 'success',
        'profissional': 'info',
        'sistema': 'warning',
        'default': 'secondary'
    };
    return colors[tipo] || colors.default;
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Carrega as notificações iniciais
    loadNotifications();

    // Configura o recarregamento periódico das notificações
    setInterval(loadNotifications, 60000); // Recarrega a cada minuto
});