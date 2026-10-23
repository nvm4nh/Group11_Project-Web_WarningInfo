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
                    // Đổi mã phiên sau khi đăng nhập (chống chiếm phiên – session fixation)
                    session_regenerate_id(true);
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

    public function profile() {
        requireLogin();
        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!$user) {
            setFlash('danger', 'Không tìm thấy thông tin tài khoản');
            redirect('auth/login');
        }

        $profileErrors = [];
        $passwordErrors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'update_info') {
                $fullName = trim($_POST['full_name'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');

                if ($fullName === '') {
                    $profileErrors['full_name'] = 'Vui lòng nhập họ và tên';
                }

                if (empty($profileErrors)) {
                    $this->userModel->updateProfile($user->id, $fullName, $phone);
                    $_SESSION['full_name'] = $fullName;
                    setFlash('success', 'Cập nhật thông tin cá nhân thành công!');
                    redirect('auth/profile');
                }
            } elseif ($action === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword     = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if ($currentPassword === '') {
                    $passwordErrors['current_password'] = 'Vui lòng nhập mật khẩu hiện tại';
                } elseif (!password_verify($currentPassword, $user->password)) {
                    $passwordErrors['current_password'] = 'Mật khẩu hiện tại không chính xác';
                }

                if (strlen($newPassword) < 6) {
                    $passwordErrors['new_password'] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
                }

                if ($newPassword !== $confirmPassword) {
                    $passwordErrors['confirm_password'] = 'Xác nhận mật khẩu mới không khớp';
                }

                if (empty($passwordErrors)) {
                    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                    $this->userModel->updatePassword($user->id, $hashed);
                    setFlash('success', 'Đổi mật khẩu thành công!');
                    redirect('auth/profile');
                }
            }
        }

        $this->view('auth/profile', [
            'title'          => 'Thông tin cá nhân',
            'user'           => $user,
            'profileErrors'  => $profileErrors,
            'passwordErrors' => $passwordErrors,
        ]);
    }

    public function logout() {
        // Chỉ đăng xuất bằng form POST có CSRF token (link GET từ trang lạ không đăng xuất được người dùng)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('');
        $_SESSION = [];
        session_destroy();
        session_start();
        setFlash('success', 'Đã đăng xuất');
        redirect('auth/login');
    }
}