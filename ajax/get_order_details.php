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
$order_id = (int)($input['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // Kiểm tra đơn hàng có thuộc về user này không
    $order = fetchOne(
        "SELECT * FROM orders WHERE id = ? AND user_id = ?",
        [$order_id, $_SESSION['user_id']]
    );
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
        exit;
    }
    
    // Lấy chi tiết đơn hàng
    $order_items = fetchAll(
        "SELECT oi.*, pk.key_code 
         FROM order_items oi 
         LEFT JOIN product_keys pk ON oi.product_key_id = pk.id 
         WHERE oi.order_id = ?",
        [$order_id]
    );
    
    // Tạo HTML cho modal
    $html = '<div class="row">';
    $html .= '<div class="col-12">';
    $html .= '<h6>Thông tin đơn hàng</h6>';
    $html .= '<table class="table table-sm">';
    $html .= '<tr><td><strong>Mã đơn hàng:</strong></td><td>' . htmlspecialchars($order['order_number']) . '</td></tr>';
    $html .= '<tr><td><strong>Ngày đặt:</strong></td><td>' . date('d/m/Y H:i', strtotime($order['created_at'])) . '</td></tr>';
    $html .= '<tr><td><strong>Trạng thái:</strong></td><td>';
    
    if ($order['order_status'] === 'completed') {
        $html .= '<span class="badge bg-success">Hoàn thành</span>';
    } elseif ($order['order_status'] === 'processing') {
        $html .= '<span class="badge bg-warning">Đang xử lý</span>';
    } else {
        $html .= '<span class="badge bg-danger">Đã hủy</span>';
    }
    
    $html .= '</td></tr>';
    $html .= '<tr><td><strong>Phương thức thanh toán:</strong></td><td>' . 
             ($order['payment_method'] === 'manual' ? 'Thanh toán thủ công' : 'VNPay') . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<hr>';
    
    $html .= '<div class="row">';
    $html .= '<div class="col-12">';
    $html .= '<h6>Chi tiết sản phẩm</h6>';
    $html .= '<div class="table-responsive">';
    $html .= '<table class="table table-sm">';
    $html .= '<thead><tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Thành tiền</th><th>Key</th></tr></thead>';
    $html .= '<tbody>';
    
    foreach ($order_items as $item) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($item['product_name']) . '</td>';
        $html .= '<td>' . formatPrice($item['price']) . '</td>';
        $html .= '<td>' . $item['quantity'] . '</td>';
        $html .= '<td>' . formatPrice($item['price'] * $item['quantity']) . '</td>';
        $html .= '<td>';
        if ($item['key_code']) {
            $html .= '<code class="small">' . htmlspecialchars($item['key_code']) . '</code>';
        } else {
            $html .= '<span class="text-muted">N/A</span>';
        }
        $html .= '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '<hr>';
    
    $html .= '<div class="row">';
    $html .= '<div class="col-12">';
    $html .= '<h6>Tổng thanh toán</h6>';
    $html .= '<table class="table table-sm">';
    $html .= '<tr><td>Tạm tính:</td><td class="text-end">' . formatPrice($order['total_amount']) . '</td></tr>';
    
    if ($order['discount_amount'] > 0) {
        $html .= '<tr><td>Giảm giá:</td><td class="text-end text-success">-' . formatPrice($order['discount_amount']) . '</td></tr>';
    }
    
    $html .= '<tr><td><strong>Tổng cộng:</strong></td><td class="text-end"><strong>' . formatPrice($order['final_amount']) . '</strong></td></tr>';
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>';
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
