<?php
class App {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Controller: /report/... → ReportController. Không có file tương ứng → trang 404 (trước đây lặng lẽ về trang chủ)
        if (isset($url[0]) && $url[0] !== '') {
            $name = ucfirst($url[0]) . 'Controller';
            if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $url[0]) || !file_exists('../app/controllers/' . $name . '.php')) {
                show_404();
            }
            $this->controller = $name;
            unset($url[0]);
        }
        require_once '../app/controllers/' . $this->controller . '.php';

        // 2. Action: chỉ gọi hàm public (không gọi hàm private/protected, static hay __xxx) – sai thì trang 404.
        //    Kiểm tra TRƯỚC khi khởi tạo controller để đường dẫn sai luôn ra 404 (kể cả khi chưa đăng nhập)
        if (isset($url[1]) && $url[1] !== '') {
            if (!$this->isAction($this->controller, $url[1])) {
                show_404();
            }
            $this->method = $url[1];
            unset($url[1]);
        }

        $this->controller = new $this->controller;
        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /** Hàm $method của $class có được phép gọi qua URL không */
    private function isAction($class, $method) {
        if (strpos($method, '__') === 0 || !method_exists($class, $method)) {
            return false;
        }
        $ref = new ReflectionMethod($class, $method);
        return $ref->isPublic() && !$ref->isStatic() && $ref->getDeclaringClass()->getName() !== 'Controller';
    }

    public function parseUrl() {
        if(isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
