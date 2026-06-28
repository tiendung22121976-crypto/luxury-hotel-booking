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

$mode = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';
$loiDangNhap = ($_GET['mode'] ?? '') !== 'register' ? ($_GET['err'] ?? '') : '';
$loiDangKy   = ($_GET['mode'] ?? '') === 'register' ? ($_GET['err'] ?? '') : '';

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
      <?php if ($loiDangNhap): ?><div class="alert alert-danger small py-2">⚠ <?= h($loiDangNhap) ?></div><?php endif; ?>

      <form method="POST" action="../controllers/ctrl_taikhoan.php">
        <input type="hidden" name="action" value="dangNhap">
        <div class="mb-3"><label class="form-label small text-muted">EMAIL</label><input class="form-control" type="email" name="email" required autofocus></div>
        <div class="mb-4"><label class="form-label small text-muted">MẬT KHẨU</label><input class="form-control" type="password" name="matKhau" required></div>
        <button type="submit" class="btn btn-gold w-100 py-2 fw-medium">Đăng nhập</button>
      </form>
    <?php else: ?>
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
    <?php endif; ?>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>