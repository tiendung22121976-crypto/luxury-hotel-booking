<?php 
include_once 'header.php'; 

$error = '';
$success = '';

// Xử lý Sự kiện Submit Form từ Client
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        // XỬ LÝ ĐĂNG NHẬP
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        $stmt = $pdo->prepare("SELECT * FROM tai_khoan WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Kiểm tra mật khẩu (Sử dụng so sánh trực tiếp hoặc password_verify tùy cấu hình của bạn)
        if ($user && ($password === $user['MatKhau'] || password_verify($password, $user['MatKhau']))) {
            $_SESSION['user'] = $user;
            echo "<script>window.location.href='index.php';</script>";
            exit();
        } else {
            $error = 'Email hoặc mật khẩu không chính xác!';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        // XỬ LÝ ĐĂNG KÝ THÀNH VIÊN MỚI
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);
        
        // Kiểm tra xem Email đã đăng ký chưa
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tai_khoan WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email này đã tồn tại trên hệ thống!';
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO tai_khoan (HoTen, Email, SDT, MatKhau, VaiTro) VALUES (?, ?, ?, ?, 'ThanhVien')");
            if ($stmtInsert->execute([$fullname, $email, $phone, $password])) {
                $success = 'Tạo tài khoản thành công! Vui lòng đăng nhập.';
            } else {
                $error = 'Đã có lỗi xảy ra khi đăng ký.';
            }
        }
    }
}
?>

<div class="row justify-content-center my-5">
    <div class="col-md-5">

        <div class="text-center mb-4">
            <h2 class="font-luxury fw-bold text-navy mb-1">Đăng nhập hoặc tạo tài khoản</h2>
            <p class="text-muted small mb-0">Truy cập các dịch vụ và ưu đãi dành riêng cho thành viên Luxury Hotel</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger shadow-sm small"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success shadow-sm small"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <ul class="nav nav-pills nav-justified mb-4" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="tab-login" data-bs-toggle="pill" data-bs-target="#pane-login" type="button">ĐĂNG NHẬP</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="tab-register" data-bs-toggle="pill" data-bs-target="#pane-register" type="button">TẠO TÀI KHOẢN</button>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="pane-login">
                    <form method="POST" action="auth.php">
                        <input type="hidden" name="action" value="login">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Địa chỉ Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="name@example.com" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Mật khẩu</label>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-navy text-white w-100 py-2 fw-bold rounded-pill">Tiếp tục với email</button>
                        <div class="text-center mt-3">
                            <a href="#" class="text-muted small text-decoration-none">Khôi phục tài khoản?</a>
                        </div>
                    </form>
                </div>
                
                <div class="tab-pane fade" id="pane-register">
                    <form method="POST" action="auth.php">
                        <input type="hidden" name="action" value="register">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Họ và Tên</label>
                            <input type="text" name="fullname" class="form-control" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Địa chỉ Email</label>
                            <input type="email" name="email" class="form-control" placeholder="nguyenvana@gmail.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control" placeholder="0901234567" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Mật khẩu bảo mật</label>
                            <input type="password" name="password" class="form-control" placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 py-2 fw-bold rounded-pill">Hoàn tất đăng ký</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
