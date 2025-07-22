// Função para carregar a lista de serviços
async function loadServicos() {
    try {
        showLoading();
        const response = await fetch('php/listar_servicos.php');
        const data = await response.json();

        if (data.status === 'sucesso') {
            updateServicosTable(data.servicos);
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        showError('Erro ao carregar serviços. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Atualiza a tabela de serviços
function updateServicosTable(servicos) {
    const tbody = document.querySelector('#tabelaServicos tbody');
    tbody.innerHTML = '';

    if (servicos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">Nenhum serviço cadastrado.</td>
            </tr>
        `;
        return;
    }

    servicos.forEach(servico => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${servico.nome}</td>
            <td>${servico.duracao_minutos} minutos</td>
            <td>${formatCurrency(servico.preco)}</td>
            <td>
                <button class="btn btn-sm btn-primary me-2" onclick="abrirModalServico(${servico.id})">
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

// Função para formatar moeda
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Inicializa os eventos da página de serviços
document.addEventListener('DOMContentLoaded', function() {
    // Configura o botão de adicionar serviço
    const btnAddServico = document.getElementById('btnAddServico');
    if (btnAddServico) {
        btnAddServico.addEventListener('click', () => abrirModalServico());
    }

    // Configura a máscara para o campo de preço
    const campoPreco = document.getElementById('preco');
    if (campoPreco) {
        IMask(campoPreco, {
            mask: Number,
            scale: 2,
            thousandsSeparator: '.',
            padFractionalZeros: true,
            normalizeZeros: true,
            radix: ','
        });
    }

    // Carrega a lista inicial de serviços
    loadServicos();
});