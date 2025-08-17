<?php
// Kiểm tra xem có phải đang ở thư mục admin không
$isAdminPage = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
if ($isAdminPage) {
    require_once __DIR__ . '/functions.php';
} else {
    require_once __DIR__ . '/functions.php';
}
$currentUser = getCurrentUser();
$cartCount = isLoggedIn() ? getCartItemCount($_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Website Bán Key Phần Mềm</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --body-bg: #ffffff;
            --body-color: #212529;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
        }

        [data-theme="dark"] {
            --body-bg: #1a1a1a;
            --body-color: #ffffff;
            --card-bg: #2d2d2d;
            --border-color: #404040;
        }

        body {
            background-color: var(--body-bg);
            color: var(--body-color);
            transition: background-color 0.3s, color 0.3s;
        }

        .card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }

        .navbar {
            background-color: var(--card-bg) !important;
            border-bottom: 1px solid var(--border-color);
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: var(--body-color) !important;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .price {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--success-color);
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
        }

        .footer {
            background-color: var(--card-bg);
            border-top: 1px solid var(--border-color);
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <?php 
            // Xác định base URL
            $baseUrl = '';
            if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
                $baseUrl = '../';
            }
            ?>
            <a class="navbar-brand" href="<?php echo $baseUrl; ?>index.php">
                <i class="fas fa-key me-2"></i>KeyStore
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>index.php">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>index.php?category=1">
                            <i class="fas fa-briefcase me-1"></i>Văn phòng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>index.php?category=2">
                            <i class="fas fa-palette me-1"></i>Thiết kế
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>index.php?category=3">
                            <i class="fas fa-shield-alt me-1"></i>Bảo mật
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Dark mode toggle -->
                    <li class="nav-item">
                        <button class="btn btn-outline-primary btn-sm" id="darkModeToggle">
                            <i class="fas fa-moon" id="darkModeIcon"></i>
                        </button>
                    </li>
                    
                    <!-- Cart -->
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo $baseUrl; ?>cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Giỏ hàng
                            <?php if ($cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- User menu -->
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($currentUser['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>admin/">
                                <i class="fas fa-cog me-2"></i>Admin Panel
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>profile.php">
                                <i class="fas fa-user-edit me-2"></i>Hồ sơ
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>orders.php">
                                <i class="fas fa-list me-2"></i>Đơn hàng
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>register.php">
                            <i class="fas fa-user-plus me-1"></i>Đăng ký
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-4">
        <?php echo displayMessage(); ?>
