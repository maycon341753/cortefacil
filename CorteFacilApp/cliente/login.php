<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container{
            display: flex;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
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
        .btn-login {
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
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <h1>CorteFácil</h1>
                <p class="text-muted">Portal do Cliente</p>
            </div>
            
            <form id="loginForm" method="POST" action="../php/login.php">
                <div class="form-floating">
                    <input type="text" class="form-control" id="cpf" name="cpf" placeholder="CPF" required>
                    <label for="cpf">CPF</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                    <label for="senha">Senha</label>
                </div>

                <div class="alert alert-danger" id="errorMessage" role="alert"></div>
                <div class="alert alert-success" id="successMessage" role="alert"></div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>

                <div class="register-link">
                    <a href="cadastro.php" class="text-decoration-none">Ainda não tem conta? Cadastre-se</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const form = e.target;
            
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
            
            try {
                const formData = new FormData(form);
                const formDataObject = Object.fromEntries(formData);
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(formDataObject),
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status === 'success' && data.tipo === 'cliente') {
                    successMessage.textContent = 'Login realizado com sucesso! Redirecionando...';
                    successMessage.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = 'painel.php';
                    }, 1500);
                } else {
                    errorMessage.textContent = data.mensagem || 'Erro ao realizar login';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Erro:', error);
                errorMessage.textContent = 'Erro ao realizar login. Por favor, tente novamente.';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>