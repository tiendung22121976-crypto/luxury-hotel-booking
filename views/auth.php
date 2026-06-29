<?php
/**
 * auth.php
 * Trang Đăng nhập / Đăng ký (Đã dọn dẹp Dead Code)
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (daDangNhap()) {
  header('Location: ' . (laAdmin() ? 'admin.php' : 'index.php'));
  exit;
}

$modeRaw = $_GET['mode'] ?? '';
$mode = in_array($modeRaw, ['register', 'forgot'], true) ? $modeRaw : 'login';
$loiDangNhap = ($mode === 'login') ? ($_GET['err'] ?? '') : '';
$loiDangKy   = ($mode === 'register') ? ($_GET['err'] ?? '') : '';
$loiQuenMK   = ($mode === 'forgot') ? ($_GET['err'] ?? '') : '';
$thongBaoLogin = ($mode === 'login') ? ($_GET['msg'] ?? '') : '';

// Bước trong luồng quên mật khẩu: 'email' (nhập email) hoặc 'otp' (nhập OTP + mật khẩu mới)
$buocQuenMK = ($_GET['step'] ?? '') === 'otp' ? 'otp' : 'email';
$demoOtp    = $_GET['demo_otp'] ?? ''; // CHỈ DÙNG CHO DEMO: hiển thị OTP trực tiếp khi chưa có hệ thống gửi email thật
$emailOtp   = $_SESSION['otp_email'] ?? '';

// Nếu chưa có email đang chờ xác thực trong session (vào thẳng URL, hoặc session đã hết),
// luôn đưa người dùng quay lại bước nhập email để tránh form bị thiếu dữ liệu.
if ($buocQuenMK === 'otp' && $emailOtp === '') {
    $buocQuenMK = 'email';
}

$pageTitle = 'Luxury Hotel – Đăng nhập';
require_once '../includes/head.php';
require_once '../includes/navbar.php';
?>

<div class="container my-5">
  <div class="auth-card bg-white border rounded p-4 p-md-5 mx-auto shadow-sm" style="max-width:520px">
    <div class="text-center mb-4"><div class="font-playfair fw-bold fs-4" style="color:var(--gold);letter-spacing:2px">LUXURY HOTEL</div></div>

    <ul class="nav nav-tabs mb-4 justify-content-center">
      <li class="nav-item"><a class="nav-link <?= $mode === 'login' ? 'active' : '' ?>" href="auth.php?mode=login">Đăng nhập</a></li>
      <li class="nav-item"><a class="nav-link <?= $mode === 'register' ? 'active' : '' ?>" href="auth.php?mode=register">Đăng ký</a></li>
    </ul>

    <?php if ($mode === 'login'): ?>
      <h2 class="font-playfair h5 mb-1 text-navy">Chào mừng trở lại</h2>
      <p class="text-muted small mb-4">Đăng nhập bằng tài khoản của bạn.</p>
      <?php if ($thongBaoLogin): ?><div class="alert alert-success small py-2">✓ <?= h($thongBaoLogin) ?></div><?php endif; ?>
      <?php if ($loiDangNhap): ?><div class="alert alert-danger small py-2">⚠ <?= h($loiDangNhap) ?></div><?php endif; ?>

      <form method="POST" action="../controllers/ctrl_taikhoan.php">
        <input type="hidden" name="action" value="dangNhap">
        <div class="mb-3"><label class="form-label small text-muted">EMAIL</label><input class="form-control" type="email" name="email" required autofocus></div>
        <div class="mb-2"><label class="form-label small text-muted">MẬT KHẨU</label><input class="form-control" type="password" name="matKhau" required></div>
        <div class="mb-4 text-end"><a href="auth.php?mode=forgot" class="small text-muted">Quên mật khẩu?</a></div>
        <button type="submit" class="btn btn-gold w-100 py-2 fw-medium">Đăng nhập</button>
      </form>
    <?php elseif ($mode === 'register'): ?>
      <h2 class="font-playfair h5 mb-1 text-navy">Tạo tài khoản mới</h2>
      <p class="text-muted small mb-4">Trở thành thành viên của Luxury Hotel.</p>
      <?php if ($loiDangKy): ?><div class="alert alert-danger small py-2">⚠ <?= h($loiDangKy) ?></div><?php endif; ?>

      <form method="POST" action="../controllers/ctrl_taikhoan.php">
        <input type="hidden" name="action" value="dangKy">
        <div class="mb-3"><label class="form-label small text-muted">HỌ TÊN</label><input class="form-control" name="hoTen" required></div>
        <div class="mb-3"><label class="form-label small text-muted">EMAIL</label><input class="form-control" type="email" name="email" required></div>
        <div class="mb-3"><label class="form-label small text-muted">SỐ ĐIỆN THOẠI</label><input class="form-control" name="sdt" required></div>
        <div class="mb-3"><label class="form-label small text-muted">MẬT KHẨU</label><input class="form-control" type="password" name="matKhau" required></div>
        <div class="mb-4"><label class="form-label small text-muted">XÁC NHẬN MẬT KHẨU</label><input class="form-control" type="password" name="matKhau2" required></div>
        <button type="submit" class="btn btn-gold w-100 py-2 fw-medium">Đăng ký</button>
      </form>
    <?php else: /* mode === 'forgot' */ ?>

      <?php if ($buocQuenMK === 'email'): ?>
        <h2 class="font-playfair h5 mb-1 text-navy">Quên mật khẩu</h2>
        <p class="text-muted small mb-4">Nhập email đã đăng ký, hệ thống sẽ gửi mã OTP để xác nhận.</p>
        <?php if ($loiQuenMK): ?><div class="alert alert-danger small py-2">⚠ <?= h($loiQuenMK) ?></div><?php endif; ?>

        <form method="POST" action="../controllers/ctrl_taikhoan.php">
          <input type="hidden" name="action" value="quenMatKhau">
          <div class="mb-4"><label class="form-label small text-muted">EMAIL</label><input class="form-control" type="email" name="email" required autofocus></div>
          <button type="submit" class="btn btn-gold w-100 py-2 fw-medium">Gửi mã OTP</button>
        </form>
        <p class="text-center small text-muted mt-3 mb-0"><a href="auth.php?mode=login">← Quay lại đăng nhập</a></p>

      <?php else: /* buocQuenMK === 'otp' */ ?>
        <h2 class="font-playfair h5 mb-1 text-navy">Xác nhận OTP</h2>
        <p class="text-muted small mb-4">
          Mã OTP đã được gửi đến email <strong><?= h($emailOtp) ?></strong>.
          Mã có hiệu lực trong <strong>5 phút</strong>.
        </p>
        <?php if ($demoOtp): ?>
          <div class="alert alert-info small py-2">
            Mã OTP của bạn là:
            <strong style="letter-spacing:2px"><?= h($demoOtp) ?></strong>
          </div>
        <?php endif; ?>
        <?php if ($loiQuenMK): ?><div class="alert alert-danger small py-2">⚠ <?= h($loiQuenMK) ?></div><?php endif; ?>

        <form method="POST" action="../controllers/ctrl_taikhoan.php">
          <input type="hidden" name="action" value="xacNhanOTP">
          <input type="hidden" name="email" value="<?= h($emailOtp) ?>">
          <div class="mb-3">
            <label class="form-label small text-muted">MÃ OTP</label>
            <input class="form-control" type="text" name="otp" inputmode="numeric" maxlength="6" pattern="\d{6}" placeholder="Nhập 6 số" required autofocus>
          </div>
          <div class="mb-3"><label class="form-label small text-muted">MẬT KHẨU MỚI</label><input class="form-control" type="password" name="matKhauMoi" minlength="6" required></div>
          <div class="mb-4"><label class="form-label small text-muted">XÁC NHẬN MẬT KHẨU MỚI</label><input class="form-control" type="password" name="matKhauMoi2" minlength="6" required></div>
          <button type="submit" class="btn btn-gold w-100 py-2 fw-medium">Đặt lại mật khẩu</button>
        </form>
        <p class="text-center small text-muted mt-3 mb-0"><a href="auth.php?mode=forgot">← Chưa nhận được mã? Gửi lại</a></p>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>