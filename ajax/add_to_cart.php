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
$product_id = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // Kiểm tra sản phẩm có tồn tại không
    $product = fetchOne(
        "SELECT * FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }
    
    // Kiểm tra số lượng key có sẵn
    $available_keys = fetchOne(
        "SELECT COUNT(*) as count FROM product_keys WHERE product_id = ? AND is_used = FALSE",
        [$product_id]
    );
    
    if ($available_keys['count'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Không đủ key có sẵn']);
        exit;
    }
    
    // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
    $existing_cart = fetchOne(
        "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
        [$_SESSION['user_id'], $product_id]
    );
    
    if ($existing_cart) {
        // Cập nhật số lượng
        $new_quantity = $existing_cart['quantity'] + $quantity;
        
        if ($new_quantity > $available_keys['count']) {
            echo json_encode(['success' => false, 'message' => 'Không đủ key có sẵn']);
            exit;
        }
        
        executeQuery(
            "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?",
            [$new_quantity, $existing_cart['id']]
        );
    } else {
        // Thêm mới vào giỏ hàng
        executeQuery(
            "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)",
            [$_SESSION['user_id'], $product_id, $quantity]
        );
    }
    
    // Lấy số lượng sản phẩm trong giỏ hàng
    $cart_count = getCartItemCount($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm vào giỏ hàng',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
