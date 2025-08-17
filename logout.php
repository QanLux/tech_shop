<?php
require_once 'includes/functions.php';

// Xóa session
session_destroy();

// Redirect về trang chủ
redirectWithMessage('index.php', 'Đã đăng xuất thành công', 'success');
?>
