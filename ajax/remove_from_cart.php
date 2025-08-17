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
$cart_id = (int)($input['cart_id'] ?? 0);

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // Kiểm tra item trong giỏ hàng có thuộc về user này không
    $cart_item = fetchOne(
        "SELECT * FROM cart WHERE id = ? AND user_id = ?",
        [$cart_id, $_SESSION['user_id']]
    );
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        exit;
    }
    
    // Xóa item khỏi giỏ hàng
    executeQuery(
        "DELETE FROM cart WHERE id = ?",
        [$cart_id]
    );
    
    // Lấy số lượng sản phẩm trong giỏ hàng
    $cart_count = getCartItemCount($_SESSION['user_id']);
    
    // Tính toán lại tổng tiền
    $cart_total = getCartTotal($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
        'cart_count' => $cart_count,
        'cart_total' => $cart_total,
        'formatted_total' => formatPrice($cart_total)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
