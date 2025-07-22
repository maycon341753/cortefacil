function logar() {
  const cpf = document.getElementById('cpf').value;
  const data = document.getElementById('data_nascimento').value;

  fetch('php/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `cpf=${cpf}&data_nascimento=${data}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      if (data.tipo === 'cliente') window.location.href = 'cliente/painel.html';
      else if (data.tipo === 'salao') window.location.href = 'salao/painel.html';
      else if (data.tipo === 'admin') window.location.href = 'admin/painel.html';
    } else {
      alert('Erro: ' + data.mensagem);
    }
  });
}
