# Funcionalidade: Alterar Senha do Parceiro

## Implementação Realizada

Foi implementada a funcionalidade para o parceiro trocar a senha ao clicar no nome do salão/usuário no painel administrativo.

## Arquivos Modificados

### 1. painel.php
- **Modificação no nome do usuário**: Adicionado cursor pointer, sublinhado e evento para abrir modal
- **Modal de alteração de senha**: Criado modal completo com formulário
- **JavaScript**: Implementada validação e envio AJAX para alterar senha
- **Função togglePassword**: Permite mostrar/ocultar senhas nos campos

### 2. php/parceiro_alterar_senha.php
- **Atualização da autenticação**: Ajustado para funcionar com a estrutura de sessão atual
- **Validações**: Adicionadas validações de tamanho mínimo da senha
- **Estrutura PDO**: Convertido para usar PDO em vez de mysqli

## Como Funciona

1. **Acesso**: O parceiro acessa o painel através do arquivo `painel.php`
2. **Clique no nome**: No canto superior direito, o nome do usuário aparece sublinhado e com cursor pointer
3. **Modal**: Ao clicar no nome, abre um modal com formulário de alteração de senha
4. **Campos do formulário**:
   - Senha atual (obrigatória)
   - Nova senha (mínimo 6 caracteres)
   - Confirmar nova senha
5. **Validações**:
   - Verificação se nova senha e confirmação coincidem
   - Verificação de tamanho mínimo (6 caracteres)
   - Verificação se senha atual está correta
6. **Feedback**: Mensagens de sucesso ou erro usando SweetAlert2

## Recursos Implementados

- ✅ Modal responsivo com Bootstrap 5
- ✅ Validação client-side e server-side
- ✅ Botões para mostrar/ocultar senhas
- ✅ Feedback visual com SweetAlert2
- ✅ Segurança com hash de senhas
- ✅ Verificação de autenticação
- ✅ Interface intuitiva

## Teste da Funcionalidade

Para testar a funcionalidade:

1. Acesse: `http://localhost/cortefacil/CorteFacilApp/teste_alterar_senha_parceiro.php`
2. Clique em "Ir para o Painel"
3. No painel, clique no nome do usuário (canto superior direito)
4. Teste a alteração de senha usando:
   - **Senha atual**: 123456
   - **Nova senha**: qualquer senha com 6+ caracteres

## Credenciais de Teste

Conforme arquivo `credenciais_saloes.txt`, você pode usar qualquer um dos CPFs listados com a senha padrão `123456` para fazer login e testar a funcionalidade.

## Segurança

- Senhas são criptografadas com `password_hash()`
- Verificação de autenticação antes de permitir alteração
- Validação da senha atual antes de alterar
- Sanitização de dados de entrada