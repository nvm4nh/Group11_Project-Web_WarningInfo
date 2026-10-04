<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $stmt;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        );
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            echo $e->getMessage();
        }
    }
    public function query($sql){ $this->stmt = $this->dbh->prepare($sql); }
    public function execute(){ return $this->stmt->execute(); }
    public function resultSet(){ $this->execute(); return $this->stmt->fetchAll(PDO::FETCH_OBJ); }
    public function single(){ $this->execute(); return $this->stmt->fetch(PDO::FETCH_OBJ); }
}
