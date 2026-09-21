
document.addEventListener('DOMContentLoaded', function() {
    
    if (document.querySelector('.stats-section')) {
        initializeCharts();
    }
});

function initializeCharts() {
    
    createDoctorPatientChart();

    createAppointmentTrendsChart();

    createSpecialtiesChart();
}

function createDoctorPatientChart() {
    const chartContainer = document.getElementById('doctor-patient-chart');
    if (!chartContainer) return;

    const doctorCount = parseInt(document.querySelector('.stat-number.doctors')?.textContent || '0');
    const patientCount = parseInt(document.querySelector('.stat-number.patients')?.textContent || '0');

    chartContainer.innerHTML = '';

    const canvas = document.createElement('canvas');
    canvas.width = 300;
    canvas.height = 300;
    chartContainer.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');

    const total = doctorCount + patientCount;
    const doctorPercent = (doctorCount / total) * 100;
    const patientPercent = (patientCount / total) * 100;

    ctx.beginPath();
    ctx.arc(150, 150, 100, 0, 2 * Math.PI);
    ctx.fillStyle = '#e2e8f0';
    ctx.fill();

    ctx.beginPath();
    ctx.moveTo(150, 150);
    ctx.arc(150, 150, 100, -Math.PI / 2, (-Math.PI / 2) + (2 * Math.PI * doctorPercent / 100));
    ctx.closePath();
    ctx.fillStyle = '#3b82f6';
    ctx.fill();

    ctx.beginPath();
    ctx.moveTo(150, 150);
    ctx.arc(150, 150, 100, (-Math.PI / 2) + (2 * Math.PI * doctorPercent / 100), 
            (-Math.PI / 2) + (2 * Math.PI * (doctorPercent + patientPercent) / 100));
    ctx.closePath();
    ctx.fillStyle = '#10b981';
    ctx.fill();

    ctx.beginPath();
    ctx.arc(150, 150, 40, 0, 2 * Math.PI);
    ctx.fillStyle = 'white';
    ctx.fill();

    const legend = document.createElement('div');
    legend.className = 'chart-legend';
    legend.innerHTML = `
        <div class="legend-item">
            <div class="legend-color" style="background-color: #3b82f6;"></div>
            <div class="legend-text">Doctors: ${doctorCount}</div>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #10b981;"></div>
            <div class="legend-text">Patients: ${patientCount}</div>
        </div>
    `;
    chartContainer.appendChild(legend);
}

function createAppointmentTrendsChart() {
    const chartContainer = document.getElementById('appointment-trends-chart');
    if (!chartContainer) return;

    const data = [120, 190, 150, 220, 180, 250, 200];
    const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    chartContainer.innerHTML = '';

    const canvas = document.createElement('canvas');
    canvas.width = 400;
    canvas.height = 200;
    chartContainer.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');

    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1;

    for (let i = 0; i <= 5; i++) {
        const y = 30 + (i * 30);
        ctx.beginPath();
        ctx.moveTo(50, y);
        ctx.lineTo(380, y);
        ctx.stroke();
    }

    const barWidth = 30;
    const maxValue = Math.max(...data);
    
    for (let i = 0; i < data.length; i++) {
        const x = 60 + (i * 45);
        const barHeight = (data[i] / maxValue) * 150;
        const y = 180 - barHeight;

        ctx.fillStyle = '#3b82f6';
        ctx.fillRect(x, y, barWidth, barHeight);

        ctx.fillStyle = '#64748b';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(labels[i], x + barWidth/2, 195);

        ctx.fillText(data[i], x + barWidth/2, y - 5);
    }

    ctx.fillStyle = '#64748b';
    ctx.font = '14px Arial';
    ctx.textAlign = 'left';
    ctx.fillText('Appointments', 10, 20);
}

function createSpecialtiesChart() {
    const chartContainer = document.getElementById('specialties-chart');
    if (!chartContainer) return;

    const specialties = [
        { name: 'Cardiology', count: 25, color: '#ef4444' },
        { name: 'Neurology', count: 18, color: '#f97316' },
        { name: 'Orthopedics', count: 22, color: '#eab308' },
        { name: 'Pediatrics', count: 30, color: '#22c55e' },
        { name: 'Dermatology', count: 15, color: '#3b82f6' }
    ];

    chartContainer.innerHTML = '';

    const canvas = document.createElement('canvas');
    canvas.width = 350;
    canvas.height = 250;
    chartContainer.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');

    const barHeight = 30;
    const maxValue = Math.max(...specialties.map(s => s.count));
    const startY = 30;
    
    specialties.forEach((specialty, index) => {
        const barWidth = (specialty.count / maxValue) * 250;
        const y = startY + (index * 40);

        ctx.fillStyle = specialty.color;
        ctx.fillRect(80, y, barWidth, barHeight);

        ctx.fillStyle = '#64748b';
        ctx.font = '14px Arial';
        ctx.textAlign = 'right';
        ctx.fillText(specialty.name, 70, y + 20);

        ctx.textAlign = 'left';
        ctx.fillText(specialty.count, 85 + barWidth, y + 20);
    });

    ctx.fillStyle = '#1e293b';
    ctx.font = '16px Arial';
    ctx.textAlign = 'left';
    ctx.fillText('Doctors by Specialty', 10, 20);
}