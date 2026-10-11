<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container" style="max-width: 640px;">
    <h1 class="section-title mb-4"><?= e($data['title']) ?></h1>

    <?php $item = $data['item']; $errors = $data['errors']; ?>

    <form method="post"
          action="<?= $data['mode'] === 'create'
                    ? url('admin/storeCategory')
                    : url('admin/updateCategory/' . $item->id) ?>">
      <div class="mb-3">
        <label class="form-label">Tên danh mục</label>
        <input type="text" name="name"
               class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
               value="<?= e($item->name ?? '') ?>">
        <?php if (!empty($errors['name'])): ?>
          <div class="invalid-feedback"><?= e($errors['name']) ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Mô tả</label>
        <textarea name="description" rows="3" class="form-control"><?= e($item->description ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary">
        <?= $data['mode'] === 'create' ? 'Thêm mới' : 'Cập nhật' ?>
      </button>
      <a href="<?= url('admin/categories') ?>" class="btn btn-outline-secondary">Huỷ</a>
    </form>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>