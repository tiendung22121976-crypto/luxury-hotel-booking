<?php
/**
 * account.php
 * -----------------------------------------------------------
 * Trang Tài khoản của tôi (UC05: Xem lịch sử đặt phòng, UC10: Hủy đặt
 * phòng, UC12: Thay đổi thông tin cá nhân)
 * Chức năng:
 *  - Hiển thị thông tin cá nhân + thống kê số đơn đặt phòng.
 *  - Hiển thị danh sách lịch sử đặt phòng của CHÍNH tài khoản đang
 *    đăng nhập (kiểm tra phân quyền nghiêm ngặt theo MaTK trong session).
 *  - Xử lý hủy đặt phòng (cập nhật TrangThaiDon = 'DaHuy' + giải phóng
 *    phòng về 'Available' trong 1 Transaction để đảm bảo toàn vẹn).
 *  - Xử lý cập nhật thông tin cá nhân (Họ tên, SĐT, đổi mật khẩu).
 * -----------------------------------------------------------
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

yeuCauDangNhap(); // Bắt buộc đăng nhập mới được vào trang này

$maTK = $_SESSION['MaTK'];
$thongBaoLoi = '';
$thongBaoThanhCong = '';

// ── XỬ LÝ HỦY ĐẶT PHÒNG (UC10) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'huyDatPhong') {
    $maDon = trim($_POST['maDon'] ?? '');

    // Chỉ cho phép hủy đơn của CHÍNH tài khoản đang đăng nhập (Business Rule UC10)
    $stmtKT = $pdo->prepare("SELECT MaPhong, TrangThaiDon, NgayNhan FROM don_dat_phong WHERE MaDon = :maDon AND MaTK = :maTK");
    $stmtKT->bindParam(':maDon', $maDon);
    $stmtKT->bindParam(':maTK', $maTK);
    $stmtKT->execute();
    $don = $stmtKT->fetch();

    if (!$don) {
        $thongBaoLoi = 'Không tìm thấy đơn đặt phòng hoặc bạn không có quyền hủy đơn này.';
    } elseif (!in_array($don['TrangThaiDon'], ['ChoXacNhan', 'DaXacNhan'])) {
        $thongBaoLoi = 'Đơn đặt phòng này không còn ở trạng thái có thể hủy.';
    } else {
        try {
            // Dùng Transaction: cập nhật trạng thái đơn + giải phóng phòng phải
            // CÙNG THÀNH CÔNG hoặc CÙNG THẤT BẠI (Non-Functional Requirement UC10)
            $pdo->beginTransaction();

            $pdo->prepare("UPDATE don_dat_phong SET TrangThaiDon = 'DaHuy' WHERE MaDon = :maDon")
                ->execute([':maDon' => $maDon]);

            $pdo->prepare("UPDATE phong SET TrangThai = 'Available' WHERE MaPhong = :maPhong")
                ->execute([':maPhong' => $don['MaPhong']]);

            $pdo->commit();
            $thongBaoThanhCong = 'Hủy phòng thành công!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $thongBaoLoi = 'Hệ thống đang bận, thao tác hủy chưa thành công. Vui lòng thử lại sau.';
        }
    }
}

// ── XỬ LÝ CẬP NHẬT THÔNG TIN CÁ NHÂN (UC12) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'capNhatThongTin') {
    $hoTenMoi = trim($_POST['hoTen'] ?? '');
    $sdtMoi   = trim($_POST['sdt'] ?? '');
    $matKhauMoi  = $_POST['matKhauMoi'] ?? '';
    $matKhauMoi2 = $_POST['matKhauMoi2'] ?? '';

    if (!$hoTenMoi || !$sdtMoi) {
        $thongBaoLoi = 'Vui lòng nhập đầy đủ và thông tin hợp lệ.';
    } elseif ($matKhauMoi !== '' && $matKhauMoi !== $matKhauMoi2) {
        $thongBaoLoi = 'Mật khẩu xác nhận không khớp.';
    } elseif ($matKhauMoi !== '' && strlen($matKhauMoi) < 6) {
        $thongBaoLoi = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } else {
        if ($matKhauMoi !== '') {
            // Đổi cả thông tin + mật khẩu mới (lưu plain-text, không mã hóa)
            $matKhauMaHoa = $matKhauMoi;
            $stmtUpdate = $pdo->prepare("UPDATE tai_khoan SET HoTen = :hoTen, SDT = :sdt, MatKhau = :matKhau WHERE MaTK = :maTK");
            $stmtUpdate->bindParam(':matKhau', $matKhauMaHoa);
        } else {
            // Chỉ đổi Họ tên & SĐT, giữ nguyên mật khẩu (Email và MaTK không được đổi - Business Rule UC12)
            $stmtUpdate = $pdo->prepare("UPDATE tai_khoan SET HoTen = :hoTen, SDT = :sdt WHERE MaTK = :maTK");
        }
        $stmtUpdate->bindParam(':hoTen', $hoTenMoi);
        $stmtUpdate->bindParam(':sdt', $sdtMoi);
        $stmtUpdate->bindParam(':maTK', $maTK);
        $stmtUpdate->execute();

        $_SESSION['HoTen'] = $hoTenMoi; // Đồng bộ lại session để navbar/UI cập nhật ngay
        $thongBaoThanhCong = 'Cập nhật thông tin thành công!';
    }
}

// ── LẤY THÔNG TIN TÀI KHOẢN HIỆN TẠI ──
$stmt = $pdo->prepare("SELECT MaTK, HoTen, Email, SDT, VaiTro FROM tai_khoan WHERE MaTK = :maTK");
$stmt->bindParam(':maTK', $maTK);
$stmt->execute();
$thongTinTK = $stmt->fetch();

// ── LẤY LỊCH SỬ ĐẶT PHÒNG CỦA TÀI KHOẢN (sắp xếp mới nhất trước - Business Rule UC05) ──
$locTrangThai = trim($_GET['trangThai'] ?? '');
$sqlBooking = "
    SELECT ddp.MaDon, ddp.MaXacNhan, ddp.NgayNhan, ddp.NgayTra, ddp.TongTien, ddp.TrangThaiDon, ddp.NgayTao,
           p.MaPhong, p.SoPhong, lp.TenLoai, ks.TenKS
    FROM don_dat_phong ddp
    INNER JOIN phong p ON ddp.MaPhong = p.MaPhong
    INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
    WHERE ddp.MaTK = :maTK
";
$paramsBooking = [':maTK' => $maTK];
if ($locTrangThai !== '') {
    $sqlBooking .= " AND ddp.TrangThaiDon = :trangThai ";
    $paramsBooking[':trangThai'] = $locTrangThai;
}
$sqlBooking .= " ORDER BY ddp.NgayTao DESC ";

$stmtBooking = $pdo->prepare($sqlBooking);
$stmtBooking->execute($paramsBooking);
$dsDonDat = $stmtBooking->fetchAll();

// ── THỐNG KÊ NHANH ──
$stmtStats = $pdo->prepare("
    SELECT
      COUNT(*) AS tongDon,
      SUM(CASE WHEN TrangThaiDon = 'HoanTat' THEN 1 ELSE 0 END) AS daHoanThanh,
      SUM(CASE WHEN TrangThaiDon IN ('ChoXacNhan','DaXacNhan') THEN 1 ELSE 0 END) AS dangCho
    FROM don_dat_phong WHERE MaTK = :maTK
");
$stmtStats->bindParam(':maTK', $maTK);
$stmtStats->execute();
$thongKe = $stmtStats->fetch();

$pageTitle = 'Luxury Hotel – Tài khoản của tôi';
$activePage = 'account';
require_once '../includes/head.php';
require_once '../includes/navbar.php';
?>

<div class="container my-4">
  <?php if ($thongBaoThanhCong): ?><div class="alert alert-success">✓ <?= h($thongBaoThanhCong) ?></div><?php endif; ?>
  <?php if ($thongBaoLoi): ?><div class="alert alert-danger">⚠ <?= h($thongBaoLoi) ?></div><?php endif; ?>

  <div class="row g-4">
    <!-- ═══ CỘT TRÁI: Thông tin & thống kê ═══ -->
    <div class="col-12 col-lg-3">
      <div class="profile-sidebar border rounded p-4 text-center">
        <div class="avatar-lg mb-2">
          <?= h(layChuCaiDauTen($thongTinTK['HoTen'])) ?>
        </div>
        <h3 class="font-playfair h5 mb-1" style="color:var(--navy)"><?= h($thongTinTK['HoTen']) ?></h3>
        <p class="small text-muted mb-3"><?= $thongTinTK['VaiTro'] === 'Admin' ? 'Quản trị viên' : 'Khách hàng thân thiết' ?></p>
        <div class="border-top pt-3 text-start">
          <p class="small text-muted mb-1">Thông tin cá nhân</p>
          <p class="small mb-1">📱 <?= h($thongTinTK['SDT']) ?></p>
          <p class="small mb-3">✉️ <?= h($thongTinTK['Email']) ?></p>
          <a href="#tab-profile" class="btn btn-outline-navy btn-sm w-100 mb-2" data-bs-toggle="tab">Thay đổi thông tin</a>
          <a href="logout.php" class="btn btn-navy btn-sm w-100">Đăng xuất</a>
        </div>
      </div>

      <div class="bg-white border rounded p-3 mt-3">
        <p class="small text-uppercase fw-medium mb-2" style="color:var(--gold)">Thống kê của bạn</p>
        <div class="d-flex justify-content-between small mb-2"><span class="text-secondary">Tổng đơn đặt</span><span class="fw-bold" style="color:var(--navy)"><?= (int)$thongKe['tongDon'] ?></span></div>
        <div class="d-flex justify-content-between small mb-2"><span class="text-secondary">Đã hoàn thành</span><span class="fw-bold text-success"><?= (int)$thongKe['daHoanThanh'] ?></span></div>
        <div class="d-flex justify-content-between small"><span class="text-secondary">Đang chờ</span><span class="fw-bold text-warning"><?= (int)$thongKe['dangCho'] ?></span></div>
      </div>
    </div>

    <!-- ═══ CỘT PHẢI: Tabs ═══ -->
    <div class="col-12 col-lg-9">
      <ul class="nav nav-tabs mb-3" id="accountTabs">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-bookings">📋 Lịch sử đặt phòng</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-profile">👤 Thông tin cá nhân</button></li>
      </ul>

      <div class="tab-content">
        <!-- TAB: Lịch sử đặt phòng -->
        <div class="tab-pane fade show active" id="tab-bookings">
          <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
            <h2 class="font-playfair h4 mb-0" style="color:var(--navy)">Đơn đặt phòng của tôi</h2>
            <form method="GET" action="account.php">
              <select name="trangThai" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
                <option value="">Tất cả trạng thái</option>
                <option value="DaXacNhan" <?= $locTrangThai === 'DaXacNhan' ? 'selected' : '' ?>>Đã xác nhận</option>
                <option value="HoanTat" <?= $locTrangThai === 'HoanTat' ? 'selected' : '' ?>>Hoàn tất</option>
                <option value="DaHuy" <?= $locTrangThai === 'DaHuy' ? 'selected' : '' ?>>Đã hủy</option>
              </select>
            </form>
          </div>

          <?php if (count($dsDonDat) === 0): ?>
            <div class="text-center text-muted py-5">
              <div class="fs-1 mb-2">📭</div>
              <h5 style="color:var(--navy)">Chưa có đơn đặt phòng</h5>
              <p>Bạn chưa có đơn đặt phòng nào. <a href="search.php">Tìm phòng ngay →</a></p>
            </div>
          <?php endif; ?>

          <?php foreach ($dsDonDat as $don):
              $soDem = tinhSoDem($don['NgayNhan'], $don['NgayTra']);
              $coTheHuy = in_array($don['TrangThaiDon'], ['ChoXacNhan', 'DaXacNhan']) && strtotime($don['NgayNhan']) > time();
          ?>
          <div class="booking-history-card border rounded p-3 mb-3 d-flex gap-3 flex-wrap">
            <div class="booking-history-img rounded" style="width:100px;height:80px">🏨</div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between flex-wrap gap-2 mb-1">
                <h6 class="mb-0" style="color:var(--navy)"><?= h($don['TenKS']) ?></h6>
                <?= getStatusBadgeDonHang($don['TrangThaiDon']) ?>
              </div>
              <p class="small text-secondary mb-1">🛏️ Phòng <?= h($don['TenLoai']) ?> – <?= h($don['SoPhong']) ?></p>
              <p class="small text-muted mb-1">📅 <?= fmtNgay($don['NgayNhan']) ?> đến <?= fmtNgay($don['NgayTra']) ?>
                <span class="badge rounded-pill" style="background:var(--gold-pale);color:var(--navy)"><?= $soDem ?> đêm</span>
              </p>
              <p class="small text-muted mb-1">Mã xác nhận: <strong class="font-playfair" style="color:var(--navy)"><?= h($don['MaXacNhan']) ?></strong></p>
              <p class="fw-bold mb-0" style="color:var(--navy)"><?= fmtVND($don['TongTien']) ?></p>
            </div>
            <div class="d-flex flex-column gap-1 align-items-end">
              <?php if ($coTheHuy): ?>
                <form method="POST" action="account.php" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn đặt phòng này? Sau khi hủy không thể khôi phục.');">
                  <input type="hidden" name="action" value="huyDatPhong">
                  <input type="hidden" name="maDon" value="<?= h($don['MaDon']) ?>">
                  <button type="submit" class="btn btn-danger btn-sm">HỦY PHÒNG</button>
                </form>
              <?php elseif ($don['TrangThaiDon'] !== 'DaHuy'): ?>
                <span class="small text-muted">Đã qua hạn hủy</span>
              <?php endif; ?>
              <button type="button" class="btn btn-outline-navy btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetail<?= h($don['MaDon']) ?>">XEM CHI TIẾT</button>
            </div>
          </div>

          <!-- Modal chi tiết đơn hàng -->
          <div class="modal fade" id="modalDetail<?= h($don['MaDon']) ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title font-playfair" style="color:var(--navy)">Chi tiết đơn đặt phòng</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                  <div class="text-center text-white rounded p-3 mb-3" style="background:var(--navy)">
                    <p class="small mb-1" style="color:var(--gold-light)">Mã đặt phòng</p>
                    <div class="font-playfair fs-3" style="letter-spacing:2px"><?= h($don['MaXacNhan']) ?></div>
                  </div>
                  <div class="row g-2 small mb-2">
                    <div class="col-6"><span class="text-muted">Khách sạn</span><br><strong><?= h($don['TenKS']) ?></strong></div>
                    <div class="col-6"><span class="text-muted">Loại phòng</span><br><strong><?= h($don['TenLoai']) ?></strong></div>
                    <div class="col-6"><span class="text-muted">Nhận phòng</span><br><strong><?= fmtNgay($don['NgayNhan']) ?> (14:00)</strong></div>
                    <div class="col-6"><span class="text-muted">Trả phòng</span><br><strong><?= fmtNgay($don['NgayTra']) ?> (12:00)</strong></div>
                  </div>
                  <hr>
                  <div class="d-flex justify-content-between small"><span>Số đêm</span><span><?= $soDem ?> đêm</span></div>
                  <div class="d-flex justify-content-between fw-bold" style="color:var(--navy)"><span>Tổng thanh toán</span><span><?= fmtVND($don['TongTien']) ?></span></div>
                  <div class="text-center mt-3"><?= getStatusBadgeDonHang($don['TrangThaiDon']) ?></div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- TAB: Thông tin cá nhân -->
        <div class="tab-pane fade" id="tab-profile">
          <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Thông tin cá nhân</h2>
          <div class="bg-white border rounded p-4" style="max-width:560px">
            <form method="POST" action="account.php">
              <input type="hidden" name="action" value="capNhatThongTin">
              <div class="mb-3">
                <label class="form-label small text-uppercase text-muted">Mã tài khoản</label>
                <input class="form-control" value="<?= h($thongTinTK['MaTK']) ?>" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label small text-uppercase text-muted">Email</label>
                <input class="form-control" value="<?= h($thongTinTK['Email']) ?>" disabled>
                <div class="form-text">Email đăng nhập không thể thay đổi</div>
              </div>
              <div class="mb-3">
                <label class="form-label small text-uppercase text-muted">Họ và tên</label>
                <input class="form-control" name="hoTen" value="<?= h($thongTinTK['HoTen']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label small text-uppercase text-muted">Số điện thoại</label>
                <input class="form-control" name="sdt" value="<?= h($thongTinTK['SDT']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label small text-uppercase text-muted">Mật khẩu mới (để trống nếu không đổi)</label>
                <input class="form-control" type="password" name="matKhauMoi" placeholder="••••••••">
              </div>
              <div class="mb-3">
                <label class="form-label small text-uppercase text-muted">Xác nhận mật khẩu mới</label>
                <input class="form-control" type="password" name="matKhauMoi2" placeholder="••••••••">
              </div>
              <button type="submit" class="btn btn-gold w-100">Lưu thay đổi</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
