document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-table').forEach((table) => {
        if (table.querySelector('tbody td[colspan]')) {
            return;
        }

        new DataTable(table, {
            responsive: true,
            pageLength: 25,
            order: [],
            language: {
                search: '',
                searchPlaceholder: 'Search'
            }
        });
    });

    document.querySelectorAll('.toast.show').forEach((toast) => {
        setTimeout(() => bootstrap.Toast.getOrCreateInstance(toast).hide(), 3500);
    });

    const chartCanvas = document.getElementById('statusChart');
    if (chartCanvas) {
        const stats = JSON.parse(chartCanvas.dataset.chart || '{}');
        new Chart(chartCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Commented', 'Failed', 'Skipped'],
                datasets: [{
                    data: [
                        Number(stats.pending || 0),
                        Number(stats.commented || 0),
                        Number(stats.failed || 0),
                        Number(stats.skipped || 0)
                    ],
                    backgroundColor: ['#f6c85f', '#65c18c', '#ef6f6c', '#7b8fa1'],
                    borderColor: '#181d23'
                }]
            },
            options: {
                plugins: {
                    legend: {
                        labels: {
                            color: '#d7dee7'
                        }
                    }
                }
            }
        });
    }
});
