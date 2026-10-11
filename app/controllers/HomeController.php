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
        $stats = $this->reportModel->getStats();
        $latestReports = $this->reportModel->getLatest(6, $priority);
        $categories = $this->categoryModel->getAll();

        $this->view('home/index', [
            'title'         => 'Trang chủ',
            'active'        => 'home',
            'stats'         => $stats,
            'latestReports' => $latestReports,
            'categories'    => $categories
        ]);
    }
}
