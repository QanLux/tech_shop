<?php
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Vui lòng đăng nhập để xem giỏ hàng', 'error');
}

// Lấy danh sách sản phẩm trong giỏ hàng
$cart_items = fetchAll(
    "SELECT c.*, p.name, p.price, p.image, p.description,
            (SELECT COUNT(*) FROM product_keys WHERE product_id = p.id AND is_used = FALSE) as available_keys
     FROM cart c 
     JOIN products p ON c.product_id = p.id 
     WHERE c.user_id = ? 
     ORDER BY c.created_at DESC",
    [$_SESSION['user_id']]
);

$cart_total = getCartTotal($_SESSION['user_id']);

$pageTitle = 'Giỏ hàng';
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Giỏ hàng</h2>
    </div>
</div>

<?php if (empty($cart_items)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h4>Giỏ hàng trống</h4>
                    <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sản phẩm trong giỏ hàng</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $total_items = count($cart_items);
                    $current_index = 0;
                    foreach ($cart_items as $item): 
                        $current_index++;
                    ?>
                        <div class="row mb-3 cart-item" data-cart-id="<?php echo $item['id']; ?>">
                            <div class="col-md-2">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="height: 80px;">
                                        <i class="fas fa-box fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted small mb-2">
                                    <?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...
                                </p>
                                <div class="text-success small">
                                    <i class="fas fa-key me-1"></i>
                                    <?php echo $item['available_keys']; ?> key có sẵn
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Số lượng:</label>
                                <input type="number" class="form-control form-control-sm quantity-input" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['available_keys']; ?>"
                                       data-cart-id="<?php echo $item['id']; ?>">
                            </div>
                            <div class="col-md-2">
                                <div class="text-end">
                                    <div class="price mb-2">
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </div>
                                    <button class="btn btn-outline-danger btn-sm remove-item" 
                                            data-cart-id="<?php echo $item['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php if ($current_index < $total_items): ?>
                            <hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tổng đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span id="subtotal"><?php echo formatPrice($cart_total); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Giảm giá:</span>
                        <span id="discount">0 VNĐ</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong id="total"><?php echo formatPrice($cart_total); ?></strong>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="checkout.php" class="btn btn-primary">
                            <i class="fas fa-credit-card me-2"></i>Thanh toán
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Update quantity functionality
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        const cartId = this.dataset.cartId;
        const quantity = parseInt(this.value);
        
        if (quantity <= 0) {
            alert('Số lượng phải lớn hơn 0');
            return;
        }
        
        fetch('ajax/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_id: cartId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update totals
                document.getElementById('subtotal').textContent = data.formatted_total;
                document.getElementById('total').textContent = data.formatted_total;
                
                // Update cart count in header
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra');
        });
    });
});

// Remove item functionality
document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function() {
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            return;
        }
        
        const cartId = this.dataset.cartId;
        const cartItem = this.closest('.cart-item');
        
        fetch('ajax/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_id: cartId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                cartItem.remove();
                
                // Update totals
                document.getElementById('subtotal').textContent = data.formatted_total;
                document.getElementById('total').textContent = data.formatted_total;
                
                // Update cart count in header
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                }
                
                // Check if cart is empty
                if (data.cart_count === 0) {
                    location.reload();
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
