<?php
class HomeController extends Controller {
    private $reportModel;
    private $categoryModel;

    public function __construct() {
        $this->reportModel   = $this->model('ReportModel');
        $this->categoryModel = $this->model('CategoryModel');
    }

    public function index() {
        $priority = $_GET['severity'] ?? null;
        $priority = is_string($priority) && in_array($priority, ReportModel::PRIORITIES, true) ? $priority : null;
        $stats = $this->reportModel->getStats();
        $latestReports = $this->reportModel->getLatest(6, $priority);
        $categories = $this->categoryModel->getAll();

        $this->view('home/index', [
            'title'          => 'Trang chủ',
            'active'         => 'home',
            'use_map'        => true,   // nạp Leaflet cho khối bản đồ trang chủ
            'stats'          => $stats,
            'latestReports'  => $latestReports,
            'severity'       => $priority,
            'categories'     => $categories,
            // Trước đây khối "Đang diễn ra" và số báo cáo theo danh mục là dữ liệu mẫu cố định (TV4 – Milestone 4)
            'urgentReports'  => $this->reportModel->getUrgentOpen(3),
            'recent24h'      => $this->reportModel->countRecent(24),
            'categoryCounts' => $this->reportModel->countPublicByCategory(),
        ]);
    }
}
