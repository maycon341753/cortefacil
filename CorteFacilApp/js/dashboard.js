// Função para carregar os dados do dashboard
async function loadDashboardData() {
    try {
        showLoading();
        const response = await fetch('php/get_dashboard_data.php');
        const data = await response.json();

        if (data.status === 'success') {
            // Atualiza os cards de estatísticas
            updateStatCards(data.stats);
            // Atualiza o gráfico de agendamentos
            updateBookingsChart(data.bookings_chart);
            // Atualiza a lista de próximos agendamentos
            updateUpcomingBookings(data.upcoming_bookings);
        } else {
            showError(data.mensagem);
        }
    } catch (error) {
        console.error('Erro ao carregar dados do dashboard:', error);
        showError('Erro ao carregar dados do dashboard. Por favor, tente novamente.');
    } finally {
        hideLoading();
    }
}

// Atualiza os cards de estatísticas
function updateStatCards(stats) {
    document.getElementById('totalAgendamentos').textContent = stats.total_agendamentos;
    document.getElementById('totalServicos').textContent = stats.total_servicos;
    document.getElementById('totalProfissionais').textContent = stats.total_profissionais;
    document.getElementById('faturamentoMensal').textContent = formatCurrency(stats.faturamento_mensal);
}

// Atualiza o gráfico de agendamentos
function updateBookingsChart(chartData) {
    const ctx = document.getElementById('graficoAgendamentos').getContext('2d');
    
    // Destrói o gráfico existente se houver
    if (window.bookingsChart instanceof Chart) {
        window.bookingsChart.destroy();
    }

    window.bookingsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Agendamentos',
                data: chartData.values,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#4e73df',
                pointBorderColor: '#fff',
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#4e73df',
                pointHoverBorderColor: '#fff',
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                },
                y: {
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value) {
                            return value;
                        }
                    },
                    grid: {
                        color: 'rgb(233, 236, 244)',
                        zeroLineColor: 'rgb(233, 236, 244)',
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgb(255, 255, 255)',
                    bodyColor: '#858796',
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    padding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            return 'Agendamentos: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });
}

// Atualiza a lista de próximos agendamentos
function updateUpcomingBookings(bookings) {
    const container = document.getElementById('proximosAgendamentos');
    container.innerHTML = '';

    if (bookings.length === 0) {
        container.innerHTML = '<p class="text-center py-3">Nenhum agendamento próximo.</p>';
        return;
    }

    bookings.forEach(booking => {
        const card = document.createElement('div');
        card.className = 'card mb-3';
        card.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">${booking.cliente_nome}</h6>
                        <p class="card-text small text-muted mb-0">${booking.servico_nome}</p>
                        <p class="card-text small text-muted mb-0">Profissional: ${booking.profissional_nome}</p>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-1">${formatDate(booking.data)}</h6>
                        <p class="small text-muted mb-0">${booking.horario}</p>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

// Função auxiliar para formatar moeda
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Função auxiliar para formatar data
function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(new Date(date));
}