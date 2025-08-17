<?php
require_once 'includes/functions.php';

// Lấy tham số tìm kiếm và lọc
$search = sanitize($_GET['search'] ?? '');
$category_id = (int)($_GET['category'] ?? 0);

// Xây dựng query
$where_conditions = ["p.status = 'active'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

$where_clause = implode(' AND ', $where_conditions);

// Lấy danh sách sản phẩm
$products = fetchAll(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE $where_clause 
     ORDER BY p.created_at DESC",
    $params
);

// Lấy danh sách categories cho filter
$categories = fetchAll("SELECT * FROM categories ORDER BY name");

$pageTitle = 'Trang chủ';
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body text-center py-5">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-key me-3"></i>KeyStore
                </h1>
                <p class="lead mb-4">Website bán key phần mềm uy tín, chất lượng cao với giá cả hợp lý</p>
                <a href="#products" class="btn btn-light btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i>Mua ngay
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-4" id="products">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Tìm kiếm sản phẩm</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nhập tên sản phẩm...">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label">Danh mục</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Tìm kiếm
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Products Grid -->
<div class="row">
    <?php if (empty($products)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>Không tìm thấy sản phẩm</h4>
                    <p class="text-muted">Hãy thử tìm kiếm với từ khóa khác hoặc chọn danh mục khác</p>
                    <a href="index.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card product-card h-100">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="fas fa-box fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted small">
                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['category_name']); ?>
                        </p>
                        <p class="card-text flex-grow-1">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>
                            <?php if (strlen($product['description']) > 100): ?>...<?php endif; ?>
                        </p>
                        
                        <div class="price mb-3">
                            <?php echo formatPrice($product['price']); ?>
                        </div>
                        
                        <div class="mt-auto">
                            <a href="product.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-outline-primary btn-sm w-100 mb-2">
                                <i class="fas fa-eye me-2"></i>Xem chi tiết
                            </a>
                            
                            <?php if (isLoggedIn()): ?>
                                <button class="btn btn-primary btn-sm w-100 add-to-cart" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để mua
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Categories Section -->
<div class="row mt-5">
    <div class="col-12">
        <h3 class="mb-4"><i class="fas fa-tags me-2"></i>Danh mục sản phẩm</h3>
    </div>
    <?php foreach ($categories as $category): ?>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-folder fa-2x text-primary mb-2"></i>
                    <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                    <p class="card-text small text-muted">
                        <?php echo htmlspecialchars($category['description']); ?>
                    </p>
                    <a href="index.php?category=<?php echo $category['id']; ?>" 
                       class="btn btn-outline-primary btn-sm">
                        Xem sản phẩm
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Add to cart functionality
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const button = this;
        
        // Disable button
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang thêm...';
        
        fetch('ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                } else {
                    // Create badge if it doesn't exist
                    const cartLink = document.querySelector('a[href="cart.php"]');
                    if (cartLink) {
                        const badge = document.createElement('span');
                        badge.className = 'cart-badge';
                        badge.textContent = data.cart_count;
                        cartLink.appendChild(badge);
                    }
                }
                
                // Show success message
                button.innerHTML = '<i class="fas fa-check me-2"></i>Đã thêm';
                button.className = 'btn btn-success btn-sm w-100';
                
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ';
                    button.className = 'btn btn-primary btn-sm w-100 add-to-cart';
                    button.disabled = false;
                }, 2000);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
                button.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ';
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra');
            button.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ';
            button.disabled = false;
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
