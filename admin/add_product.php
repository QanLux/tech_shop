<?php
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    redirectWithMessage('../index.php', 'Bạn không có quyền truy cập trang này', 'error');
}

// Lấy danh sách categories
$categories = fetchAll("SELECT * FROM categories ORDER BY name");

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');
    $image = sanitize($_POST['image'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Tên sản phẩm không được để trống';
    }
    
    if (empty($description)) {
        $errors[] = 'Mô tả sản phẩm không được để trống';
    }
    
    if ($price <= 0) {
        $errors[] = 'Giá sản phẩm phải lớn hơn 0';
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Vui lòng chọn danh mục';
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Trạng thái không hợp lệ';
    }
    
    // Thêm sản phẩm
    if (empty($errors)) {
        try {
            executeQuery(
                "INSERT INTO products (name, description, price, category_id, image, status) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$name, $description, $price, $category_id, $image, $status]
            );
            
            $product_id = $pdo->lastInsertId();
            
            redirectWithMessage("manage_keys.php?product_id=$product_id", 'Đã thêm sản phẩm thành công. Vui lòng thêm key cho sản phẩm.', 'success');
        } catch (Exception $e) {
            $errors[] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

$pageTitle = 'Thêm sản phẩm';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus me-2"></i>Thêm sản phẩm mới</h2>
            <a href="products.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin sản phẩm</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên sản phẩm *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả sản phẩm *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Giá sản phẩm (VNĐ) *</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?php echo htmlspecialchars($price ?? ''); ?>" min="0" step="1000" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Danh mục *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($category_id ?? 0) == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">URL hình ảnh</label>
                            <input type="url" class="form-control" id="image" name="image" 
                                   value="<?php echo htmlspecialchars($image ?? ''); ?>" 
                                   placeholder="https://example.com/image.jpg">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo ($status ?? 'active') === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="inactive" <?php echo ($status ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Ẩn</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Thêm sản phẩm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Hướng dẫn</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Tên sản phẩm nên ngắn gọn và dễ hiểu</li>
                    <li>Mô tả chi tiết về tính năng và lợi ích</li>
                    <li>Giá sản phẩm phải chính xác</li>
                    <li>Chọn danh mục phù hợp</li>
                    <li>Sau khi thêm sản phẩm, bạn cần thêm key</li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-tag me-2"></i>Danh mục hiện có</h6>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <p class="text-muted small">Chưa có danh mục nào</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($categories as $category): ?>
                            <li class="mb-1">
                                <i class="fas fa-folder me-2 text-primary"></i>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
