<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Cliente - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .cadastro-container {
            max-width: 500px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            color: #0d6efd;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .form-floating input {
            border-radius: 8px;
        }
        .btn-cadastro {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .alert {
            display: none;
            margin-top: 1rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cadastro-container">
            <div class="logo">
                <h1>CorteFácil</h1>
                <p class="text-muted">Cadastro de Cliente</p>
            </div>
            
            <form id="cadastroForm" method="POST" action="../php/cadastro.php">
                <div class="form-floating">
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" required>
                    <label for="nome">Nome completo</label>
                </div>
                
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="E-mail" required>
                    <label for="email">E-mail</label>
                </div>

                <div class="form-floating">
                    <input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF" required>
                    <label for="cpf">CPF</label>
                </div>

                <div class="form-floating">
                    <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="Telefone" required>
                    <label for="telefone">Telefone</label>
                </div>

                <div class="form-floating">
                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                    <label for="data_nascimento">Data de Nascimento</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                    <label for="senha">Senha</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" placeholder="Confirme a senha" required>
                    <label for="confirma_senha">Confirme a senha</label>
                </div>

                <div class="alert alert-danger" id="errorMessage" role="alert"></div>
                <div class="alert alert-success" id="successMessage" role="alert"></div>
                
                <button type="submit" class="btn btn-primary btn-cadastro">
                    <i class="bi bi-person-plus"></i> Cadastrar
                </button>

                <div class="login-link">
                    <a href="login.php" class="text-decoration-none">Já tem uma conta? Faça login</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script>
        // Máscara para CPF
        IMask(document.getElementById('cpf'), {
            mask: '000.000.000-00'
        });

        // Máscara para telefone
        IMask(document.getElementById('telefone'), {
            mask: '(00) 00000-0000'
        });

        document.getElementById('cadastroForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const form = e.target;
            
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';

            // Validar senha e confirmação
            const senha = document.getElementById('senha').value;
            const confirma_senha = document.getElementById('confirma_senha').value;

            if (senha !== confirma_senha) {
                errorMessage.textContent = 'As senhas não coincidem';
                errorMessage.style.display = 'block';
                return;
            }
            
            try {
                const formData = new FormData(form);
                formData.delete('confirma_senha'); // Remove confirmação de senha antes de enviar
                
                const formDataObject = Object.fromEntries(formData);
                console.log('Dados a serem enviados:', formDataObject);
                
                const response = await fetch('../php/cadastro.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formDataObject)
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    successMessage.textContent = data.mensagem;
                    successMessage.style.display = 'block';
                    form.reset();
                    
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    errorMessage.textContent = data.mensagem;
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Erro:', error);
                errorMessage.textContent = 'Erro ao realizar cadastro. Por favor, tente novamente.';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>