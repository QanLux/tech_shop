<?php
require_once 'includes/functions.php';

$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    redirectWithMessage('index.php', 'Sản phẩm không tồn tại', 'error');
}

// Lấy thông tin sản phẩm
$product = fetchOne(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.id = ? AND p.status = 'active'",
    [$product_id]
);

if (!$product) {
    redirectWithMessage('index.php', 'Sản phẩm không tồn tại', 'error');
}

$pageTitle = $product['name'];

// Lấy số lượng key có sẵn
$available_keys = fetchOne(
    "SELECT COUNT(*) as count FROM product_keys WHERE product_id = ? AND is_used = FALSE",
    [$product_id]
);

// Lấy sản phẩm liên quan
$related_products = fetchAll(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
     ORDER BY p.created_at DESC 
     LIMIT 4",
    [$product['category_id'], $product_id]
);

$pageTitle = $product['name'];
require_once 'includes/header.php';
?>

<div class="row">
    <!-- Product Image -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <?php if ($product['image']): ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                     style="height: 400px;">
                    <i class="fas fa-box fa-5x text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                        <li class="breadcrumb-item">
                            <a href="index.php?category=<?php echo $product['category_id']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                    </ol>
                </nav>
                
                <h2 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                
                <p class="text-muted">
                    <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($product['category_name']); ?>
                </p>
                
                <div class="price mb-3">
                    <span class="h3"><?php echo formatPrice($product['price']); ?></span>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-success">
                        <i class="fas fa-key me-1"></i>
                        <?php echo $available_keys['count']; ?> key có sẵn
                    </span>
                </div>
                
                <div class="mb-4">
                    <h5>Mô tả sản phẩm:</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <form id="addToCartForm" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Số lượng:</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="1" min="1" max="<?php echo $available_keys['count']; ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Vui lòng <a href="login.php">đăng nhập</a> để mua sản phẩm này.
                    </div>
                <?php endif; ?>
                
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <i class="fas fa-shipping-fast fa-2x text-primary mb-2"></i>
                            <h6>Giao hàng ngay</h6>
                            <small class="text-muted">Nhận key ngay lập tức</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <h6>Bảo hành</h6>
                            <small class="text-muted">Key chính hãng 100%</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <i class="fas fa-headset fa-2x text-info mb-2"></i>
                            <h6>Hỗ trợ 24/7</h6>
                            <small class="text-muted">Tư vấn miễn phí</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
<div class="row mt-5">
    <div class="col-12">
        <h3 class="mb-4"><i class="fas fa-thumbs-up me-2"></i>Sản phẩm liên quan</h3>
    </div>
    <?php foreach ($related_products as $related): ?>
        <div class="col-md-3 mb-4">
            <div class="card product-card h-100">
                <?php if ($related['image']): ?>
                    <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>">
                <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="fas fa-box fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h6>
                    <p class="card-text text-muted small">
                        <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($related['category_name']); ?>
                    </p>
                    <p class="card-text flex-grow-1">
                        <?php echo htmlspecialchars(substr($related['description'], 0, 80)); ?>...
                    </p>
                    
                    <div class="price mb-3">
                        <?php echo formatPrice($related['price']); ?>
                    </div>
                    
                    <div class="mt-auto">
                        <a href="product.php?id=<?php echo $related['id']; ?>" 
                           class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-eye me-2"></i>Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
// Add to cart functionality
document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const quantity = document.getElementById('quantity').value;
    const button = this.querySelector('button[type="submit"]');
    
    // Disable button
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang thêm...';
    
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: <?php echo $product_id; ?>,
            quantity: parseInt(quantity)
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
            button.innerHTML = '<i class="fas fa-check me-2"></i>Đã thêm vào giỏ hàng';
            button.className = 'btn btn-success btn-lg w-100';
            
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng';
                button.className = 'btn btn-primary btn-lg w-100';
                button.disabled = false;
            }, 2000);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
            button.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng';
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
        button.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng';
        button.disabled = false;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
