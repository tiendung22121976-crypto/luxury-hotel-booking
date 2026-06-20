<?php
/**
 * includes/navbar.php
 * -----------------------------------------------------------
 * Thanh điều hướng (Navbar) dùng chung cho toàn bộ trang khách hàng.
 * File này được include vào các trang con bằng require_once.
 * Biến $activePage (nếu được khai báo trước khi include) dùng để
 * tô sáng (highlight) menu đang active.
 * -----------------------------------------------------------
 */
if (!isset($activePage)) $activePage = '';
$dangNhap = daDangNhap();
?>
<nav class="navbar navbar-expand-lg navbar-luxury sticky-top">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand navbar-brand-luxury" href="index.php">LUXURY <span>HOTEL</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Mở menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
        <li class="nav-item">
          <a class="nav-link <?= $activePage === 'home' ? 'active-link' : '' ?>" href="index.php">Trang chủ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $activePage === 'search' ? 'active-link' : '' ?>" href="search.php">Tìm phòng</a>
        </li>
        <?php if ($dangNhap): ?>
          <li class="nav-item">
            <a class="nav-link <?= $activePage === 'account' ? 'active-link' : '' ?>" href="account.php">Tài khoản</a>
          </li>
          <?php if (laAdmin()): ?>
          <li class="nav-item">
            <a class="nav-link" href="admin.php">Quản trị</a>
          </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="btn btn-nav-primary btn-sm ms-lg-2" href="logout.php">Đăng xuất</a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="auth.php?mode=register">Đăng ký</a></li>
          <li class="nav-item">
            <a class="btn btn-nav-primary btn-sm ms-lg-2" href="auth.php">Đăng nhập</a>
          </li>
        <?php endif; ?>
        <li class="nav-item ms-lg-2">
          <a href="<?= $dangNhap ? 'account.php' : 'auth.php' ?>" class="nav-user-icon" title="Tài khoản">👤</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
