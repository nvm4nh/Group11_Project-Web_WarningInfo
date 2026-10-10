<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<section class="section">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="section-title mb-0">Quản lý danh mục</h1>
      <div>
        <a href="<?= url('admin') ?>" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
        <a href="<?= url('admin/createCategory') ?>" class="btn btn-warning btn-sm">+ Thêm danh mục</a>
      </div>
    </div>

    <?php if ($f = getFlash()): ?>
      <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>#</th><th>Tên danh mục</th><th>Mô tả</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($data['categories'] as $c): ?>
          <tr>
            <td><?= (int)$c->id ?></td>
            <td><?= e($c->name) ?></td>
            <td><?= e($c->description) ?></td>
            <td class="text-end">
              <a href="<?= url('admin/editCategory/' . $c->id) ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
              <form method="post" action="<?= url('admin/deleteCategory/' . $c->id) ?>"
                    class="d-inline" onsubmit="return confirm('Xoá danh mục này?');">
                <button class="btn btn-sm btn-danger">Xoá</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>