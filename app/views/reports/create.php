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
      <form method="post" action="<?= url('report/store') ?>" enctype="multipart/form-data" class="needs-validation" id="reportForm" novalidate>
        <!-- Tiêu đề -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Tiêu đề sự cố <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control <?= !empty($data['errors']['title']) ? 'is-invalid' : '' ?>"
                 placeholder="VD: Cá chết nổi trắng kênh, khói đen mù mịt từ xưởng..."
                 value="<?= e($data['old']['title'] ?? '') ?>" required minlength="5" maxlength="200">
          <?php if (!empty($data['errors']['title'])): ?>
            <div class="invalid-feedback"><?= e($data['errors']['title']) ?></div>
          <?php endif; ?>
          <div class="invalid-feedback client-feedback">Vui lòng nhập tiêu đề (từ 5 đến 200 ký tự).</div>
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
            <div class="invalid-feedback client-feedback">Vui lòng chọn danh mục sự cố.</div>
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

        <!-- Vị trí: địa chỉ + toạ độ GPS (TV4) -->
        <div class="location-box mb-3">
          <label class="form-label fw-semibold" for="addressInput"><i class="bi bi-geo-alt text-danger me-1"></i>Vị trí hiện trường</label>
          <input type="text" name="address" id="addressInput" class="form-control mb-2" maxlength="500"
                 placeholder="Số nhà, tên đường, phường/xã, tỉnh/thành phố..."
                 value="<?= e($data['old']['address'] ?? '') ?>">
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small text-muted mb-1" for="latInput">Vĩ độ (Latitude)</label>
              <input type="number" step="any" min="-90" max="90" name="latitude" id="latInput" class="form-control form-control-sm"
                     placeholder="VD: 10.7765" value="<?= e($data['old']['latitude'] ?? '') ?>">
              <div class="invalid-feedback client-feedback">Vĩ độ từ -90 đến 90, nhập đủ cả kinh độ.</div>
            </div>
            <div class="col-6">
              <label class="form-label small text-muted mb-1" for="lngInput">Kinh độ (Longitude)</label>
              <input type="number" step="any" min="-180" max="180" name="longitude" id="lngInput" class="form-control form-control-sm"
                     placeholder="VD: 106.7009" value="<?= e($data['old']['longitude'] ?? '') ?>">
              <div class="invalid-feedback client-feedback">Kinh độ từ -180 đến 180, nhập đủ cả vĩ độ.</div>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-outline-primary btn-sm btn-gps" id="btnGetLocation">
              <i class="bi bi-geo-alt-fill me-1"></i> Lấy tọa độ GPS tự động
            </button>
            <a href="#" id="mapPreview" class="small d-none" target="_blank" rel="noopener"><i class="bi bi-map me-1"></i>Xem trên Google Maps</a>
            <span id="gpsStatus" class="gps-status text-muted" aria-live="polite"></span>
          </div>
        </div>

        <!-- Mô tả -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Mô tả chi tiết <span class="text-danger">*</span></label>
          <textarea name="description" rows="4" class="form-control <?= !empty($data['errors']['description']) ? 'is-invalid' : '' ?>"
                    placeholder="Mô tả nguyên nhân, hiện trạng màu sắc, mùi hôi, thời gian bắt đầu xảy ra..." required minlength="10"><?= e($data['old']['description'] ?? '') ?></textarea>
          <?php if (!empty($data['errors']['description'])): ?>
            <div class="invalid-feedback"><?= e($data['errors']['description']) ?></div>
          <?php endif; ?>
          <div class="invalid-feedback client-feedback">Vui lòng mô tả sự cố (ít nhất 10 ký tự).</div>
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

<script src="<?= asset('js/report-form.js') ?>" defer></script>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
