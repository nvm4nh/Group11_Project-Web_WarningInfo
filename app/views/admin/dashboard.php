<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container">
    <!-- Nút xuất CSV -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="section-title mb-0">Bảng điều khiển Admin</h1>
      <a href="<?= url('admin/export_csv') ?>" class="btn btn-success shadow-sm">
        <i class="bi bi-file-earmark-excel"></i> Xuất báo cáo CSV
      </a>
    </div>

    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>

    <!-- 4 THẺ THỐNG KÊ TỔNG QUAN -->
    <div class="row g-3">
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-danger">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Tổng sự cố</h6>
            <p class="display-6 fw-bold mb-2"><?= (int)($data['totalReports'] ?? 0) ?></p>
            <a href="<?= url('admin/reports') ?>" class="btn btn-sm btn-outline-danger">Xem danh sách</a>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Đang xử lý</h6>
            <p class="display-6 fw-bold mb-2 text-primary"><?= (int)($data['processing'] ?? 0) ?></p>
            <a href="<?= url('admin/reports') ?>" class="btn btn-sm btn-outline-primary">Xem tiến trình</a>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-success">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Danh mục sự cố</h6>
            <p class="display-6 fw-bold mb-2"><?= (int)$data['totalCategories'] ?></p>
            <a href="<?= url('admin/categories') ?>" class="btn btn-sm btn-outline-success">Quản lý danh mục</a>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-info">
          <div class="card-body">
            <h6 class="card-subtitle text-muted mb-1">Người dùng & Cán bộ</h6>
            <p class="display-6 fw-bold mb-2"><?= (int)$data['totalUsers'] ?></p>
            <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-outline-info">Quản lý tài khoản</a>
          </div>
        </div>
      </div>
    </div>

    <!-- KHU VỰC BIỂU ĐỒ -->
    <div class="row mt-4">
      <!-- Biểu đồ tròn: Danh mục -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title text-center mb-3">Sự cố theo Danh mục</h5>
            <div style="position: relative; height: 300px; width: 100%;">
              <canvas id="categoryChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <!-- Biểu đồ cột: Trạng thái -->
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title text-center mb-3">Sự cố theo Trạng thái</h5>
            <div style="position: relative; height: 300px; width: 100%;">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Biểu đồ đường: Xu hướng theo tháng -->
      <div class="col-12 mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title text-center mb-3">Xu hướng sự cố theo Tháng</h5>
            <div style="position: relative; height: 300px; width: 100%;">
              <canvas id="monthChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Thư viện Chart.js và Script AJAX -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= url('admin/api_statistics') ?>')
      .then(response => response.json())
      .then(res => {
        if (res.status === 'success') {
          // 1. Biểu đồ tròn (Danh mục)
          new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
              labels: res.data.categories.map(item => item.name),
              datasets: [{
                data: res.data.categories.map(item => item.total),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69']
              }]
            },
            options: {
              maintainAspectRatio: false,
              plugins: { legend: { position: 'bottom' } }
            }
          });

          // 2. Biểu đồ cột (Trạng thái)
          new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
              labels: res.data.statuses.map(item => item.status),
              datasets: [{
                label: 'Số lượng',
                data: res.data.statuses.map(item => item.total),
                backgroundColor: '#4e73df',
                borderRadius: 4
              }]
            },
            options: {
              maintainAspectRatio: false,
              scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
              plugins: { legend: { display: false } }
            }
          });

          // 3. Biểu đồ đường (Xu hướng theo tháng)
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
            options: {
              maintainAspectRatio: false,
              scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
          });
        }
      })
      .catch(err => console.error('Lỗi API biểu đồ:', err));
  });
</script>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>