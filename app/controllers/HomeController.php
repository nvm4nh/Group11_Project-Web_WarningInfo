<?php
class HomeController extends Controller {
    public function index() {
        $data = [
            'title' => 'Trang chủ Hệ thống',
            'description' => 'Khung MVC cơ bản đã thiết lập thành công!'
        ];
        $this->view('home/index', $data);
    }
}
