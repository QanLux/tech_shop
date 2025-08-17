<?php
require_once '../includes/functions.php';

// Chỉ cho phép POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Lấy dữ liệu JSON
$input = json_decode(file_get_contents('php://input'), true);
$dark_mode = (bool)($input['dark_mode'] ?? false);

try {
    // Cập nhật dark mode trong database
    executeQuery(
        "UPDATE users SET dark_mode = ? WHERE id = ?",
        [$dark_mode ? 1 : 0, $_SESSION['user_id']]
    );
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã cập nhật chế độ giao diện'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
