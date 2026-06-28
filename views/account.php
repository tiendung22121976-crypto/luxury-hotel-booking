<?php
/**
 * account.php
 * Trang Tài khoản của tôi (Vá lỗi băm Bcrypt + check hạn hủy)
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

yeuCauDangNhap();

$maTK = $_SESSION['MaTK'];
$thongBaoLoi = '';
$thongBaoThanhCong = '';

// ── XỬ LÝ HỦY ĐẶT PHÒNG (UC10) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'huyDatPhong') {
    $maDon = trim($_POST['maDon'] ?? '');
    $stmtKT = $pdo->prepare("SELECT MaPhong, TrangThaiDon, NgayNhan FROM don_dat_phong WHERE MaDon = :maDon AND MaTK = :maTK");
    $stmtKT->execute([':maDon' => $maDon, ':maTK' => $maTK]);
    $don = $stmtKT->fetch();

    if (!$don) {
        $thongBaoLoi = 'Không tìm thấy đơn đặt phòng hoặc bạn không có quyền hủy.';
    } elseif (!in_array($don['TrangThaiDon'], ['ChoXacNhan', 'DaXacNhan'])) {
        $thongBaoLoi = 'Đơn đặt phòng này không còn ở trạng thái có thể hủy.';
    } elseif (strtotime($don['NgayNhan']) <= time()) {
        // ĐÃ VÁ BẪY GIAN LẬN POSTMAN: Quá thời điểm nhận phòng thì cấm hủy!
        $thongBaoLoi = 'Đơn đặt phòng đã quá hạn thời gian cho phép hủy (phải hủy trước thời điểm nhận phòng).';
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE don_dat_phong SET TrangThaiDon = 'DaHuy' WHERE MaDon = :maDon")->execute([':maDon' => $maDon]);
            $pdo->prepare("UPDATE phong SET TrangThai = 'Available' WHERE MaPhong = :maPhong")->execute([':maPhong' => $don['MaPhong']]);
            $pdo->commit();
            $thongBaoThanhCong = 'Hủy phòng thành công!';
        } catch (PDOException $e) {
            $pdo->rollBack(); $thongBaoLoi = 'Lỗi hệ thống, vui lòng thử lại sau.';
        }
    }
}

// ── XỬ LÝ CẬP NHẬT THÔNG TIN CÁ NHÂN (UC12) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'capNhatThongTin') {
    $hoTenMoi    = trim($_POST['hoTen'] ?? '');
    $sdtMoi      = trim($_POST['sdt'] ?? '');
    $matKhauMoi  = $_POST['matKhauMoi'] ?? '';
    $matKhauMoi2 = $_POST['matKhauMoi2'] ?? '';

    if (!$hoTenMoi || !$sdtMoi) {
        $thongBaoLoi = 'Vui lòng nhập đầy đủ thông tin hợp lệ.';
    } elseif ($matKhauMoi !== '' && $matKhauMoi !== $matKhauMoi2) {
        $thongBaoLoi = 'Mật khẩu xác nhận không khớp.';
    } elseif ($matKhauMoi !== '' && strlen($matKhauMoi) < 6) {
        $thongBaoLoi = 'Mật khẩu mới tối thiểu 6 ký tự.';
    } else {
        if ($matKhauMoi !== '') {
            // ĐÃ VÁ LỖI BẢO MẬT: Mã hóa Bcrypt trước khi lưu
            $matKhauHash = password_hash($matKhauMoi, PASSWORD_BCRYPT);
            $stmtU = $pdo->prepare("UPDATE tai_khoan SET HoTen = :ht, SDT = :sdt, MatKhau = :mk WHERE MaTK = :id");
            $stmtU->execute([':ht' => $hoTenMoi, ':sdt' => $sdtMoi, ':mk' => $matKhauHash, ':id' => $maTK]);
        } else {
            $stmtU = $pdo->prepare("UPDATE tai_khoan SET HoTen = :ht, SDT = :sdt WHERE MaTK = :id");
            $stmtU->execute([':ht' => $hoTenMoi, ':sdt' => $sdtMoi, ':id' => $maTK]);
        }
        $_SESSION['HoTen'] = $hoTenMoi;
        $thongBaoThanhCong = 'Cập nhật thông tin thành công!';
    }
}

$thongTinTK = $pdo->prepare("SELECT * FROM tai_khoan WHERE MaTK = ?"); $thongTinTK->execute([$maTK]); $thongTinTK = $thongTinTK->fetch();
$locTrangThai = trim($_GET['trangThai'] ?? '');
$sqlB = "SELECT ddp.*, p.SoPhong, lp.TenLoai, ks.TenKS FROM don_dat_phong ddp JOIN phong p ON ddp.MaPhong=p.MaPhong JOIN loai_phong lp ON p.MaLoai=lp.MaLoai JOIN khach_san ks ON p.MaKS=ks.MaKS WHERE ddp.MaTK = :id";
$paramsB = [':id' => $maTK];
if ($locTrangThai !== '') { $sqlB .= " AND ddp.TrangThaiDon = :tt"; $paramsB[':tt'] = $locTrangThai; }
$sqlB .= " ORDER BY ddp.NgayTao DESC";
$stmtB = $pdo->prepare($sqlB); $stmtB->execute($paramsB); $dsDonDat = $stmtB->fetchAll();

$thongKe = $pdo->prepare("SELECT COUNT(*) as tongDon, SUM(CASE WHEN TrangThaiDon='HoanTat' THEN 1 ELSE 0 END) as daHoanThanh, SUM(CASE WHEN TrangThaiDon IN ('ChoXacNhan','DaXacNhan') THEN 1 ELSE 0 END) as dangCho FROM don_dat_phong WHERE MaTK = ?");
$thongKe->execute([$maTK]); $thongKe = $thongKe->fetch();

$pageTitle = 'Luxury Hotel – Tài khoản'; $activePage = 'account';
require_once '../includes/head.php'; require_once '../includes/navbar.php';
?>

<div class="container my-4">
  <?php if ($thongBaoThanhCong): ?><div class="alert alert-success">✓ <?= h($thongBaoThanhCong) ?></div><?php endif; ?>
  <?php if ($thongBaoLoi): ?><div class="alert alert-danger">⚠ <?= h($thongBaoLoi) ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-12 col-lg-3">
      <div class="profile-sidebar border rounded p-4 text-center">
        <div class="avatar-lg mb-2"><?= h(layChuCaiDauTen($thongTinTK['HoTen'])) ?></div>
        <h3 class="font-playfair h5 mb-1" style="color:var(--navy)"><?= h($thongTinTK['HoTen']) ?></h3>
        <p class="small text-muted mb-3"><?= $thongTinTK['VaiTro'] ?></p>
        <div class="border-top pt-3 text-start small">
          <p class="mb-1">📱 <?= h($thongTinTK['SDT']) ?></p><p class="mb-3">✉️ <?= h($thongTinTK['Email']) ?></p>
          <a href="#tab-profile" class="btn btn-outline-navy btn-sm w-100 mb-2" data-bs-toggle="tab">Sửa thông tin</a>
          <a href="logout.php" class="btn btn-navy btn-sm w-100">Đăng xuất</a>
        </div>
      </div>
      <div class="bg-white border rounded p-3 mt-3 small">
        <div class="d-flex justify-content-between mb-2"><span>Tổng đơn</span><strong><?= (int)$thongKe['tongDon'] ?></strong></div>
        <div class="d-flex justify-content-between mb-2"><span>Hoàn thành</span><strong class="text-success"><?= (int)$thongKe['daHoanThanh'] ?></strong></div>
        <div class="d-flex justify-content-between"><span>Đang chờ</span><strong class="text-warning"><?= (int)$thongKe['dangCho'] ?></strong></div>
      </div>
    </div>

    <div class="col-12 col-lg-9">
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-bookings">📋 Đơn đặt phòng</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-profile">👤 Cá nhân</button></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-bookings">
          <?php foreach ($dsDonDat as $don):
              $soDem = tinhSoDem($don['NgayNhan'], $don['NgayTra']);
              $coTheHuy = in_array($don['TrangThaiDon'], ['ChoXacNhan', 'DaXacNhan']) && strtotime($don['NgayNhan']) > time();
          ?>
          <div class="border rounded p-3 mb-3 d-flex gap-3 flex-wrap align-items-center">
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between mb-1"><h6 class="mb-0" style="color:var(--navy)"><?= h($don['TenKS']) ?></h6> <?= getStatusBadgeDonHang($don['TrangThaiDon']) ?></div>
              <p class="small text-secondary mb-1">🛏️ Phòng <?= h($don['TenLoai']) ?> – <?= h($don['SoPhong']) ?> (<?= $soDem ?> đêm)</p>
              <p class="small text-muted mb-1">📅 <?= fmtNgay($don['NgayNhan']) ?> → <?= fmtNgay($don['NgayTra']) ?> | Mã: <strong><?= h($don['MaXacNhan']) ?></strong></p>
              <p class="fw-bold mb-0 text-navy"><?= fmtVND($don['TongTien']) ?></p>
            </div>
            <?php if ($coTheHuy): ?>
              <form method="POST" action="account.php" onsubmit="return confirm('Hủy đơn đặt phòng này?');">
                <input type="hidden" name="action" value="huyDatPhong"><input type="hidden" name="maDon" value="<?= $don['MaDon'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Hủy phòng</button>
              </form>
            <?php endif; ?>
          </div>
          <?php endforeach; if(empty($dsDonDat)) echo '<p class="text-muted small">Chưa có đơn hàng nào.</p>'; ?>
        </div>

        <div class="tab-pane fade" id="tab-profile">
          <form method="POST" action="account.php" class="bg-white border rounded p-4" style="max-width:500px">
            <input type="hidden" name="action" value="capNhatThongTin">
            <div class="mb-3"><label class="form-label small">Email</label><input class="form-control" value="<?= h($thongTinTK['Email']) ?>" disabled></div>
            <div class="mb-3"><label class="form-label small">Họ tên *</label><input class="form-control" name="hoTen" value="<?= h($thongTinTK['HoTen']) ?>" required></div>
            <div class="mb-3"><label class="form-label small">SĐT *</label><input class="form-control" name="sdt" value="<?= h($thongTinTK['SDT']) ?>" required></div>
            <div class="mb-3"><label class="form-label small">Mật khẩu mới</label><input class="form-control" type="password" name="matKhauMoi"></div>
            <div class="mb-3"><label class="form-label small">Xác nhận mật khẩu mới</label><input class="form-control" type="password" name="matKhauMoi2"></div>
            <button type="submit" class="btn btn-gold w-100">Lưu thay đổi</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>