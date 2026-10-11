<?php /* Trang chủ – cảnh báo mới nhất (lọc theo mức độ) – BẢN TĨNH (dữ liệu mẫu) */ ?>
<!-- ========== CẢNH BÁO MỚI NHẤT ==========
     Hiện tại lọc bằng link ?severity=...  TV5 có thể đổi sang AJAX (data-severity) -->
<section class="section bg-white border-top border-bottom" id="latest">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <span class="section-eyebrow"><i class="bi bi-broadcast"></i> Cập nhật liên tục</span>
        <h2 class="section-title">Cảnh báo mới nhất</h2>
      </div>
      <ul class="nav nav-pills gap-1 flex-nowrap overflow-x-auto pb-1 mw-100" id="latestTabs">
        <li class="nav-item"><a class="nav-link active" data-severity="" href="<?= url('') ?>#latest">Tất cả</a></li>
        <li class="nav-item"><a class="nav-link" data-severity="critical" href="<?= url('?severity=critical') ?>#latest">Nghiêm trọng</a></li>
        <li class="nav-item"><a class="nav-link" data-severity="high" href="<?= url('?severity=high') ?>#latest">Cao</a></li>
        <li class="nav-item"><a class="nav-link" data-severity="medium" href="<?= url('?severity=medium') ?>#latest">Trung bình</a></li>
        <li class="nav-item"><a class="nav-link" data-severity="low" href="<?= url('?severity=low') ?>#latest">Thấp</a></li>
      </ul>
    </div>
    <div class="row g-4" id="latestAlerts">
        <div class="col-md-6 col-lg-4">
          <article class="alert-card" data-id="1842">
            <div class="thumb">
              <img src="<?= asset('img/placeholder/water.svg') ?>" alt="Nước kênh chuyển màu đen, bốc mùi hôi, cá chết nổi hàng loạt" loading="lazy">
              <span class="badge-severity sev-high"><i class="bi bi-exclamation-triangle-fill"></i> Cao</span>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="category"><i class="bi bi-droplet-half"></i> Ô nhiễm nguồn nước</span>
                <span class="badge-status st-processing">Đang xử lý</span>
              </div>
              <h3 class="title"><a href="<?= url('report/detail/1842') ?>">Nước kênh chuyển màu đen, bốc mùi hôi, cá chết nổi hàng loạt</a></h3>
              <p class="excerpt">Từ sáng sớm, đoạn kênh dài khoảng 500m xuất hiện nhiều cá chết, nước đen đặc và mùi hôi nồng nặc lan vào khu dân cư.</p>
              <div class="meta"><span><i class="bi bi-geo-alt"></i>Kênh Nhiêu Lộc, Phường Sài Gòn, TP. Hồ Chí Minh</span></div>
              <div class="card-foot">
                <span><i class="bi bi-clock me-1"></i>2 giờ trước</span>
                <span class="stats">
                  <span title="Lượt xác nhận"><i class="bi bi-hand-thumbs-up"></i> 128</span>
                  <span title="Bình luận"><i class="bi bi-chat-dots"></i> 24</span>
                </span>
              </div>
            </div>
          </article>
        </div>
        <div class="col-md-6 col-lg-4">
          <article class="alert-card" data-id="1841">
            <div class="thumb">
              <img src="<?= asset('img/placeholder/forest.svg') ?>" alt="Cháy rừng thông lan rộng, khói bao phủ khu dân cư lân cận" loading="lazy">
              <span class="badge-severity sev-critical"><i class="bi bi-exclamation-octagon-fill"></i> Nghiêm trọng</span>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="category"><i class="bi bi-tree"></i> Cháy rừng</span>
                <span class="badge-status st-processing">Đang xử lý</span>
              </div>
              <h3 class="title"><a href="<?= url('report/detail/1841') ?>">Cháy rừng thông lan rộng, khói bao phủ khu dân cư lân cận</a></h3>
              <p class="excerpt">Đám cháy bùng phát từ chiều qua trên sườn đồi, gió mạnh khiến lửa lan nhanh. Lực lượng chức năng đang khống chế.</p>
              <div class="meta"><span><i class="bi bi-geo-alt"></i>Phường Lâm Viên - Đà Lạt, Lâm Đồng</span></div>
              <div class="card-foot">
                <span><i class="bi bi-clock me-1"></i>3 giờ trước</span>
                <span class="stats">
                  <span title="Lượt xác nhận"><i class="bi bi-hand-thumbs-up"></i> 342</span>
                  <span title="Bình luận"><i class="bi bi-chat-dots"></i> 57</span>
                </span>
              </div>
            </div>
          </article>
        </div>
        <div class="col-md-6 col-lg-4">
          <article class="alert-card" data-id="1839">
            <div class="thumb">
              <img src="<?= asset('img/placeholder/air.svg') ?>" alt="Khói đen dày đặc từ ống khói nhà máy vào ban đêm" loading="lazy">
              <span class="badge-severity sev-critical"><i class="bi bi-exclamation-octagon-fill"></i> Nghiêm trọng</span>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="category"><i class="bi bi-cloud-haze2"></i> Ô nhiễm không khí</span>
                <span class="badge-status st-pending">Chờ duyệt</span>
              </div>
              <h3 class="title"><a href="<?= url('report/detail/1839') ?>">Khói đen dày đặc từ ống khói nhà máy vào ban đêm</a></h3>
              <p class="excerpt">Nhiều đêm liền, khói đen thải ra liên tục từ 22h đến 3h sáng, người dân phản ánh khó thở và có mùi khét.</p>
              <div class="meta"><span><i class="bi bi-geo-alt"></i>Phường Hồng Bàng, Hải Phòng</span></div>
              <div class="card-foot">
                <span><i class="bi bi-clock me-1"></i>5 giờ trước</span>
                <span class="stats">
                  <span title="Lượt xác nhận"><i class="bi bi-hand-thumbs-up"></i> 211</span>
                  <span title="Bình luận"><i class="bi bi-chat-dots"></i> 38</span>
                </span>
              </div>
            </div>
          </article>
        </div>
        <div class="col-md-6 col-lg-4">
          <article class="alert-card" data-id="1836">
            <div class="thumb">
              <img src="<?= asset('img/placeholder/chem.svg') ?>" alt="Rò rỉ hoá chất từ xe bồn sau va chạm trên quốc lộ" loading="lazy">
              <span class="badge-severity sev-critical"><i class="bi bi-exclamation-octagon-fill"></i> Nghiêm trọng</span>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="category"><i class="bi bi-exclamation-octagon"></i> Sự cố hoá chất</span>
                <span class="badge-status st-processing">Đang xử lý</span>
              </div>
              <h3 class="title"><a href="<?= url('report/detail/1836') ?>">Rò rỉ hoá chất từ xe bồn sau va chạm trên quốc lộ</a></h3>
              <p class="excerpt">Chất lỏng màu vàng nhạt chảy tràn xuống rãnh thoát nước, có mùi hắc. Khu vực đã được phong toả tạm thời.</p>
              <div class="meta"><span><i class="bi bi-geo-alt"></i>Quốc lộ 1A, Thanh Hóa</span></div>
              <div class="card-foot">
                <span><i class="bi bi-clock me-1"></i>Hôm qua</span>
                <span class="stats">
                  <span title="Lượt xác nhận"><i class="bi bi-hand-thumbs-up"></i> 96</span>
                  <span title="Bình luận"><i class="bi bi-chat-dots"></i> 15</span>
                </span>
              </div>
            </div>
          </article>
        </div>
        <div class="col-md-6 col-lg-4">
          <article class="alert-card" data-id="1833">
            <div class="thumb">
              <img src="<?= asset('img/placeholder/flood.svg') ?>" alt="Sạt lở bờ sông kéo dài 40m, uy hiếp hàng chục hộ dân" loading="lazy">
              <span class="badge-severity sev-high"><i class="bi bi-exclamation-triangle-fill"></i> Cao</span>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="category"><i class="bi bi-cloud-lightning-rain"></i> Ngập úng</span>
                <span class="badge-status st-processing">Đang xử lý</span>
              </div>
              <h3 class="title"><a href="<?= url('report/detail/1833') ?>">Sạt lở bờ sông kéo dài 40m, uy hiếp hàng chục hộ dân</a></h3>
              <p class="excerpt">Vết nứt xuất hiện sát móng nhà, nhiều mảng đất lớn đã sụt xuống sông sau trận mưa lớn kéo dài.</p>
              <div class="meta"><span><i class="bi bi-geo-alt"></i>Phường Ninh Kiều, Cần Thơ</span></div>
              <div class="card-foot">
                <span><i class="bi bi-clock me-1"></i>Hôm qua</span>
                <span class="stats">
                  <span title="Lượt xác nhận"><i class="bi bi-hand-thumbs-up"></i> 154</span>
                  <span title="Bình luận"><i class="bi bi-chat-dots"></i> 22</span>
                </span>
              </div>
            </div>
          </article>
        </div>
        <div class="col-md-6 col-lg-4">
          <article class="alert-card" data-id="1830">
            <div class="thumb">
              <img src="<?= asset('img/placeholder/waste.svg') ?>" alt="Bãi rác tự phát tràn ra lòng đường gần chợ dân sinh" loading="lazy">
              <span class="badge-severity sev-medium"><i class="bi bi-exclamation-circle-fill"></i> Trung bình</span>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="category"><i class="bi bi-trash3"></i> Ô nhiễm rác thải</span>
                <span class="badge-status st-pending">Chờ duyệt</span>
              </div>
              <h3 class="title"><a href="<?= url('report/detail/1830') ?>">Bãi rác tự phát tràn ra lòng đường gần chợ dân sinh</a></h3>
              <p class="excerpt">Rác thải sinh hoạt bị đổ trộm nhiều ngày, bốc mùi và thu hút ruồi nhặng, ảnh hưởng tiểu thương và người qua lại.</p>
              <div class="meta"><span><i class="bi bi-geo-alt"></i>Phường Hải Châu, Đà Nẵng</span></div>
              <div class="card-foot">
                <span><i class="bi bi-clock me-1"></i>2 ngày trước</span>
                <span class="stats">
                  <span title="Lượt xác nhận"><i class="bi bi-hand-thumbs-up"></i> 47</span>
                  <span title="Bình luận"><i class="bi bi-chat-dots"></i> 9</span>
                </span>
              </div>
            </div>
          </article>
        </div>
    </div>
    <div class="text-center mt-5">
      <a href="<?= url('report') ?>" class="btn btn-primary btn-lg px-4">Xem tất cả cảnh báo <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>
