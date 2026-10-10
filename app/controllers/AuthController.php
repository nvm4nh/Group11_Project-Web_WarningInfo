<?php
class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('UserModel');
    }

    public function login() {
        if (isLoggedIn()) redirect('');

        $errors = [];
        $email  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '')    $errors['email']    = 'Vui lòng nhập email';
            if ($password === '') $errors['password'] = 'Vui lòng nhập mật khẩu';

            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);
                if ($user && password_verify($password, $user->password)) {
                    $_SESSION['user_id']   = $user->id;
                    $_SESSION['full_name'] = $user->full_name;
                    $_SESSION['email']     = $user->email;
                    $_SESSION['role']      = $user->role;
                    setFlash('success', 'Đăng nhập thành công!');
                    redirect($user->role === 'admin' ? 'admin' : '');
                } else {
                    $errors['general'] = 'Email hoặc mật khẩu không đúng';
                }
            }
        }

        $this->view('auth/login', [
            'title'  => 'Đăng nhập',
            'errors' => $errors,
            'email'  => $email,
        ]);
    }

    public function register() {
        if (isLoggedIn()) redirect('');

        $errors = [];
        $old    = ['full_name' => '', 'email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['full_name'] = trim($_POST['full_name'] ?? '');
            $old['email']     = trim($_POST['email'] ?? '');
            $password         = $_POST['password'] ?? '';
            $confirm          = $_POST['password_confirm'] ?? '';

            if ($old['full_name'] === '')                              $errors['full_name'] = 'Vui lòng nhập họ tên';
            if ($old['email'] === '')                                  $errors['email']     = 'Vui lòng nhập email';
            elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email']     = 'Email không hợp lệ';
            elseif ($this->userModel->emailExists($old['email']))      $errors['email']     = 'Email đã được đăng ký';
            if (strlen($password) < 6)                                 $errors['password']  = 'Mật khẩu tối thiểu 6 ký tự';
            if ($password !== $confirm)                                $errors['password_confirm'] = 'Mật khẩu xác nhận không khớp';

            if (empty($errors)) {
                $ok = $this->userModel->create([
                    'full_name' => $old['full_name'],
                    'email'     => $old['email'],
                    'password'  => password_hash($password, PASSWORD_DEFAULT),
                    'role'      => 'user',
                ]);
                if ($ok) {
                    setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                    redirect('auth/login');
                } else {
                    $errors['general'] = 'Có lỗi xảy ra, vui lòng thử lại';
                }
            }
        }

        $this->view('auth/register', [
            'title'  => 'Đăng ký',
            'errors' => $errors,
            'old'    => $old,
        ]);
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
        session_start();
        setFlash('success', 'Đã đăng xuất');
        redirect('auth/login');
    }
}