<?php
require_once '../includes/functions.php';

// Chỉ cho phép POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Lấy dữ liệu JSON
$input = json_decode(file_get_contents('php://input'), true);
$coupon_code = sanitize($input['coupon_code'] ?? '');
$order_amount = (float)($input['order_amount'] ?? 0);

if (empty($coupon_code)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
    exit;
}

if ($order_amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Giá trị đơn hàng không hợp lệ']);
    exit;
}

try {
    // Kiểm tra coupon
    $coupon_result = validateCoupon($coupon_code, $order_amount);
    
    if (!$coupon_result['valid']) {
        echo json_encode(['success' => false, 'message' => $coupon_result['message']]);
        exit;
    }
    
    $coupon = $coupon_result['coupon'];
    $discount_amount = calculateDiscount($coupon, $order_amount);
    $final_amount = $order_amount - $discount_amount;
    
    echo json_encode([
        'success' => true,
        'message' => 'Áp dụng mã giảm giá thành công',
        'discount_amount' => $discount_amount,
        'formatted_discount' => formatPrice($discount_amount),
        'final_amount' => $final_amount,
        'formatted_final_amount' => formatPrice($final_amount)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
