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
$quantity = (int)($input['quantity'] ?? 0);

if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // Kiểm tra item trong giỏ hàng có thuộc về user này không
    $cart_item = fetchOne(
        "SELECT c.*, p.name as product_name 
         FROM cart c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.id = ? AND c.user_id = ?",
        [$cart_id, $_SESSION['user_id']]
    );
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        exit;
    }
    
    // Kiểm tra số lượng key có sẵn
    $available_keys = fetchOne(
        "SELECT COUNT(*) as count FROM product_keys WHERE product_id = ? AND is_used = FALSE",
        [$cart_item['product_id']]
    );
    
    if ($available_keys['count'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Không đủ key có sẵn']);
        exit;
    }
    
    // Cập nhật số lượng
    executeQuery(
        "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?",
        [$quantity, $cart_id]
    );
    
    // Tính toán lại tổng tiền
    $cart_total = getCartTotal($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã cập nhật giỏ hàng',
        'cart_total' => $cart_total,
        'formatted_total' => formatPrice($cart_total)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
