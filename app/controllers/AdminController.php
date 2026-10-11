<?php
class AdminController extends Controller {
    private $userModel;
    private $categoryModel;

    public function __construct() {
        requireAdmin();
        $this->userModel     = $this->model('UserModel');
        $this->categoryModel = $this->model('CategoryModel');
    }

    public function index() {
        $this->view('admin/dashboard', [
            'title'           => 'Bảng điều khiển',
            'totalUsers'      => count($this->userModel->getAll()),
            'totalCategories' => count($this->categoryModel->getAll()),
        ]);
    }

    public function users() {
        $this->view('admin/users/index', [
            'title' => 'Quản lý người dùng',
            'users' => $this->userModel->getAll(),
        ]);
    }

    public function updateUser($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/users');
        $role = $_POST['role'] ?? 'user';
        if (!in_array($role, ['user','admin'], true)) $role = 'user';
        $this->userModel->updateRole($id, $role);
        setFlash('success', 'Cập nhật người dùng thành công');
        redirect('admin/users');
    }

    public function deleteUser($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/users');
        if ((int)$id === (int)($_SESSION['user_id'] ?? 0)) {
            setFlash('danger', 'Không thể xoá chính mình');
            redirect('admin/users');
        }
        try {
            $this->userModel->delete($id);
            setFlash('success', 'Đã xoá người dùng');
        } catch (PDOException $e) {
            setFlash('danger', 'Không thể xoá: người dùng còn dữ liệu liên quan');
        }
        redirect('admin/users');
    }

    public function categories() {
        $this->view('admin/categories/index', [
            'title'      => 'Quản lý danh mục',
            'categories' => $this->categoryModel->getAll(),
        ]);
    }

    public function createCategory() {
        $this->view('admin/categories/form', [
            'title'  => 'Thêm danh mục',
            'mode'   => 'create',
            'errors' => [],
            'item'   => null,
        ]);
    }

    public function storeCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin/categories');
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $errors = [];
        if ($name === '')                                $errors['name'] = 'Vui lòng nhập tên danh mục';
        elseif ($this->categoryModel->nameExists($name)) $errors['name'] = 'Tên danh mục đã tồn tại';

        if (empty($errors)) {
            $this->categoryModel->create($name, $desc);
            setFlash('success', 'Thêm danh mục thành công');
            redirect('admin/categories');
        }
        $this->view('admin/categories/form', [
            'title'  => 'Thêm danh mục',
            'mode'   => 'create',
            'errors' => $errors,
            'item'   => (object)['name' => $name, 'description' => $desc],
        ]);
    }

    public function editCategory($id = null) {
        $item = $id ? $this->categoryModel->findById($id) : null;
        if (!$item) {
            setFlash('danger', 'Không tìm thấy danh mục');
            redirect('admin/categories');
        }
        $this->view('admin/categories/form', [
            'title'  => 'Sửa danh mục',
            'mode'   => 'edit',
            'errors' => [],
            'item'   => $item,
        ]);
    }

    public function updateCategory($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/categories');
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $errors = [];
        if ($name === '')                                     $errors['name'] = 'Vui lòng nhập tên danh mục';
        elseif ($this->categoryModel->nameExists($name, $id)) $errors['name'] = 'Tên danh mục đã tồn tại';

        if (empty($errors)) {
            $this->categoryModel->update($id, $name, $desc);
            setFlash('success', 'Cập nhật danh mục thành công');
            redirect('admin/categories');
        }
        $this->view('admin/categories/form', [
            'title'  => 'Sửa danh mục',
            'mode'   => 'edit',
            'errors' => $errors,
            'item'   => (object)['id' => $id, 'name' => $name, 'description' => $desc],
        ]);
    }

    public function deleteCategory($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) redirect('admin/categories');
        try {
            $this->categoryModel->delete($id);
            setFlash('success', 'Đã xoá danh mục');
        } catch (PDOException $e) {
            setFlash('danger', 'Không thể xoá: danh mục đang được sử dụng');
        }
        redirect('admin/categories');
    }
}