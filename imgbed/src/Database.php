<?php
namespace ImgBed;

class Database {
    private static $instance = null;
    private $conn;
    private $prefix;

    private function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $dbConfig = $config['db'];
        
        $this->prefix = $dbConfig['prefix'];
        
        try {
            $this->conn = new \PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                $dbConfig['username'],
                $dbConfig['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }

    // 单例模式获取实例
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // 获取数据库连接
    public function getConnection() {
        return $this->conn;
    }
    
    // 获取表名（应用前缀）
    public function getTable($table) {
        return $this->prefix . $table;
    }
    
    // 执行查询
    public function query($sql, $params = []) {
        // 替换SQL中的表前缀占位符
        $sql = str_replace('{prefix}', $this->prefix, $sql);
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    // 获取单行
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    // 获取多行
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // 插入数据
    public function insert($table, $data) {
        $table = $this->getTable($table);
        
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ':' . $field;
        }, $fields);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, $data);
        return $this->conn->lastInsertId();
    }
    
    // 更新数据
    public function update($table, $data, $where, $whereParams = []) {
        $table = $this->getTable($table);
        
        $setParts = [];
        foreach ($data as $field => $value) {
            $setParts[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $this->query($sql, $params);
        
        return true;
    }
} 