// Função para carregar a lista de profissionais
async function loadProfissionais() {
    try {
        showLoading();
        const response = await fetch('php/listar_profissionais.php');
        const data = await response.json();

        if (data.status === 'sucesso') {
            updateProfissionaisTable(data.profissionais);
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao carregar profissionais:', error);
        showError('Erro ao carregar profissionais. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Atualiza a tabela de profissionais
function updateProfissionaisTable(profissionais) {
    const tbody = document.querySelector('#tabelaProfissionais tbody');
    tbody.innerHTML = '';

    if (profissionais.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">Nenhum profissional cadastrado.</td>
            </tr>
        `;
        return;
    }

    profissionais.forEach(profissional => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <img src="${profissional.foto || 'img/default-avatar.png'}" 
                         alt="${profissional.nome}" 
                         class="rounded-circle me-2" 
                         width="40" 
                         height="40">
                    <div>
                        <h6 class="mb-0">${profissional.nome}</h6>
                        <small class="text-muted">${profissional.especialidade}</small>
                    </div>
                </div>
            </td>
            <td>${formatTelefone(profissional.telefone)}</td>
            <td>${profissional.status ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'}</td>
            <td>
                <button class="btn btn-sm btn-primary me-2" onclick="abrirModalProfissional(${profissional.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="excluirProfissional(${profissional.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Função para formatar telefone
function formatTelefone(telefone) {
    if (!telefone) return '';
    const cleaned = telefone.replace(/\D/g, '');
    const match = cleaned.match(/^(\d{2})(\d{4,5})(\d{4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return telefone;
}

// Inicializa os eventos da página de profissionais
document.addEventListener('DOMContentLoaded', function() {
    // Configura o botão de adicionar profissional
    const btnAddProfissional = document.getElementById('btnAddProfissional');
    if (btnAddProfissional) {
        btnAddProfissional.addEventListener('click', () => abrirModalProfissional());
    }

    // Configura a máscara para o campo de telefone
    const campoTelefone = document.getElementById('telefone');
    if (campoTelefone) {
        IMask(campoTelefone, {
            mask: '(00) 00000-0000'
        });
    }

    // Configura o upload de foto
    const inputFoto = document.getElementById('foto');
    const previewFoto = document.getElementById('previewFoto');
    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFoto.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Carrega a lista inicial de profissionais
    loadProfissionais();
});