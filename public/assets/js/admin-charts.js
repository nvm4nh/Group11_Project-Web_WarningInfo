document.addEventListener('DOMContentLoaded', function () {
  const dashboard = document.querySelector('[data-statistics-url]');
  if (!dashboard) return;

  fetch(dashboard.dataset.statisticsUrl)
    .then(response => {
      if (!response.ok) throw new Error('Lỗi mạng hoặc hết phiên đăng nhập');
      return response.json();
    })
    .then(res => {
      if (res.status !== 'success') {
        throw new Error('API không trả về dữ liệu biểu đồ hợp lệ');
      }

      new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
          labels: res.data.categories.map(item => item.name),
          datasets: [{
            data: res.data.categories.map(item => item.total),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69']
          }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
      });

      new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
          labels: res.data.statuses.map(item => item.label),
          datasets: [{
            label: 'Số lượng',
            data: res.data.statuses.map(item => item.total),
            backgroundColor: '#4e73df',
            borderRadius: 4
          }]
        },
        options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
      });

      new Chart(document.getElementById('monthChart'), {
        type: 'line',
        data: {
          labels: res.data.months.map(item => item.month),
          datasets: [{
            label: 'Số lượng sự cố',
            data: res.data.months.map(item => item.total),
            borderColor: '#e74a3b',
            backgroundColor: 'rgba(231, 74, 59, 0.1)',
            fill: true,
            tension: 0.3,
            pointRadius: 5
          }]
        },
        options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
      });
    })
    .catch(err => {
      console.error('Lỗi API biểu đồ:', err);
      document.querySelectorAll('canvas').forEach(canvas => {
        if (canvas.parentElement) {
          canvas.parentElement.innerHTML = '<div class="text-danger text-center py-5"><i class="bi bi-exclamation-triangle fs-1"></i><br>Không thể tải dữ liệu. Vui lòng thử đăng nhập lại.</div>';
        }
      });
    });
});
