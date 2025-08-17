<?php
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirectWithMessage('../index.php', 'Bạn không có quyền truy cập trang này', 'error');
}

// Lấy thống kê
$stats = [
    'total_users' => fetchOne("SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE")['count'],
    'total_products' => fetchOne("SELECT COUNT(*) as count FROM products")['count'],
    'total_orders' => fetchOne("SELECT COUNT(*) as count FROM orders")['count'],
    'total_revenue' => fetchOne("SELECT SUM(final_amount) as total FROM orders WHERE order_status = 'completed'")['total'] ?? 0
];

// Lấy đơn hàng gần đây
$recent_orders = fetchAll(
    "SELECT o.*, u.username, u.full_name 
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     ORDER BY o.created_at DESC 
     LIMIT 5"
);

// Lấy sản phẩm bán chạy
$top_products = fetchAll(
    "SELECT p.name, COUNT(oi.id) as sold_count, SUM(oi.quantity) as total_quantity
     FROM products p 
     LEFT JOIN order_items oi ON p.id = oi.product_id 
     GROUP BY p.id 
     ORDER BY total_quantity DESC 
     LIMIT 5"
);

$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo number_format($stats['total_users']); ?></h4>
                        <p class="mb-0">Người dùng</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo number_format($stats['total_products']); ?></h4>
                        <p class="mb-0">Sản phẩm</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo number_format($stats['total_orders']); ?></h4>
                        <p class="mb-0">Đơn hàng</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo formatPrice($stats['total_revenue']); ?></h4>
                        <p class="mb-0">Doanh thu</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Đơn hàng gần đây
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                    <p class="text-muted text-center">Chưa có đơn hàng nào</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <a href="orders.php?id=<?php echo $order['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($order['order_number']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td><?php echo formatPrice($order['final_amount']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order['order_status'] === 'completed' ? 'success' : 
                                                    ($order['order_status'] === 'processing' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php 
                                                echo $order['order_status'] === 'completed' ? 'Hoàn thành' : 
                                                    ($order['order_status'] === 'processing' ? 'Đang xử lý' : 'Đã hủy'); 
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Sản phẩm bán chạy
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_products)): ?>
                    <p class="text-muted text-center">Chưa có dữ liệu bán hàng</p>
                <?php else: ?>
                    <?php 
                    $total_products = count($top_products);
                    $current_index = 0;
                    foreach ($top_products as $product): 
                        $current_index++;
                    ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                <small class="text-muted"><?php echo $product['sold_count']; ?> đơn hàng</small>
                            </div>
                            <div class="text-end">
                                <strong><?php echo $product['total_quantity']; ?></strong>
                                <br>
                                <small class="text-muted">sản phẩm</small>
                            </div>
                        </div>
                        <?php if ($current_index < $total_products): ?>
                            <hr class="my-2">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="text-end mt-3">
                        <a href="products.php" class="btn btn-sm btn-outline-primary">Quản lý sản phẩm</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="products.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-box me-2"></i>Quản lý sản phẩm
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="orders.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-shopping-cart me-2"></i>Quản lý đơn hàng
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="users.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-users me-2"></i>Quản lý người dùng
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="coupons.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-tag me-2"></i>Quản lý coupon
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
