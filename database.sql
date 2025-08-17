-- Tạo bảng users (người dùng)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    dark_mode BOOLEAN DEFAULT FALSE,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tạo bảng categories (danh mục sản phẩm)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tạo bảng products (sản phẩm)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tạo bảng product_keys (key sản phẩm)
CREATE TABLE product_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    key_code TEXT NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tạo bảng coupons (mã giảm giá)
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tạo bảng orders (đơn hàng)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    coupon_id INT NULL,
    payment_method ENUM('manual', 'vnpay') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_status ENUM('processing', 'completed', 'cancelled') DEFAULT 'processing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
);

-- Tạo bảng order_items (chi tiết đơn hàng)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    product_key_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_key_id) REFERENCES product_keys(id) ON DELETE SET NULL
);

-- Tạo bảng cart (giỏ hàng)
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Thêm dữ liệu mẫu
INSERT INTO categories (name, description) VALUES 
('Phần mềm văn phòng', 'Các phần mềm hỗ trợ công việc văn phòng'),
('Phần mềm thiết kế', 'Các phần mềm thiết kế đồ họa và video'),
('Phần mềm bảo mật', 'Các phần mềm antivirus và bảo mật'),
('Phần mềm tiện ích', 'Các phần mềm tiện ích khác');

INSERT INTO products (name, description, price, category_id) VALUES 
('Microsoft Office 2021 Professional', 'Bộ phần mềm văn phòng chuyên nghiệp bao gồm Word, Excel, PowerPoint, Outlook', 299.00, 1),
('Adobe Photoshop 2024', 'Phần mềm chỉnh sửa ảnh chuyên nghiệp', 599.00, 2),
('Adobe Premiere Pro 2024', 'Phần mềm chỉnh sửa video chuyên nghiệp', 799.00, 2),
('Kaspersky Internet Security', 'Phần mềm bảo mật toàn diện', 199.00, 3),
('WinRAR Pro', 'Phần mềm nén và giải nén file', 49.00, 4);

-- Thêm key mẫu cho sản phẩm
INSERT INTO product_keys (product_id, key_code) VALUES 
(1, 'OFFICE2021-XXXX-YYYY-ZZZZ-AAAA'),
(1, 'OFFICE2021-XXXX-YYYY-ZZZZ-BBBB'),
(1, 'OFFICE2021-XXXX-YYYY-ZZZZ-CCCC'),
(2, 'PHOTOSHOP2024-XXXX-YYYY-ZZZZ'),
(2, 'PHOTOSHOP2024-XXXX-YYYY-AAAA'),
(3, 'PREMIERE2024-XXXX-YYYY-ZZZZ'),
(4, 'KASPERSKY2024-XXXX-YYYY-ZZZZ'),
(5, 'WINRAR2024-XXXX-YYYY-ZZZZ');

-- Thêm coupon mẫu
INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_uses, expires_at) VALUES 
('WELCOME10', 'percentage', 10.00, 100.00, 100, DATE_ADD(NOW(), INTERVAL 30 DAY)),
('SAVE50K', 'fixed', 50.00, 200.00, 50, DATE_ADD(NOW(), INTERVAL 60 DAY)),
('NEWUSER20', 'percentage', 20.00, 50.00, 200, DATE_ADD(NOW(), INTERVAL 90 DAY));

-- Tạo admin mặc định (password: admin123)
INSERT INTO users (username, email, password, full_name, is_admin) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', TRUE);
