<?php
require_once 'includes/functions.php';

// Redirect nếu đã đăng nhập
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($username)) {
        $errors[] = 'Tên đăng nhập không được để trống';
    }
    
    if (empty($password)) {
        $errors[] = 'Mật khẩu không được để trống';
    }
    
    // Kiểm tra đăng nhập
    if (empty($errors)) {
        $user = fetchOne(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$username, $username]
        );
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Redirect
            if ($user['is_admin']) {
                header('Location: admin/');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $errors[] = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
    }
}

$pageTitle = 'Đăng nhập';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Đăng nhập</h4>
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
                        <label for="username" class="form-label">Tên đăng nhập hoặc Email</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <small class="text-muted">
                        <strong>Tài khoản demo:</strong><br>
                        Admin: admin / admin123<br>
                        User: user / user123
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
