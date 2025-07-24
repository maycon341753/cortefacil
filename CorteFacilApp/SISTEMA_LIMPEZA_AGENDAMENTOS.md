# 🔄 Sistema de Limpeza Automática de Agendamentos

## ✅ Funcionalidades Implementadas

### 1. **Filtro de Status**
- A tela "Meus Agendamentos" agora mostra **apenas agendamentos confirmados**
- Agendamentos pendentes, cancelados ou realizados não aparecem mais

### 2. **Limpeza Automática (30 dias)**
- Agendamentos são **automaticamente excluídos** após 30 dias da data de criação
- A limpeza acontece **automaticamente** sempre que um cliente acessa "Meus Agendamentos"
- Sistema de log para acompanhar as limpezas realizadas

## 🛠️ Arquivos Criados/Modificados

### Modificado:
- `php/meus_agendamentos.php` - Filtro por status + limpeza automática

### Criados:
- `php/limpeza_agendamentos_automatica.php` - Script de limpeza standalone
- `executar_limpeza_agendamentos.bat` - Script para automação via Windows

## ⚙️ Configuração Opcional - Agendamento Automático

Para executar a limpeza automaticamente todos os dias (recomendado):

### Windows Task Scheduler:

1. **Abrir o Agendador de Tarefas:**
   - Pressione `Win + R`, digite `taskschd.msc` e pressione Enter

2. **Criar Nova Tarefa:**
   - Clique em "Criar Tarefa Básica"
   - Nome: "Limpeza Agendamentos CorteFácil"
   - Descrição: "Remove agendamentos com mais de 30 dias"

3. **Configurar Frequência:**
   - Selecione "Diariamente"
   - Horário sugerido: 02:00 (madrugada)

4. **Configurar Ação:**
   - Selecione "Iniciar um programa"
   - Programa: `C:\xampp\htdocs\cortefacil\CorteFacilApp\executar_limpeza_agendamentos.bat`

5. **Finalizar:**
   - Marque "Abrir a caixa de diálogo Propriedades"
   - Na aba "Geral", marque "Executar estando o usuário conectado ou não"

## 📊 Como Funciona

### Limpeza Automática:
```
Agendamento criado em: 01/01/2024
Data limite (30 dias): 31/01/2024
Status: Será excluído automaticamente após 31/01/2024
```

### Filtro de Status:
```
✅ Confirmado → Aparece em "Meus Agendamentos"
❌ Pendente → Não aparece
❌ Cancelado → Não aparece  
❌ Realizado → Não aparece
```

## 🔍 Logs e Monitoramento

- **Logs automáticos:** `logs/limpeza_agendamentos.log`
- **Logs de execução:** `logs/limpeza_execucoes.log`
- **Tabela de controle:** `logs_limpeza_agendamentos` (criada automaticamente)

## ⚠️ Importante

- A limpeza é **irreversível** - agendamentos excluídos não podem ser recuperados
- O sistema mantém logs detalhados de todas as exclusões
- A limpeza automática garante que o banco de dados não cresça indefinidamente
- Clientes só veem agendamentos confirmados, melhorando a experiência do usuário