# Website Bán Key Phần Mềm

Website bán key/phần mềm được xây dựng bằng PHP thuần và MySQL, không sử dụng framework hay API bên thứ ba.

## Tính năng chính

### 1. Hệ thống tài khoản người dùng
- Đăng ký/đăng nhập tài khoản
- Quản lý hồ sơ cá nhân
- Đổi mật khẩu
- Bật/tắt dark mode (lưu trạng thái vào database)
- Xem lịch sử đơn hàng

### 2. Trang người dùng (Frontend)
- Trang chủ hiển thị danh sách sản phẩm
- Tìm kiếm sản phẩm theo tên
- Lọc sản phẩm theo danh mục
- Trang chi tiết sản phẩm
- Giỏ hàng (thêm, sửa, xóa sản phẩm)
- Thanh toán với coupon và phương thức thanh toán
- Hiển thị key sau khi thanh toán thành công

### 3. Admin Dashboard
- Thống kê tổng quan
- Quản lý sản phẩm và key
- Quản lý đơn hàng (lọc theo ngày, user)
- Quản lý tài khoản người dùng
- Quản lý mã giảm giá (coupon)

## Cài đặt

### 1. Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Web server (Apache/Nginx)

### 2. Cài đặt database
1. Tạo database mới
2. Import file `database.sql` để tạo các bảng và dữ liệu mẫu

### 3. Cấu hình kết nối
1. Mở file `config/database.php`
2. Thay đổi thông tin kết nối database:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Tạo thư mục logs
```bash
mkdir logs
chmod 755 logs
```

## Tài khoản demo

### Admin
- Username: `admin`
- Password: `admin123`

### User thường
- Username: `user`
- Password: `user123`

## Cấu trúc thư mục

```
├── ajax/                    # Các file AJAX
│   ├── add_to_cart.php
│   ├── apply_coupon.php
│   ├── get_order_details.php
│   ├── remove_from_cart.php
│   ├── update_cart.php
│   └── update_dark_mode.php
├── admin/                   # Trang admin
│   ├── index.php           # Dashboard
│   ├── products.php        # Quản lý sản phẩm
│   ├── add_product.php     # Thêm sản phẩm
│   ├── manage_keys.php     # Quản lý key
│   └── orders.php          # Quản lý đơn hàng
├── config/
│   └── database.php        # Cấu hình database
├── includes/
│   ├── functions.php       # Các hàm tiện ích
│   ├── header.php          # Header chung
│   └── footer.php          # Footer chung
├── logs/                   # Thư mục log
├── cart.php               # Trang giỏ hàng
├── change_password.php    # Đổi mật khẩu
├── checkout.php           # Trang thanh toán
├── database.sql           # File SQL tạo database
├── index.php              # Trang chủ
├── login.php              # Đăng nhập
├── logout.php             # Đăng xuất
├── order_success.php      # Trang thành công
├── orders.php             # Lịch sử đơn hàng
├── product.php            # Chi tiết sản phẩm
├── profile.php            # Hồ sơ người dùng
├── register.php           # Đăng ký
└── README.md              # Hướng dẫn sử dụng
```

## Tính năng chi tiết

### 1. Hệ thống Coupon
- Hỗ trợ giảm giá theo phần trăm hoặc số tiền cố định
- Giới hạn số lượt sử dụng
- Ngày hết hạn
- Giá trị đơn hàng tối thiểu

### 2. Quản lý Key
- Thêm key đơn lẻ hoặc hàng loạt
- Tự động tạo key với prefix tùy chỉnh
- Theo dõi trạng thái sử dụng key
- Không cho phép xóa key đã sử dụng

### 3. Thanh toán
- Thanh toán thủ công (nhận key ngay)
- Thanh toán VNPay (giả lập)
- Gửi key qua email (giả lập)
- Hiển thị key trực tiếp trên trang

### 4. Dark Mode
- Lưu trạng thái vào database
- Đồng bộ giữa các thiết bị
- Giao diện responsive

## Bảo mật

- Sử dụng PDO với prepared statements
- Mã hóa mật khẩu với password_hash()
- Validation dữ liệu đầu vào
- CSRF protection
- Kiểm tra quyền truy cập admin

## Tùy chỉnh

### Thêm sản phẩm mới
1. Vào Admin Panel > Quản lý sản phẩm
2. Click "Thêm sản phẩm"
3. Điền thông tin sản phẩm
4. Thêm key cho sản phẩm

### Tạo coupon mới
1. Vào Admin Panel > Quản lý coupon
2. Click "Thêm coupon"
3. Cấu hình thông tin coupon

### Thay đổi giao diện
- Chỉnh sửa CSS trong file `includes/header.php`
- Thay đổi màu sắc và layout theo ý muốn

## Lưu ý

- File SQL không tạo database, chỉ tạo bảng phù hợp với hosting dạng username_database
- Email được giả lập, trong thực tế cần cấu hình SMTP
- Thanh toán VNPay được giả lập, cần tích hợp API thật để sử dụng
- Backup database thường xuyên để bảo vệ dữ liệu

## Hỗ trợ

Nếu có vấn đề hoặc cần hỗ trợ, vui lòng liên hệ:
- Email: support@example.com
- Hotline: 0123 456 789

## License

Dự án này được phát hành dưới giấy phép MIT.
