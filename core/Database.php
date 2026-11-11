<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $stmt;
    private static $shared = null;   // kết nối PDO dùng chung trong 1 request

    public function __construct() {
        // Mỗi request chỉ mở 1 kết nối dùng chung cho mọi Model (trước đây mỗi Model 1 kết nối riêng:
        // trang Admin mở 4 kết nối/lượt xem, dễ chạm giới hạn max_user_connections của shared hosting)
        if (self::$shared instanceof PDO) {
            $this->dbh = self::$shared;
            return;
        }
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_PERSISTENT => false,   // shared hosting giới hạn số kết nối: không giữ kết nối thường trực
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Giờ Việt Nam cho NOW()/CURRENT_TIMESTAMP: MySQL trên hosting thường chạy UTC, nếu không đặt thì
            // created_at (MySQL) và incident_time (PHP) lệch nhau 7 tiếng
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '+07:00'"
        );
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            // Không in thẳng lỗi ra trang (lộ thông tin máy chủ); ném lỗi để public/index.php hiện trang 500 và ghi log
            throw new RuntimeException('Không kết nối được cơ sở dữ liệu: ' . $e->getMessage(), 0, $e);
        }
        self::$shared = $this->dbh;
    }

    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):  $type = PDO::PARAM_INT;  break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default:              $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // ID tự tăng của bản ghi vừa INSERT (dùng sau execute())
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}