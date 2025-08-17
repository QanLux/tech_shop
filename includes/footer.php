    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-key me-2"></i>KeyStore</h5>
                    <p class="text-muted">Website bán key phần mềm uy tín, chất lượng cao với giá cả hợp lý.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
                        <li><a href="index.php?category=1" class="text-decoration-none">Phần mềm văn phòng</a></li>
                        <li><a href="index.php?category=2" class="text-decoration-none">Phần mềm thiết kế</a></li>
                        <li><a href="index.php?category=3" class="text-decoration-none">Phần mềm bảo mật</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Hỗ trợ</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i>support@keystore.com</li>
                        <li><i class="fas fa-phone me-2"></i>0123 456 789</li>
                        <li><i class="fas fa-clock me-2"></i>24/7 Hỗ trợ</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2024 KeyStore. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dark mode toggle script -->
    <script>
        // Dark mode functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const body = document.body;
        
        // Check for saved dark mode preference
        const darkMode = localStorage.getItem('darkMode') === 'true';
        if (darkMode) {
            body.setAttribute('data-theme', 'dark');
            darkModeIcon.className = 'fas fa-sun';
        }
        
        // Toggle dark mode
        darkModeToggle.addEventListener('click', function() {
            const isDark = body.getAttribute('data-theme') === 'dark';
            
            if (isDark) {
                body.removeAttribute('data-theme');
                darkModeIcon.className = 'fas fa-moon';
                localStorage.setItem('darkMode', 'false');
            } else {
                body.setAttribute('data-theme', 'dark');
                darkModeIcon.className = 'fas fa-sun';
                localStorage.setItem('darkMode', 'true');
            }
            
            // Update dark mode in database if user is logged in
            if (<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                fetch('ajax/update_dark_mode.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        dark_mode: !isDark
                    })
                });
            }
        });
    </script>
</body>
</html>
