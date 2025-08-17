<?php
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Vui lòng đăng nhập', 'error');
}

$currentUser = getCurrentUser();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($full_name)) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (empty($email)) {
        $errors[] = 'Email không được để trống';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    // Kiểm tra email đã tồn tại chưa (trừ user hiện tại)
    if (empty($errors)) {
        $existing_user = fetchOne(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$email, $_SESSION['user_id']]
        );
        if ($existing_user) {
            $errors[] = 'Email đã được sử dụng bởi tài khoản khác';
        }
    }
    
    // Cập nhật thông tin
    if (empty($errors)) {
        try {
            executeQuery(
                "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?",
                [$full_name, $email, $phone, $address, $_SESSION['user_id']]
            );
            
            redirectWithMessage('profile.php', 'Cập nhật thông tin thành công', 'success');
        } catch (Exception $e) {
            $errors[] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}

$pageTitle = 'Hồ sơ';
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <h2 class="mb-4"><i class="fas fa-user-edit me-2"></i>Hồ sơ cá nhân</h2>
        
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
            <div class="card-header">
                <h5 class="mb-0">Thông tin cá nhân</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly>
                            <div class="form-text">Tên đăng nhập không thể thay đổi</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Họ tên *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Cập nhật thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Thông tin tài khoản</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Ngày tham gia:</strong> <?php echo date('d/m/Y', strtotime($currentUser['created_at'])); ?></p>
                        <p><strong>Trạng thái:</strong> 
                            <span class="badge bg-success">Hoạt động</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Vai trò:</strong> 
                            <?php if ($currentUser['is_admin']): ?>
                                <span class="badge bg-danger">Administrator</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Người dùng</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Chế độ giao diện:</strong> 
                            <?php if ($currentUser['dark_mode']): ?>
                                <span class="badge bg-dark">Dark Mode</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">Light Mode</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="change_password.php" class="btn btn-outline-warning me-2">
                <i class="fas fa-key me-2"></i>Đổi mật khẩu
            </a>
            <a href="orders.php" class="btn btn-outline-primary">
                <i class="fas fa-list me-2"></i>Xem đơn hàng
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
