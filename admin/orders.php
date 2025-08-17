<?php
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirectWithMessage('../index.php', 'Bạn không có quyền truy cập trang này', 'error');
}

// Xử lý lọc
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_filter = $_GET['user'] ?? '';

// Xây dựng query
$where_conditions = ["1=1"];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.order_status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

if (!empty($user_filter)) {
    $where_conditions[] = "(u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$user_filter%";
    $params[] = "%$user_filter%";
    $params[] = "%$user_filter%";
}

$where_clause = implode(' AND ', $where_conditions);

// Lấy danh sách đơn hàng
$orders = fetchAll(
    "SELECT o.*, u.username, u.full_name, u.email,
            COUNT(oi.id) as item_count,
            GROUP_CONCAT(oi.product_name SEPARATOR ', ') as products
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     LEFT JOIN order_items oi ON o.id = oi.order_id 
     WHERE $where_clause 
     GROUP BY o.id 
     ORDER BY o.created_at DESC",
    $params
);

// Thống kê
$stats = [
    'total_orders' => fetchOne("SELECT COUNT(*) as count FROM orders")['count'],
    'completed_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE order_status = 'completed'")['count'],
    'processing_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE order_status = 'processing'")['count'],
    'total_revenue' => fetchOne("SELECT SUM(final_amount) as total FROM orders WHERE order_status = 'completed'")['total'] ?? 0
];

$pageTitle = 'Quản lý đơn hàng';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Quản lý đơn hàng</h2>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo number_format($stats['total_orders']); ?></h4>
                        <p class="mb-0">Tổng đơn hàng</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x"></i>
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
                        <h4 class="mb-0"><?php echo number_format($stats['completed_orders']); ?></h4>
                        <p class="mb-0">Đã hoàn thành</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
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
                        <h4 class="mb-0"><?php echo number_format($stats['processing_orders']); ?></h4>
                        <p class="mb-0">Đang xử lý</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
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
                        <p class="mb-0">Tổng doanh thu</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Bộ lọc</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-2">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tất cả</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Đến ngày</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="user" class="form-label">Tìm kiếm khách hàng</label>
                        <input type="text" class="form-control" id="user" name="user" 
                               value="<?php echo htmlspecialchars($user_filter); ?>" 
                               placeholder="Username, tên, email...">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Lọc
                            </button>
                            <a href="orders.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Xóa lọc
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Danh sách đơn hàng (<?php echo count($orders); ?> đơn)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p class="text-muted text-center">Không có đơn hàng nào</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($order['username']); ?> | 
                                                    <?php echo htmlspecialchars($order['email']); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo $order['item_count']; ?> sản phẩm</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($order['products'], 0, 100)); ?>
                                                    <?php if (strlen($order['products']) > 100): ?>...<?php endif; ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo formatPrice($order['final_amount']); ?></strong>
                                                <?php if ($order['discount_amount'] > 0): ?>
                                                    <br>
                                                    <small class="text-success">
                                                        Giảm: <?php echo formatPrice($order['discount_amount']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
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
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['order_status'] === 'processing'): ?>
                                                    <a href="orders.php?complete=<?php echo $order['id']; ?>" 
                                                       class="btn btn-outline-success"
                                                       onclick="return confirm('Xác nhận hoàn thành đơn hàng này?')">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
