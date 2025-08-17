<?php
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Vui lòng đăng nhập', 'error');
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($current_password)) {
        $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
    }
    
    if (empty($new_password)) {
        $errors[] = 'Vui lòng nhập mật khẩu mới';
    } elseif (strlen($new_password) < 6) {
        $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    // Kiểm tra mật khẩu hiện tại
    if (empty($errors)) {
        $user = fetchOne("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);
        
        if (!verifyPassword($current_password, $user['password'])) {
            $errors[] = 'Mật khẩu hiện tại không đúng';
        }
    }
    
    // Đổi mật khẩu
    if (empty($errors)) {
        try {
            $hashed_password = hashPassword($new_password);
            
            executeQuery(
                "UPDATE users SET password = ? WHERE id = ?",
                [$hashed_password, $_SESSION['user_id']]
            );
            
            redirectWithMessage('profile.php', 'Đổi mật khẩu thành công', 'success');
        } catch (Exception $e) {
            $errors[] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

$pageTitle = 'Đổi mật khẩu';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4"><i class="fas fa-key me-2"></i>Đổi mật khẩu</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại *</label>
                        <input type="password" class="form-control" id="current_password" 
                               name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Mật khẩu mới *</label>
                        <input type="password" class="form-control" id="new_password" 
                               name="new_password" required>
                        <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới *</label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Đổi mật khẩu
                        </button>
                        <a href="profile.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Lưu ý bảo mật</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Sử dụng mật khẩu mạnh với ít nhất 8 ký tự</li>
                    <li>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                    <li>Không sử dụng thông tin cá nhân trong mật khẩu</li>
                    <li>Không chia sẻ mật khẩu với người khác</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
