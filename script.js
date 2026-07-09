document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Logika Hitung Mundur Sisa Magang
    function hitungSisaMagang() {
        const endDate = new Date('2026-10-08T00:00:00');
        const today = new Date(); // Mengambil waktu sistem saat ini
        
        const diffTime = endDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
        
        const displayDays = diffDays > 0 ? diffDays : 0;
        document.getElementById('remainingDays').innerHTML = displayDays + ' <span class="text-lg font-medium text-rose-100">Days</span>';
    }
    
    hitungSisaMagang();

    // 2. Konfigurasi Grafik (Chart.js)
    const chartCanvas = document.getElementById('attendanceChart');
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        const gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); 
        gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: [100, 95, 98, 100], // Data visual dummy
                    borderColor: '#3b82f6',
                    backgroundColor: gradientBlue,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 80,
                        max: 100,
                        grid: { borderDash: [5, 5], color: '#f3f4f6' },
                        ticks: { stepSize: 5 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});