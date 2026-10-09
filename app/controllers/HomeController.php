<?php
class HomeController extends Controller {
    // URL: /  – Trang chủ (giao diện tĩnh, dữ liệu sẽ lấy từ CSDL ở Milestone 3)
    public function index() {
        $this->view('home/index', ['title' => 'Trang chủ', 'active' => 'home']);
    }
}
