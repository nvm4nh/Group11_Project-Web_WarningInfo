<?php require APPROOT . '/app/views/layouts/header.php'; ?>
<link href="<?= asset('css/report-form.css') ?>" rel="stylesheet">

<section class="section py-4 bg-light border-bottom">
  <div class="container" style="max-width: 800px;">
    <h1 class="h3 fw-bold mb-1"><i class="bi bi-megaphone-fill text-warning me-2"></i>Gửi Báo Cáo Sự Cố Môi Trường</h1>
    <p class="text-muted mb-0">Hãy cung cấp thông tin chính xác để cơ quan chức năng và cộng đồng kịp thời nắm bắt và xử lý.</p>
  </div>
</section>

<section class="section py-5">
  <div class="container" style="max-width: 800px;">
    <?php if (!empty($data['errors']['general'])): ?>
      <div class="alert alert-danger"><?= e($data['errors']['general']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm p-4">
      <form method="post" action="<?= url('report/store') ?>" enctype="multipart/form-data" novalidate>
        <!-- Tiêu đề -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Tiêu đề sự cố <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control <?= !empty($data['errors']['title']) ? 'is-invalid' : '' ?>" 
                 placeholder="VD: Cá chết nổi trắng kênh, khói đen mù mịt từ xưởng..." 
                 value="<?= e($data['old']['title'] ?? '') ?>" required>
          <?php if (!empty($data['errors']['title'])): ?>
            <div class="invalid-feedback"><?= e($data['errors']['title']) ?></div>
          <?php endif; ?>
        </div>

        <div class="row g-3 mb-3">
          <!-- Danh mục -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Danh mục sự cố <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select <?= !empty($data['errors']['category_id']) ? 'is-invalid' : '' ?>" required>
              <option value="">-- Chọn danh mục --</option>
              <?php foreach ($data['categories'] as $c): ?>
                <option value="<?= $c->id ?>" <?= ($data['old']['category_id'] ?? '') == $c->id ? 'selected' : '' ?>>
                  <?= e($c->name) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($data['errors']['category_id'])): ?>
              <div class="invalid-feedback"><?= e($data['errors']['category_id']) ?></div>
            <?php endif; ?>
          </div>

          <!-- Mức độ -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Mức độ khẩn cấp</label>
            <select name="priority" class="form-select">
              <option value="medium" selected>Trung bình (Ảnh hưởng cục bộ)</option>
              <option value="low">Thấp (Nhẹ, chưa lan rộng)</option>
              <option value="high">Cao (Gây ô nhiễm nặng)</option>
              <option value="critical">Nghiêm trọng (Nguy cơ cháy nổ, ngộ độc khẩn cấp)</option>
            </select>
          </div>
        </div>

        <!-- Địa chỉ & Toạ độ -->
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label fw-semibold mb-0">Địa chỉ cụ thể / Hiện trường</label>
            <button type="button" class="btn btn-outline-secondary btn-sm py-0" id="btnGetLocation">
              <i class="bi bi-geo-alt me-1"></i> Lấy toạ độ tự động
            </button>
          </div>
          <input type="text" name="address" class="form-control" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." 
                 value="<?= e($data['old']['address'] ?? '') ?>">
        </div>

        <div class="row g-2 mb-3">
          <div class="col-sm-6">
            <input type="number" step="any" name="latitude" id="latInput" class="form-control form-control-sm text-muted" 
                   placeholder="Vĩ độ (Latitude)" value="<?= e($data['old']['latitude'] ?? '') ?>">
          </div>
          <div class="col-sm-6">
            <input type="number" step="any" name="longitude" id="lngInput" class="form-control form-control-sm text-muted" 
                   placeholder="Kinh độ (Longitude)" value="<?= e($data['old']['longitude'] ?? '') ?>">
          </div>
        </div>

        <!-- Mô tả -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Mô tả chi tiết <span class="text-danger">*</span></label>
          <textarea name="description" rows="4" class="form-control <?= !empty($data['errors']['description']) ? 'is-invalid' : '' ?>"
                    placeholder="Mô tả nguyên nhân, hiện trạng màu sắc, mùi hôi, thời gian bắt đầu xảy ra..." required><?= e($data['old']['description'] ?? '') ?></textarea>
          <?php if (!empty($data['errors']['description'])): ?>
            <div class="invalid-feedback"><?= e($data['errors']['description']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Ảnh minh chứng (TV4): xem trước ảnh trước khi gửi. Phần lưu file do TV3 xử lý ở ReportController::store() -->
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label fw-semibold mb-0" for="reportImages">Ảnh minh chứng</label>
            <small class="text-muted" id="imageCount">0/3 ảnh</small>
          </div>
          <div class="upload-zone <?= !empty($data['errors']['images']) ? 'is-invalid' : '' ?>" id="uploadZone">
            <input type="file" name="images[]" id="reportImages" accept="image/jpeg,image/png,image/webp" multiple
                   data-max-files="3" data-max-size="5" aria-describedby="uploadHelp">
            <i class="bi bi-cloud-arrow-up"></i>
            <p class="mb-1 fw-semibold">Kéo thả ảnh vào đây hoặc <span class="text-primary">chọn từ máy / chụp ảnh</span></p>
            <small class="text-muted" id="uploadHelp">Tối đa 3 ảnh · JPG, PNG, WEBP · mỗi ảnh không quá 5MB</small>
          </div>
          <div class="text-danger small mt-1 <?= empty($data['errors']['images']) ? 'd-none' : '' ?>" id="uploadError"><?= e($data['errors']['images'] ?? '') ?></div>
          <div class="preview-grid" id="previewGrid"></div>
        </div>

        <!-- Thông tin liên hệ -->
        <h6 class="fw-bold mt-4 pt-3 border-top mb-3 text-secondary">Thông tin người báo cáo (bảo mật):</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label small">Họ và tên</label>
            <input type="text" name="contact_name" class="form-control" 
                   value="<?= e($data['old']['contact_name'] ?? ($_SESSION['full_name'] ?? '')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small">Số điện thoại</label>
            <input type="text" name="contact_phone" class="form-control" 
                   value="<?= e($data['old']['contact_phone'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small">Email</label>
            <input type="email" name="contact_email" class="form-control" 
                   value="<?= e($data['old']['contact_email'] ?? ($_SESSION['email'] ?? '')) ?>">
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <a href="<?= url('report') ?>" class="btn btn-outline-secondary">Hủy bỏ</a>
          <button type="submit" class="btn btn-warning fw-semibold px-4"><i class="bi bi-send me-1"></i> Gửi báo cáo ngay</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
document.getElementById('btnGetLocation')?.addEventListener('click', function() {
  if (navigator.geolocation) {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang định vị...';
    navigator.geolocation.getCurrentPosition(
      pos => {
        document.getElementById('latInput').value = pos.coords.latitude.toFixed(7);
        document.getElementById('lngInput').value = pos.coords.longitude.toFixed(7);
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> Đã lấy toạ độ';
      },
      err => {
        alert('Không thể lấy toạ độ: ' + err.message);
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Lấy toạ độ tự động';
      }
    );
  } else {
    alert('Trình duyệt không hỗ trợ Geolocation.');
  }
});
</script>

<script src="<?= asset('js/report-form.js') ?>" defer></script>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
