// Função para carregar as configurações
async function loadConfiguracoes() {
    try {
        showLoading();
        const response = await fetch('php/get_configuracoes.php');
        const data = await response.json();

        if (data.status === 'sucesso') {
            preencherFormulario(data.configuracoes);
            initializeHorariosFuncionamento();
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao carregar configurações:', error);
        showError('Erro ao carregar configurações. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Preenche o formulário com as configurações
function preencherFormulario(config) {
    // Informações do salão
    document.getElementById('nome_salao').value = config.nome_salao || '';
    document.getElementById('cnpj').value = config.cnpj || '';
    document.getElementById('telefone').value = config.telefone || '';
    document.getElementById('email').value = config.email || '';
    document.getElementById('endereco').value = config.endereco || '';

    // Horários de funcionamento
    config.horarios_funcionamento.forEach(horario => {
        document.querySelector(`input[name="dia_${horario.dia}"]`).checked = horario.aberto;
        document.querySelector(`input[name="abertura_${horario.dia}"]`).value = horario.horario_abertura;
        document.querySelector(`input[name="fechamento_${horario.dia}"]`).value = horario.horario_fechamento;
    });

    // Preferências de agendamento
    document.getElementById('intervalo_agendamento').value = config.intervalo_agendamento || '30';
    document.getElementById('antecedencia_minima').value = config.antecedencia_minima || '1';
    document.getElementById('antecedencia_maxima').value = config.antecedencia_maxima || '30';
    document.getElementById('permitir_reagendamento').checked = config.permitir_reagendamento || false;
    document.getElementById('notificacoes_email').checked = config.notificacoes_email || false;
    document.getElementById('notificacoes_sms').checked = config.notificacoes_sms || false;
}

// Inicializa os campos de horários de funcionamento
function initializeHorariosFuncionamento() {
    const diasSemana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];

    diasSemana.forEach(dia => {
        const checkbox = document.querySelector(`input[name="dia_${dia}"]`);
        const camposHorario = document.querySelectorAll(`input[name^="${dia}_"]`);

        if (checkbox) {
            checkbox.addEventListener('change', function() {
                camposHorario.forEach(campo => {
                    campo.disabled = !this.checked;
                });
            });

            // Dispara o evento change para configurar o estado inicial
            checkbox.dispatchEvent(new Event('change'));
        }
    });
}

// Salva as configurações
async function salvarConfiguracoes(formData) {
    try {
        showLoading();
        const response = await fetch('php/salvar_configuracoes.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'sucesso') {
            showSuccess(data.mensagem);
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao salvar configurações:', error);
        showError('Erro ao salvar configurações. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Altera a senha
async function alterarSenha(formData) {
    try {
        showLoading();
        const response = await fetch('php/alterar_senha.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'sucesso') {
            showSuccess(data.mensagem);
            document.getElementById('formAlterarSenha').reset();
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
    const cnpjInput = document.getElementById('cnpj');
    if (cnpjInput) {
        IMask(cnpjInput, {
            mask: '00.000.000/0000-00'
        });
    }

    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        IMask(telefoneInput, {
            mask: '(00) 00000-0000'
        });
    }

    // Configura o formulário de configurações gerais
    const formConfiguracoes = document.getElementById('formConfiguracoes');
    if (formConfiguracoes) {
        formConfiguracoes.addEventListener('submit', function(e) {
            e.preventDefault();
            salvarConfiguracoes(new FormData(this));
        });
    }

    // Configura o formulário de alteração de senha
    const formAlterarSenha = document.getElementById('formAlterarSenha');
    if (formAlterarSenha) {
        formAlterarSenha.addEventListener('submit', function(e) {
            e.preventDefault();
            alterarSenha(new FormData(this));
        });
    }

    // Carrega as configurações iniciais
    loadConfiguracoes();
});