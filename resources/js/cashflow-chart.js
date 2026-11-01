import Chart from 'chart.js/auto';

// Warna mengikuti token aplikasi (DESIGN Bab 7.1).
const COLORS = {
    income: '#16A34A',
    expense: '#DC2626',
};

function boot() {
    const canvas = document.getElementById('cashflow-chart');
    const holder = document.getElementById('cashflow-data');

    if (!canvas || !holder) {
        return;
    }

    let data;
    try {
        data = JSON.parse(holder.textContent);
    } catch (error) {
        return;
    }

    const autoType = data.auto_type || 'bar';
    let option = 'auto';
    let chart = null;

    const resolvedType = () => (option === 'auto' ? autoType : option);

    const datasets = () => [
        {
            label: 'Pemasukan',
            data: data.income,
            backgroundColor: COLORS.income,
            borderColor: COLORS.income,
            borderWidth: 2,
            tension: 0.3,
        },
        {
            label: 'Pengeluaran',
            data: data.expense,
            backgroundColor: COLORS.expense,
            borderColor: COLORS.expense,
            borderWidth: 2,
            tension: 0.3,
        },
    ];

    const draw = () => {
        if (chart) {
            chart.destroy();
        }

        chart = new Chart(canvas, {
            type: resolvedType(),
            data: {
                labels: data.labels,
                datasets: datasets(),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    };

    const buttons = document.querySelectorAll('[data-chart-type]');

    const activate = (active) => {
        buttons.forEach((button) => {
            button.classList.remove('bg-primary', 'text-white', 'font-semibold');
            button.classList.add('border', 'border-line', 'text-subtle', 'font-medium');
        });

        active.classList.remove('border', 'border-line', 'text-subtle', 'font-medium');
        active.classList.add('bg-primary', 'text-white', 'font-semibold');
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            option = button.dataset.chartType;
            activate(button);
            draw();
        });
    });

    draw();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}