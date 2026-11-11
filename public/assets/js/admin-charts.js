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
            backgroundColor: ['#1b6b45', '#2f8fb3', '#c2410c', '#f4b93a', '#8a5a2b', '#7c5fb0', '#b23a48']
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
            backgroundColor: '#2f8a55',
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
            borderColor: '#1b6b45',
            backgroundColor: 'rgba(111, 191, 115, 0.22)',
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
