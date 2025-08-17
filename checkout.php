<?php
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Vui lòng đăng nhập để thanh toán', 'error');
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

if (empty($cart_items)) {
    redirectWithMessage('cart.php', 'Giỏ hàng trống', 'error');
}

$cart_total = getCartTotal($_SESSION['user_id']);
$currentUser = getCurrentUser();

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $coupon_code = sanitize($_POST['coupon_code'] ?? '');
    
    $errors = [];
    
    // Validation
    if (!in_array($payment_method, ['manual', 'vnpay'])) {
        $errors[] = 'Vui lòng chọn phương thức thanh toán';
    }
    
    // Kiểm tra coupon nếu có
    $discount_amount = 0;
    $coupon_id = null;
    
    if (!empty($coupon_code)) {
        $coupon_result = validateCoupon($coupon_code, $cart_total);
        if (!$coupon_result['valid']) {
            $errors[] = $coupon_result['message'];
        } else {
            $discount_amount = calculateDiscount($coupon_result['coupon'], $cart_total);
            $coupon_id = $coupon_result['coupon']['id'];
        }
    }
    
    $final_amount = $cart_total - $discount_amount;
    
    if (empty($errors)) {
        try {
            // Tạo đơn hàng
            $order_number = generateOrderNumber();
            
            executeQuery(
                "INSERT INTO orders (user_id, order_number, total_amount, discount_amount, final_amount, coupon_id, payment_method) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$_SESSION['user_id'], $order_number, $cart_total, $discount_amount, $final_amount, $coupon_id, $payment_method]
            );
            
            $order_id = $pdo->lastInsertId();
            
            // Thêm chi tiết đơn hàng và cấp key
            $keys_provided = [];
            
            foreach ($cart_items as $item) {
                // Lấy key cho sản phẩm
                $keys_needed = $item['quantity'];
                $keys = fetchAll(
                    "SELECT * FROM product_keys WHERE product_id = ? AND is_used = FALSE LIMIT ?",
                    [$item['product_id'], $keys_needed]
                );
                
                if (count($keys) < $keys_needed) {
                    throw new Exception('Không đủ key cho sản phẩm: ' . $item['name']);
                }
                
                // Đánh dấu key đã sử dụng
                foreach ($keys as $key) {
                    markKeyAsUsed($key['id']);
                    $keys_provided[] = [
                        'product_name' => $item['name'],
                        'key_code' => $key['key_code']
                    ];
                }
                
                // Thêm vào order_items
                executeQuery(
                    "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, product_key_id) 
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity'], $keys[0]['id']]
                );
            }
            
            // Tăng số lượt sử dụng coupon
            if ($coupon_id) {
                incrementCouponUsage($coupon_id);
            }
            
            // Xóa giỏ hàng
            executeQuery("DELETE FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
            
            // Lưu thông tin key vào session để hiển thị
            $_SESSION['order_success'] = [
                'order_number' => $order_number,
                'final_amount' => $final_amount,
                'keys' => $keys_provided
            ];
            
            // Gửi email (giả lập)
            $email_content = "Đơn hàng $order_number đã được thanh toán thành công.\n\n";
            $email_content .= "Các key của bạn:\n";
            foreach ($keys_provided as $key_info) {
                $email_content .= "- {$key_info['product_name']}: {$key_info['key_code']}\n";
            }
            
            sendEmail($currentUser['email'], "Đơn hàng $order_number - KeyStore", $email_content);
            
            // Redirect đến trang thành công
            header('Location: order_success.php');
            exit;
            
        } catch (Exception $e) {
            $errors[] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Thanh toán';
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-credit-card me-2"></i>Thanh toán</h2>
    </div>
</div>

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
    <div class="row">
        <div class="col-lg-8">
            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $total_items = count($cart_items);
                    $current_index = 0;
                    foreach ($cart_items as $item): 
                        $current_index++;
                    ?>
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="height: 60px;">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <p class="text-muted small mb-0">
                                    Số lượng: <?php echo $item['quantity']; ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="price">
                                    <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($current_index < $total_items): ?>
                            <hr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Phương thức thanh toán</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment_method" 
                               id="payment_manual" value="manual" checked>
                        <label class="form-check-label" for="payment_manual">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <strong>Thanh toán thủ công (nhận key ngay)</strong>
                            <br>
                            <small class="text-muted">Thanh toán qua chuyển khoản ngân hàng hoặc ví điện tử</small>
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" 
                               id="payment_vnpay" value="vnpay">
                        <label class="form-check-label" for="payment_vnpay">
                            <i class="fas fa-credit-card me-2"></i>
                            <strong>Thanh toán VNPay (giả lập)</strong>
                            <br>
                            <small class="text-muted">Thanh toán qua cổng VNPay (demo)</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Total -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tổng đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span><?php echo formatPrice($cart_total); ?></span>
                    </div>
                    
                    <!-- Coupon Section -->
                    <div class="mb-3">
                        <label for="coupon_code" class="form-label">Mã giảm giá:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="coupon_code" name="coupon_code" 
                                   placeholder="Nhập mã giảm giá">
                            <button class="btn btn-outline-secondary" type="button" id="applyCoupon">
                                Áp dụng
                            </button>
                        </div>
                        <div id="couponMessage" class="form-text"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Giảm giá:</span>
                        <span id="discountAmount">0 VNĐ</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong id="finalAmount"><?php echo formatPrice($cart_total); ?></strong>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock me-2"></i>Hoàn tất thanh toán
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin khách hàng</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($currentUser['full_name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
                    <?php if ($currentUser['phone']): ?>
                        <p class="mb-1"><strong>SĐT:</strong> <?php echo htmlspecialchars($currentUser['phone']); ?></p>
                    <?php endif; ?>
                    <?php if ($currentUser['address']): ?>
                        <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($currentUser['address']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Coupon functionality
document.getElementById('applyCoupon')?.addEventListener('click', function() {
    const couponCode = document.getElementById('coupon_code').value.trim();
    const messageDiv = document.getElementById('couponMessage');
    
    if (!couponCode) {
        messageDiv.innerHTML = '<span class="text-danger">Vui lòng nhập mã giảm giá</span>';
        return;
    }
    
    fetch('ajax/apply_coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            coupon_code: couponCode,
            order_amount: <?php echo $cart_total; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = '<span class="text-success">' + data.message + '</span>';
            document.getElementById('discountAmount').textContent = data.formatted_discount;
            document.getElementById('finalAmount').textContent = data.formatted_final_amount;
        } else {
            messageDiv.innerHTML = '<span class="text-danger">' + data.message + '</span>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.innerHTML = '<span class="text-danger">Có lỗi xảy ra</span>';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
