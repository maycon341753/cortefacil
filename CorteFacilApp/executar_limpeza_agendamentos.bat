@echo off
REM Script para executar limpeza automática de agendamentos
REM Este arquivo deve ser configurado no Agendador de Tarefas do Windows para executar diariamente

echo [%date% %time%] Iniciando limpeza automatica de agendamentos...

REM Executar o script PHP de limpeza
C:\xampp\php\php.exe "C:\xampp\htdocs\cortefacil\CorteFacilApp\php\limpeza_agendamentos_automatica.php"

echo [%date% %time%] Limpeza automatica concluida.

REM Log da execução
echo [%date% %time%] Limpeza automatica executada >> "C:\xampp\htdocs\cortefacil\CorteFacilApp\logs\limpeza_execucoes.log"