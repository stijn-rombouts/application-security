<div>
    <canvas id="interestChart"></canvas>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function createChart() {
        const canvas = document.getElementById('interestChart');
        if (!canvas) return;

        if (window.myChart) {
            window.myChart.destroy();
        }

        window.myChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: '{{__('pagesresearcher.Intrestfields')}}',
                    data: @json($data),
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', setTimeout(createChart, 100));
    document.addEventListener('livewire:navigated', createChart);
</script>
@endpush
