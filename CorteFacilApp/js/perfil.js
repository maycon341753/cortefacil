// Função para carregar os dados do perfil
async function loadPerfil() {
    try {
        showLoading();
        const response = await fetch('php/get_profile.php');
        const data = await response.json();

        if (data.status === 'sucesso') {
            preencherFormularioPerfil(data.perfil);
        } else {
            showError(data.mensagem);
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        }
    } catch (error) {
        console.error('Erro ao carregar perfil:', error);
        showError('Erro ao carregar perfil. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Preenche o formulário com os dados do perfil
function preencherFormularioPerfil(perfil) {
    document.getElementById('nome').value = perfil.nome || '';
    document.getElementById('email').value = perfil.email || '';
    document.getElementById('telefone').value = perfil.telefone || '';
    document.getElementById('cpf').value = perfil.cpf || '';
    
    // Atualiza a foto do perfil
    const previewFoto = document.getElementById('previewFoto');
    if (previewFoto) {
        previewFoto.src = perfil.foto || 'img/default-avatar.png';
    }
}

// Salva as alterações do perfil
async function salvarPerfil(formData) {
    try {
        showLoading();
        const response = await fetch('php/salvar_perfil.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'sucesso') {
            showSuccess(data.mensagem);
            // Atualiza o nome do usuário no header
            document.getElementById('userName').textContent = formData.get('nome');
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao salvar perfil:', error);
        showError('Erro ao salvar perfil. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Altera a senha do usuário
async function alterarSenhaPerfil(formData) {
    try {
        showLoading();
        const response = await fetch('php/alterar_senha_perfil.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'sucesso') {
            showSuccess(data.mensagem);
            document.getElementById('formAlterarSenhaPerfil').reset();
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao alterar senha:', error);
        showError('Erro ao alterar senha. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Configura máscaras de input
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        IMask(cpfInput, {
            mask: '000.000.000-00'
        });
    }

    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        IMask(telefoneInput, {
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

    // Configura o formulário de perfil
    const formPerfil = document.getElementById('formPerfil');
    if (formPerfil) {
        formPerfil.addEventListener('submit', function(e) {
            e.preventDefault();
            salvarPerfil(new FormData(this));
        });
    }

    // Configura o formulário de alteração de senha
    const formAlterarSenhaPerfil = document.getElementById('formAlterarSenhaPerfil');
    if (formAlterarSenhaPerfil) {
        formAlterarSenhaPerfil.addEventListener('submit', function(e) {
            e.preventDefault();
            alterarSenhaPerfil(new FormData(this));
        });
    }

    // Carrega os dados iniciais do perfil
    loadPerfil();
});