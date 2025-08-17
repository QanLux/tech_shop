<?php
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirectWithMessage('../index.php', 'Bạn không có quyền truy cập trang này', 'error');
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    
    try {
        // Kiểm tra xem có đơn hàng nào đang sử dụng sản phẩm này không
        $order_count = fetchOne(
            "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?",
            [$product_id]
        )['count'];
        
        if ($order_count > 0) {
            redirectWithMessage('products.php', 'Không thể xóa sản phẩm đã có đơn hàng', 'error');
        }
        
        // Xóa sản phẩm
        executeQuery("DELETE FROM products WHERE id = ?", [$product_id]);
        redirectWithMessage('products.php', 'Đã xóa sản phẩm thành công', 'success');
    } catch (Exception $e) {
        redirectWithMessage('products.php', 'Có lỗi xảy ra khi xóa sản phẩm', 'error');
    }
}

// Lấy danh sách sản phẩm
$products = fetchAll(
    "SELECT p.*, c.name as category_name,
            (SELECT COUNT(*) FROM product_keys WHERE product_id = p.id AND is_used = FALSE) as available_keys,
            (SELECT COUNT(*) FROM product_keys WHERE product_id = p.id) as total_keys
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     ORDER BY p.created_at DESC"
);

// Lấy danh sách categories
$categories = fetchAll("SELECT * FROM categories ORDER BY name");

$pageTitle = 'Quản lý sản phẩm';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-box me-2"></i>Quản lý sản phẩm</h2>
            <a href="add_product.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm sản phẩm
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Danh sách sản phẩm</h5>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <p class="text-muted text-center">Chưa có sản phẩm nào</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Key có sẵn</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <?php if ($product['image']): ?>
                                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo formatPrice($product['price']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['available_keys'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $product['available_keys']; ?>/<?php echo $product['total_keys']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo $product['status'] === 'active' ? 'Hoạt động' : 'Ẩn'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="manage_keys.php?product_id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline-info">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                                <a href="products.php?delete=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
