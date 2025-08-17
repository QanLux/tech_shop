<?php
/**
 * Cấu hình kết nối database
 * Thay đổi thông tin kết nối theo hosting của bạn
 */

// Thông tin kết nối database
define('DB_HOST', 'localhost');
define('DB_NAME', 'tech_store'); // Thay đổi tên database
define('DB_USER', 'root');      // Thay đổi username
define('DB_PASS', '');      // Thay đổi password

// Tạo kết nối PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

// Hàm helper để thực thi query
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        die("Lỗi thực thi query: " . $e->getMessage());
    }
}

// Hàm helper để lấy một dòng dữ liệu
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

// Hàm helper để lấy nhiều dòng dữ liệu
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}
?>
