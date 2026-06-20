<?php
/**
 * auth.php
 * -----------------------------------------------------------
 * Trang Đăng nhập / Đăng ký (UC02: Đăng ký tài khoản + đăng nhập)
 * Chức năng:
 *  - Đăng nhập bằng Email + Mật khẩu, so sánh trực tiếp (plain-text,
 *    KHÔNG mã hóa/băm mật khẩu).
 *  - Đăng ký tài khoản mới, kiểm tra Email/SĐT chưa tồn tại (UNIQUE).
 *  - Lưu thông tin phiên làm việc vào session_start().
 * -----------------------------------------------------------
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Nếu đã đăng nhập rồi thì không cần vào trang này nữa
if (daDangNhap()) {
    header('Location: ' . (laAdmin() ? 'admin.php' : 'index.php'));
    exit;
}

$mode = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';
$loiDangNhap = '';
$loiDangKy = '';
$emailDaNhap = '';

// ── XỬ LÝ ĐĂNG NHẬP ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'dangNhap') {
    $email = trim($_POST['email'] ?? '');
    $matKhau = $_POST['matKhau'] ?? '';

    if (!$email || !$matKhau) {
        $loiDangNhap = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } else {
        $stmt = $pdo->prepare("SELECT MaTK, HoTen, Email, MatKhau, VaiTro FROM tai_khoan WHERE Email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $taiKhoan = $stmt->fetch();

        if (!$taiKhoan || $matKhau !== $taiKhoan['MatKhau']) {
            $loiDangNhap = 'Email hoặc mật khẩu không chính xác.';
        } else {
            // Lưu thông tin phiên làm việc
            $_SESSION['MaTK']   = $taiKhoan['MaTK'];
            $_SESSION['HoTen']  = $taiKhoan['HoTen'];
            $_SESSION['Email']  = $taiKhoan['Email'];
            $_SESSION['VaiTro'] = $taiKhoan['VaiTro'];

            $diaChiChuyenHuong = $taiKhoan['VaiTro'] === 'Admin' ? 'admin.php' : 'index.php';
            if (!empty($_GET['redirect'])) $diaChiChuyenHuong = $_GET['redirect'];
            header('Location: ' . $diaChiChuyenHuong . (strpos($diaChiChuyenHuong, '?') === false ? '?' : '&') . 'msg=' . urlencode('Đăng nhập thành công!') . '&type=success');
            exit;
        }
    }
}

// ── XỬ LÝ ĐĂNG KÝ ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'dangKy') {
    $mode = 'register';
    $hoTen   = trim($_POST['hoTen'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $sdt     = trim($_POST['sdt'] ?? '');
    $matKhau = $_POST['matKhau'] ?? '';
    $matKhau2 = $_POST['matKhau2'] ?? '';
    $emailDaNhap = $email;

    if (!$hoTen || !$email || !$sdt) {
        $loiDangKy = 'Vui lòng nhập đầy đủ họ tên, email và số điện thoại.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loiDangKy = 'Email không hợp lệ.';
    } elseif (strlen($matKhau) < 6) {
        $loiDangKy = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($matKhau !== $matKhau2) {
        $loiDangKy = 'Mật khẩu xác nhận không khớp.';
    } else {
        // Kiểm tra Email và SĐT đã tồn tại chưa (Business Rule UC02: mỗi email/SĐT chỉ 1 tài khoản)
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM tai_khoan WHERE Email = :email OR SDT = :sdt");
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->bindParam(':sdt', $sdt);
        $stmtCheck->execute();

        if ((int)$stmtCheck->fetchColumn() > 0) {
            $loiDangKy = 'Email hoặc số điện thoại đã được sử dụng. Vui lòng đăng nhập hoặc dùng thông tin khác.';
        } else {
            // Lưu mật khẩu dạng plain-text (không mã hóa) theo yêu cầu
            $matKhauMaHoa = $matKhau;

            $stmtInsert = $pdo->prepare("
                INSERT INTO tai_khoan (HoTen, Email, SDT, MatKhau, VaiTro)
                VALUES (:hoTen, :email, :sdt, :matKhau, 'ThanhVien')
            ");
            $stmtInsert->bindParam(':hoTen', $hoTen);
            $stmtInsert->bindParam(':email', $email);
            $stmtInsert->bindParam(':sdt', $sdt);
            $stmtInsert->bindParam(':matKhau', $matKhauMaHoa);
            $stmtInsert->execute();

            $maTKMoi = $pdo->lastInsertId();

            // Tự động đăng nhập sau khi đăng ký thành công
            $_SESSION['MaTK']   = $maTKMoi;
            $_SESSION['HoTen']  = $hoTen;
            $_SESSION['Email']  = $email;
            $_SESSION['VaiTro'] = 'ThanhVien';

            header('Location: index.php?msg=' . urlencode('Tạo tài khoản thành công! Chào mừng bạn!') . '&type=success');
            exit;
        }
    }
}

$pageTitle = 'Luxury Hotel – Đăng nhập';
require_once '../includes/head.php';
require_once '../includes/navbar.php';
?>

<div class="container">
  <div class="auth-card bg-white border rounded p-4 p-md-5">
    <div class="text-center mb-4">
      <div class="font-playfair" style="color:var(--gold);letter-spacing:2px;font-size:1.3rem">LUXURY HOTEL</div>
    </div>

    <ul class="nav nav-tabs mb-4 justify-content-center">
      <li class="nav-item">
        <a class="nav-link <?= $mode === 'login' ? 'active' : '' ?>" href="auth.php?mode=login">Đăng nhập</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $mode === 'register' ? 'active' : '' ?>" href="auth.php?mode=register">Đăng ký</a>
      </li>
    </ul>

    <?php if ($mode === 'login'): ?>
      <h2 class="font-playfair h4 mb-1" style="color:var(--navy)">Đăng nhập tài khoản</h2>
      <p class="text-muted small mb-4">Đăng nhập để truy cập các dịch vụ của Luxury Hotel.</p>

      <?php if ($loiDangNhap): ?><div class="alert alert-danger small">⚠ <?= h($loiDangNhap) ?></div><?php endif; ?>

      <form method="POST" action="auth.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>">
        <input type="hidden" name="action" value="dangNhap">
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Email</label>
          <input class="form-control" type="email" name="email" placeholder="your@email.com" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Mật khẩu</label>
          <input class="form-control" type="password" name="matKhau" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-gold w-100 mb-2">Đăng nhập</button>
      </form>
      <p class="text-center small text-muted mt-3">Demo: admin@luxuryhotel.vn / Admin@123 (Admin) — tranvanan@gmail.com / KhachHang123 (Thành viên)</p>

    <?php else: ?>
      <h2 class="font-playfair h4 mb-1" style="color:var(--navy)">Tạo tài khoản mới</h2>
      <p class="text-muted small mb-4">Hoàn tất thông tin để tạo tài khoản Luxury Hotel.</p>

      <?php if ($loiDangKy): ?><div class="alert alert-danger small">⚠ <?= h($loiDangKy) ?></div><?php endif; ?>

      <form method="POST" action="auth.php?mode=register">
        <input type="hidden" name="action" value="dangKy">
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Họ và tên</label>
          <input class="form-control" name="hoTen" placeholder="Trần Văn An" required>
        </div>
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Email</label>
          <input class="form-control" type="email" name="email" value="<?= h($emailDaNhap) ?>" placeholder="email@gmail.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Số điện thoại</label>
          <input class="form-control" name="sdt" placeholder="0912345678" required>
        </div>
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Mật khẩu</label>
          <input class="form-control" type="password" name="matKhau" placeholder="Ít nhất 6 ký tự" required>
        </div>
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Xác nhận mật khẩu</label>
          <input class="form-control" type="password" name="matKhau2" placeholder="Nhập lại mật khẩu" required>
        </div>
        <button type="submit" class="btn btn-gold w-100">Tạo tài khoản</button>
        <p class="small text-muted text-center mt-3">Bằng cách tạo tài khoản, bạn đồng ý với <a href="#">Điều khoản dịch vụ</a> của chúng tôi.</p>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
