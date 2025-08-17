<?php
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Vui lòng đăng nhập', 'error');
}

// Lấy thông tin đơn hàng từ session
if (!isset($_SESSION['order_success'])) {
    redirectWithMessage('index.php', 'Không có thông tin đơn hàng', 'error');
}

$order_info = $_SESSION['order_success'];
unset($_SESSION['order_success']); // Xóa để tránh hiển thị lại

$pageTitle = 'Đặt hàng thành công';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                
                <h2 class="mb-3">Đặt hàng thành công!</h2>
                <p class="lead mb-4">
                    Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đã được xử lý thành công.
                </p>
                
                <div class="alert alert-info">
                    <strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order_info['order_number']); ?><br>
                    <strong>Tổng tiền:</strong> <?php echo formatPrice($order_info['final_amount']); ?>
                </div>
            </div>
        </div>
        
        <!-- Keys Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>Key sản phẩm của bạn
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Lưu ý:</strong> Vui lòng lưu lại các key này. Chúng sẽ không được hiển thị lại.
                </div>
                
                <?php foreach ($order_info['keys'] as $key_info): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1"><?php echo htmlspecialchars($key_info['product_name']); ?></h6>
                                <small class="text-muted">Key sản phẩm</small>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($key_info['key_code']); ?>" 
                                           readonly>
                                    <button class="btn btn-outline-secondary copy-btn" type="button" 
                                            data-clipboard-text="<?php echo htmlspecialchars($key_info['key_code']); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="text-center mt-4">
                    <button class="btn btn-primary" onclick="copyAllKeys()">
                        <i class="fas fa-copy me-2"></i>Sao chép tất cả key
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Hướng dẫn sử dụng
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-download me-2"></i>Tải phần mềm</h6>
                        <p class="small text-muted">
                            Tải phần mềm từ trang web chính thức của nhà phát triển.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-key me-2"></i>Kích hoạt key</h6>
                        <p class="small text-muted">
                            Mở phần mềm và nhập key vào phần kích hoạt hoặc đăng ký.
                        </p>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-shield-alt me-2"></i>Bảo mật</h6>
                        <p class="small text-muted">
                            Không chia sẻ key với người khác. Mỗi key chỉ sử dụng được một lần.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-headset me-2"></i>Hỗ trợ</h6>
                        <p class="small text-muted">
                            Nếu gặp vấn đề, liên hệ hỗ trợ qua email hoặc hotline.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="text-center mt-4">
            <a href="orders.php" class="btn btn-outline-primary me-2">
                <i class="fas fa-list me-2"></i>Xem đơn hàng
            </a>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
            </a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script>
// Initialize clipboard.js
new ClipboardJS('.copy-btn');

// Copy button feedback
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-check"></i>';
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-success');
        
        setTimeout(() => {
            this.innerHTML = originalText;
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-secondary');
        }, 2000);
    });
});

// Copy all keys function
function copyAllKeys() {
    const keys = [];
    document.querySelectorAll('input[readonly]').forEach(input => {
        keys.push(input.value);
    });
    
    const keysText = keys.join('\n');
    
    navigator.clipboard.writeText(keysText).then(() => {
        alert('Đã sao chép tất cả key vào clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = keysText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Đã sao chép tất cả key vào clipboard!');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
