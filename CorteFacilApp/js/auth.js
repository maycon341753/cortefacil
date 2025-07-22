// Função para verificar a autenticação do usuário
async function verificarAutenticacao() {
    try {
        showLoading();
        const response = await fetch('php/verificar_sessao.php');
        const data = await response.json();

        if (!data.logado) {
            window.location.href = 'parceiro_login.html';
            return false;
        }

        // Atualiza o nome do usuário no header
        document.getElementById('userName').textContent = data.nome;

        // Carrega o dashboard por padrão
        loadContent('dashboard');
        return true;
    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        window.location.href = 'parceiro_login.html';
        return false;
    } finally {
        hideLoading();
    }
}

// Função para realizar logout
async function logout() {
    try {
        showLoading();
        const response = await fetch('php/logout.php');
        const data = await response.json();

        if (data.status === 'success') {
            window.location.href = 'parceiro_login.html';
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao realizar logout:', error);
        showError('Erro ao realizar logout. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Configura o botão de logout
    const btnSair = document.getElementById('btnSair');
    if (btnSair) {
        btnSair.addEventListener('click', function(e) {
            e.preventDefault();
            showConfirm('Deseja realmente sair?').then((result) => {
                if (result.isConfirmed) {
                    logout();
                }
            });
        });
    }

    // Configura o toggle da sidebar
    const btnToggleSidebar = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    if (btnToggleSidebar && sidebar) {
        btnToggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }

    // Configura a navegação
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.nav-link');
        if (link && !link.id === 'btnSair') {
            e.preventDefault();
            const section = link.getAttribute('data-section');
            if (section) {
                loadContent(section);
            }
        }
    });

    // Verifica autenticação ao carregar a página
    verificarAutenticacao();
});