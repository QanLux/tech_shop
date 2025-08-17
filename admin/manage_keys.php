<?php
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirectWithMessage('../index.php', 'Bạn không có quyền truy cập trang này', 'error');
}

$product_id = (int)($_GET['product_id'] ?? 0);

if (!$product_id) {
    redirectWithMessage('products.php', 'Không tìm thấy sản phẩm', 'error');
}

// Lấy thông tin sản phẩm
$product = fetchOne(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.id = ?",
    [$product_id]
);

if (!$product) {
    redirectWithMessage('products.php', 'Sản phẩm không tồn tại', 'error');
}

// Xử lý thêm key
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_single') {
        $key_code = sanitize($_POST['key_code'] ?? '');
        
        if (empty($key_code)) {
            redirectWithMessage("manage_keys.php?product_id=$product_id", 'Vui lòng nhập key', 'error');
        }
        
        try {
            executeQuery(
                "INSERT INTO product_keys (product_id, key_code) VALUES (?, ?)",
                [$product_id, $key_code]
            );
            redirectWithMessage("manage_keys.php?product_id=$product_id", 'Đã thêm key thành công', 'success');
        } catch (Exception $e) {
            redirectWithMessage("manage_keys.php?product_id=$product_id", 'Có lỗi xảy ra', 'error');
        }
    }
    
    if ($action === 'add_multiple') {
        $key_codes = $_POST['key_codes'] ?? '';
        $prefix = sanitize($_POST['prefix'] ?? '');
        $count = (int)($_POST['count'] ?? 0);
        
        if ($count <= 0 || $count > 100) {
            redirectWithMessage("manage_keys.php?product_id=$product_id", 'Số lượng key không hợp lệ', 'error');
        }
        
        try {
            for ($i = 1; $i <= $count; $i++) {
                $key_code = $prefix . '-' . strtoupper(substr(md5(uniqid() . $i), 0, 16));
                executeQuery(
                    "INSERT INTO product_keys (product_id, key_code) VALUES (?, ?)",
                    [$product_id, $key_code]
                );
            }
            redirectWithMessage("manage_keys.php?product_id=$product_id", "Đã thêm $count key thành công", 'success');
        } catch (Exception $e) {
            redirectWithMessage("manage_keys.php?product_id=$product_id", 'Có lỗi xảy ra', 'error');
        }
    }
}

// Xử lý xóa key
if (isset($_GET['delete_key']) && is_numeric($_GET['delete_key'])) {
    $key_id = (int)$_GET['delete_key'];
    
    try {
        // Kiểm tra key có đang được sử dụng không
        $key_usage = fetchOne(
            "SELECT COUNT(*) as count FROM order_items WHERE product_key_id = ?",
            [$key_id]
        )['count'];
        
        if ($key_usage > 0) {
            redirectWithMessage("manage_keys.php?product_id=$product_id", 'Không thể xóa key đã được sử dụng', 'error');
        }
        
        executeQuery("DELETE FROM product_keys WHERE id = ? AND product_id = ?", [$key_id, $product_id]);
        redirectWithMessage("manage_keys.php?product_id=$product_id", 'Đã xóa key thành công', 'success');
    } catch (Exception $e) {
        redirectWithMessage("manage_keys.php?product_id=$product_id", 'Có lỗi xảy ra', 'error');
    }
}

// Lấy danh sách key
$keys = fetchAll(
    "SELECT pk.*, 
            CASE WHEN oi.id IS NOT NULL THEN 'used' ELSE 'available' END as status,
            oi.created_at as used_at
     FROM product_keys pk 
     LEFT JOIN order_items oi ON pk.id = oi.product_key_id 
     WHERE pk.product_id = ? 
     ORDER BY pk.created_at DESC",
    [$product_id]
);

$available_count = 0;
$used_count = 0;
foreach ($keys as $key) {
    if ($key['status'] === 'available') {
        $available_count++;
    } else {
        $used_count++;
    }
}

$pageTitle = 'Quản lý key';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-key me-2"></i>Quản lý key</h2>
            <a href="products.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Product Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['category_name']); ?>
                        </p>
                        <p class="mb-0"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h4 text-primary"><?php echo formatPrice($product['price']); ?></div>
                        <div class="mb-2">
                            <span class="badge bg-success"><?php echo $available_count; ?> key có sẵn</span>
                            <span class="badge bg-secondary"><?php echo $used_count; ?> key đã dùng</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Add Keys -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thêm key</h5>
            </div>
            <div class="card-body">
                <!-- Add Single Key -->
                <form method="POST" action="" class="mb-4">
                    <input type="hidden" name="action" value="add_single">
                    <h6>Thêm key đơn lẻ</h6>
                    <div class="mb-3">
                        <label for="key_code" class="form-label">Key code</label>
                        <input type="text" class="form-control" id="key_code" name="key_code" 
                               placeholder="Nhập key code">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Thêm key
                    </button>
                </form>
                
                <hr>
                
                <!-- Add Multiple Keys -->
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_multiple">
                    <h6>Thêm nhiều key</h6>
                    <div class="mb-3">
                        <label for="prefix" class="form-label">Tiền tố</label>
                        <input type="text" class="form-control" id="prefix" name="prefix" 
                               value="<?php echo strtoupper(substr($product['name'], 0, 8)); ?>" 
                               placeholder="Ví dụ: OFFICE2021">
                    </div>
                    <div class="mb-3">
                        <label for="count" class="form-label">Số lượng</label>
                        <input type="number" class="form-control" id="count" name="count" 
                               value="10" min="1" max="100">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-2"></i>Thêm nhiều key
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Keys List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Danh sách key (<?php echo count($keys); ?> key)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($keys)): ?>
                    <p class="text-muted text-center">Chưa có key nào</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Key Code</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Ngày sử dụng</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($keys as $key): ?>
                                    <tr>
                                        <td>
                                            <code><?php echo htmlspecialchars($key['key_code']); ?></code>
                                        </td>
                                        <td>
                                            <?php if ($key['status'] === 'available'): ?>
                                                <span class="badge bg-success">Có sẵn</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Đã sử dụng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($key['created_at'])); ?></td>
                                        <td>
                                            <?php if ($key['used_at']): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($key['used_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($key['status'] === 'available'): ?>
                                                <a href="manage_keys.php?product_id=<?php echo $product_id; ?>&delete_key=<?php echo $key['id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm"
                                                   onclick="return confirm('Bạn có chắc muốn xóa key này?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
