<?php /* Script dùng chung (TV4) – được gọi ở footer.php và auth_footer.php */ ?>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= asset('js/main.js') ?>"></script>
  <?php if (!empty($data['use_map'])): /* Bản đồ Leaflet.js – TV1 (Milestone 3) */ ?>
  <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="<?= asset('js/wi-map.js') ?>"></script>
  <?php endif; ?>
</body>
</html>
