<?php
/**
 * File chứa các hàm tiện ích chung
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra user có phải admin không
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Lấy thông tin user hiện tại
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

/**
 * Tạo token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Kiểm tra token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Tạo order number ngẫu nhiên
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
}

/**
 * Format giá tiền
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

/**
 * Gửi email (giả lập)
 */
function sendEmail($to, $subject, $message) {
    // Trong thực tế, bạn có thể sử dụng PHPMailer hoặc mail() function
    // Đây chỉ là giả lập để demo
    $headers = "From: noreply@yourwebsite.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Ghi log email thay vì gửi thật
    $log = "Email to: $to\nSubject: $subject\nMessage: $message\n\n";
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logDir . '/email.log', $log, FILE_APPEND);
    
    return true; // Giả lập thành công
}

/**
 * Lấy key chưa sử dụng cho sản phẩm
 */
function getAvailableKey($productId) {
    return fetchOne(
        "SELECT * FROM product_keys WHERE product_id = ? AND is_used = FALSE LIMIT 1",
        [$productId]
    );
}

/**
 * Đánh dấu key đã sử dụng
 */
function markKeyAsUsed($keyId) {
    executeQuery(
        "UPDATE product_keys SET is_used = TRUE, used_at = NOW() WHERE id = ?",
        [$keyId]
    );
}

/**
 * Kiểm tra coupon có hợp lệ không
 */
function validateCoupon($code, $orderAmount = 0) {
    $coupon = fetchOne(
        "SELECT * FROM coupons WHERE code = ? AND is_active = TRUE",
        [$code]
    );
    
    if (!$coupon) {
        return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại'];
    }
    
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
        return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn'];
    }
    
    if ($coupon['max_uses'] && $coupon['used_count'] >= $coupon['max_uses']) {
        return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];
    }
    
    if ($orderAmount < $coupon['min_order_amount']) {
        return ['valid' => false, 'message' => 'Đơn hàng tối thiểu ' . formatPrice($coupon['min_order_amount'])];
    }
    
    return ['valid' => true, 'coupon' => $coupon];
}

/**
 * Tính toán giảm giá
 */
function calculateDiscount($coupon, $orderAmount) {
    if ($coupon['discount_type'] === 'percentage') {
        return $orderAmount * ($coupon['discount_value'] / 100);
    } else {
        return min($coupon['discount_value'], $orderAmount);
    }
}

/**
 * Tăng số lượt sử dụng coupon
 */
function incrementCouponUsage($couponId) {
    executeQuery(
        "UPDATE coupons SET used_count = used_count + 1 WHERE id = ?",
        [$couponId]
    );
}

/**
 * Lấy số lượng sản phẩm trong giỏ hàng
 */
function getCartItemCount($userId) {
    $result = fetchOne(
        "SELECT COUNT(*) as count FROM cart WHERE user_id = ?",
        [$userId]
    );
    return $result['count'] ?? 0;
}

/**
 * Lấy tổng giá trị giỏ hàng
 */
function getCartTotal($userId) {
    $result = fetchOne(
        "SELECT SUM(c.quantity * p.price) as total 
         FROM cart c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.user_id = ?",
        [$userId]
    );
    return $result['total'] ?? 0;
}

/**
 * Redirect với thông báo
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    
    // Kiểm tra xem đã có output chưa
    if (headers_sent()) {
        // Nếu đã có output, sử dụng JavaScript redirect
        echo "<script>window.location.href = '$url';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
    } else {
        // Nếu chưa có output, sử dụng header redirect
        header("Location: $url");
    }
    exit;
}

/**
 * Hiển thị thông báo
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        
        $alertClass = $type === 'success' ? 'alert-success' : 
                     ($type === 'error' ? 'alert-danger' : 'alert-info');
        
        return "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                    $message
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Tạo password hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
