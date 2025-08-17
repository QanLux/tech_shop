<?php
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Vui lòng đăng nhập', 'error');
}

// Lấy danh sách đơn hàng
$orders = fetchAll(
    "SELECT o.*, 
            COUNT(oi.id) as item_count,
            GROUP_CONCAT(oi.product_name SEPARATOR ', ') as products
     FROM orders o 
     LEFT JOIN order_items oi ON o.id = oi.order_id 
     WHERE o.user_id = ? 
     GROUP BY o.id 
     ORDER BY o.created_at DESC",
    [$_SESSION['user_id']]
);

$pageTitle = 'Lịch sử đơn hàng';
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-list me-2"></i>Lịch sử đơn hàng</h2>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <h4>Chưa có đơn hàng nào</h4>
                    <p class="text-muted">Bạn chưa có đơn hàng nào trong hệ thống</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Mua sắm ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($orders as $order): ?>
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Đơn hàng: <?php echo htmlspecialchars($order['order_number']); ?>
                                </h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge bg-<?php 
                                    echo $order['order_status'] === 'completed' ? 'success' : 
                                        ($order['order_status'] === 'processing' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php 
                                    echo $order['order_status'] === 'completed' ? 'Hoàn thành' : 
                                        ($order['order_status'] === 'processing' ? 'Đang xử lý' : 'Đã hủy'); 
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-2">
                                    <strong>Sản phẩm:</strong> 
                                    <?php echo htmlspecialchars($order['products']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Số lượng:</strong> <?php echo $order['item_count']; ?> sản phẩm
                                </p>
                                <p class="mb-2">
                                    <strong>Phương thức thanh toán:</strong> 
                                    <?php echo $order['payment_method'] === 'manual' ? 'Thanh toán thủ công' : 'VNPay'; ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Ngày đặt:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="mb-2">
                                    <small class="text-muted">Tạm tính:</small><br>
                                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                                </div>
                                <?php if ($order['discount_amount'] > 0): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">Giảm giá:</small><br>
                                        <span class="text-success">-<?php echo formatPrice($order['discount_amount']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <strong>Tổng cộng:</strong><br>
                                    <span class="h5 text-primary"><?php echo formatPrice($order['final_amount']); ?></span>
                                </div>
                                
                                <button class="btn btn-outline-primary btn-sm" 
                                        onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-eye me-2"></i>Xem chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    // Show loading
    document.getElementById('orderDetailsContent').innerHTML = 
        '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    // Load order details
    fetch('ajax/get_order_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: orderId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('orderDetailsContent').innerHTML = data.html;
        } else {
            document.getElementById('orderDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Có lỗi xảy ra khi tải thông tin đơn hàng</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('orderDetailsContent').innerHTML = 
            '<div class="alert alert-danger">Có lỗi xảy ra khi tải thông tin đơn hàng</div>';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
