<?php
/**
 * admin.php
 * -----------------------------------------------------------
 * Trang Quản trị (UC06: Quản lý tài khoản, UC07: Quản lý khách sạn,
 * UC08: Quản lý khuyến mãi, UC09: Quản lý phòng)
 * Chức năng:
 *  - Chỉ Admin (VaiTro = 'Admin') mới được truy cập.
 *  - Điều hướng giữa các trang con bằng tham số GET `page`
 *    (dashboard | rooms | promotions | hotels | accounts).
 *  - Mỗi hành động thêm/xóa được xử lý bằng POST trong CHÍNH file này,
 *    áp dụng đầy đủ ràng buộc nghiệp vụ (không xóa được khi đang có
 *    dữ liệu liên quan đang hoạt động).
 * -----------------------------------------------------------
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../models/mdl_khach_san.php';
require_once '../models/mdl_phong.php';
require_once '../models/mdl_khuyen_mai.php';

yeuCauAdmin(); // Chỉ Admin mới được vào trang quản trị

$trang = $_GET['page'] ?? 'dashboard';
$thongBaoLoi = '';
$thongBaoThanhCong = '';

// ════════════════════════════════════════════════════════════
// XỬ LÝ CÁC HÀNH ĐỘNG (POST) - CHẠY TRƯỚC KHI RENDER GIAO DIỆN
// ════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = $_POST['action'] ?? '';

    // ── THÊM PHÒNG MỚI (UC09) ──
    if ($hanhDong === 'themPhong') {
        $maPhong  = trim($_POST['maPhong'] ?? '');
        $soPhong  = trim($_POST['soPhong'] ?? '');
        $maKS     = trim($_POST['maKS'] ?? '');
        $maLoai   = trim($_POST['maLoai'] ?? '');
        $trangThai = trim($_POST['trangThai'] ?? 'Available');

        if (!$maPhong || !$soPhong || !$maKS || !$maLoai) {
            $thongBaoLoi = 'Vui lòng điền đầy đủ thông tin phòng (Mã phòng, Số phòng, Khách sạn, Loại phòng).';
        } else {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM phong WHERE MaPhong = :maPhong");
            $stmtCheck->bindParam(':maPhong', $maPhong);
            $stmtCheck->execute();
            if ((int)$stmtCheck->fetchColumn() > 0) {
                $thongBaoLoi = "Mã phòng \"$maPhong\" đã tồn tại trong hệ thống.";
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO phong (MaPhong, SoPhong, MaKS, MaLoai, TrangThai) VALUES (:maPhong, :soPhong, :maKS, :maLoai, :trangThai)");
                $stmtInsert->execute([':maPhong' => $maPhong, ':soPhong' => $soPhong, ':maKS' => $maKS, ':maLoai' => $maLoai, ':trangThai' => $trangThai]);
                $thongBaoThanhCong = "Thêm phòng $soPhong thành công!";
            }
        }
    }

    // ── XÓA PHÒNG (UC09: chỉ xóa khi không có đơn đặt phòng đang hoạt động) ──
    if ($hanhDong === 'xoaPhong') {
        $maPhong = trim($_POST['maPhong'] ?? '');
        $stmtCheck = $pdo->prepare("
            SELECT COUNT(*) FROM don_dat_phong
            WHERE MaPhong = :maPhong AND TrangThaiDon IN ('ChoXacNhan','DaXacNhan')
        ");
        $stmtCheck->bindParam(':maPhong', $maPhong);
        $stmtCheck->execute();
        if ((int)$stmtCheck->fetchColumn() > 0) {
            $thongBaoLoi = 'Không thể xóa phòng vì còn đơn đặt phòng đang hoạt động.';
        } else {
            $pdo->prepare("DELETE FROM phong WHERE MaPhong = :maPhong")->execute([':maPhong' => $maPhong]);
            $thongBaoThanhCong = 'Xóa phòng thành công.';
        }
    }

    // ── THÊM KHÁCH SẠN MỚI (UC07) ──
    if ($hanhDong === 'themKhachSan') {
        $maKS = trim($_POST['maKS'] ?? '');
        $tenKS = trim($_POST['tenKS'] ?? '');
        $diaChi = trim($_POST['diaChi'] ?? '');
        $moTa = trim($_POST['moTa'] ?? '');

        if (!$maKS || !$tenKS || !$diaChi) {
            $thongBaoLoi = 'Vui lòng nhập đầy đủ Mã khách sạn, Tên khách sạn và Địa chỉ.';
        } else {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM khach_san WHERE MaKS = :maKS OR DiaChi = :diaChi");
            $stmtCheck->execute([':maKS' => $maKS, ':diaChi' => $diaChi]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                $thongBaoLoi = 'Mã khách sạn hoặc địa chỉ này đã tồn tại.';
            } else {
                $pdo->prepare("INSERT INTO khach_san (MaKS, TenKS, DiaChi, MoTa) VALUES (:maKS, :tenKS, :diaChi, :moTa)")
                    ->execute([':maKS' => $maKS, ':tenKS' => $tenKS, ':diaChi' => $diaChi, ':moTa' => $moTa]);
                $thongBaoThanhCong = 'Thêm khách sạn thành công!';
            }
        }
    }

    // ── XÓA KHÁCH SẠN (UC07: chỉ xóa khi không còn phòng nào thuộc khách sạn) ──
    if ($hanhDong === 'xoaKhachSan') {
        $maKS = trim($_POST['maKS'] ?? '');
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM phong WHERE MaKS = :maKS");
        $stmtCheck->bindParam(':maKS', $maKS);
        $stmtCheck->execute();
        if ((int)$stmtCheck->fetchColumn() > 0) {
            $thongBaoLoi = 'Không thể xóa khách sạn vì có phòng đang hoạt động.';
        } else {
            $pdo->prepare("DELETE FROM khach_san WHERE MaKS = :maKS")->execute([':maKS' => $maKS]);
            $thongBaoThanhCong = 'Xóa khách sạn thành công.';
        }
    }

    // ── THÊM KHUYẾN MÃI (UC08) ──
    if ($hanhDong === 'themKhuyenMai') {
        $maKM = trim($_POST['maKM'] ?? '');
        $tenKM = trim($_POST['tenKM'] ?? '');
        $phanTramGiam = (int)($_POST['phanTramGiam'] ?? 0);
        $ngayBatDau = trim($_POST['ngayBatDau'] ?? '');
        $ngayKetThuc = trim($_POST['ngayKetThuc'] ?? '');

        if (!$maKM || !$tenKM || !$phanTramGiam) {
            $thongBaoLoi = 'Vui lòng điền đầy đủ Mã code, Tên chương trình và % giảm giá.';
        } elseif ($phanTramGiam < 1 || $phanTramGiam > 100) {
            $thongBaoLoi = '% giảm giá phải là số nguyên từ 1 đến 100.';
        } elseif ($ngayBatDau && $ngayKetThuc && strtotime($ngayKetThuc) < strtotime($ngayBatDau)) {
            $thongBaoLoi = 'Ngày kết thúc không hợp lệ (phải lớn hơn hoặc bằng ngày bắt đầu).';
        } else {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM khuyen_mai WHERE MaKM = :maKM");
            $stmtCheck->bindParam(':maKM', $maKM);
            $stmtCheck->execute();
            if ((int)$stmtCheck->fetchColumn() > 0) {
                $thongBaoLoi = "Mã code \"$maKM\" đã tồn tại.";
            } else {
                $pdo->prepare("INSERT INTO khuyen_mai (MaKM, TenKM, PhanTramGiam, NgayBatDau, NgayKetThuc) VALUES (:maKM, :tenKM, :phanTramGiam, :ngayBatDau, :ngayKetThuc)")
                    ->execute([':maKM' => $maKM, ':tenKM' => $tenKM, ':phanTramGiam' => $phanTramGiam, ':ngayBatDau' => $ngayBatDau ?: null, ':ngayKetThuc' => $ngayKetThuc ?: null]);
                $thongBaoThanhCong = 'Thêm khuyến mãi thành công!';
            }
        }
    }

    // ── XÓA KHUYẾN MÃI (UC08) ──
    if ($hanhDong === 'xoaKhuyenMai') {
        $maKM = trim($_POST['maKM'] ?? '');
        $pdo->prepare("DELETE FROM khuyen_mai WHERE MaKM = :maKM")->execute([':maKM' => $maKM]);
        $thongBaoThanhCong = 'Xóa khuyến mãi thành công.';
    }

    // ── THÊM TÀI KHOẢN (UC06 - admin tạo tài khoản nhân viên/khách hàng) ──
    if ($hanhDong === 'themTaiKhoan') {
        $hoTen = trim($_POST['hoTen'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sdt   = trim($_POST['sdt'] ?? '');
        $vaiTro = trim($_POST['vaiTro'] ?? 'ThanhVien');
        $matKhau = $_POST['matKhau'] ?? '';

        if (!$hoTen || !$email || !$sdt || strlen($matKhau) < 6) {
            $thongBaoLoi = 'Vui lòng điền đầy đủ thông tin (mật khẩu tối thiểu 6 ký tự).';
        } else {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM tai_khoan WHERE Email = :email OR SDT = :sdt");
            $stmtCheck->execute([':email' => $email, ':sdt' => $sdt]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                $thongBaoLoi = 'Email hoặc số điện thoại đã được sử dụng.';
            } else {
                $matKhauMaHoa = $matKhau;
                $pdo->prepare("INSERT INTO tai_khoan (HoTen, Email, SDT, MatKhau, VaiTro) VALUES (:hoTen, :email, :sdt, :matKhau, :vaiTro)")
                    ->execute([':hoTen' => $hoTen, ':email' => $email, ':sdt' => $sdt, ':matKhau' => $matKhauMaHoa, ':vaiTro' => $vaiTro]);
                $thongBaoThanhCong = 'Tạo tài khoản thành công!';
            }
        }
    }

    // ── XÓA TÀI KHOẢN (UC06: không tự xóa chính mình, không xóa khi còn đơn hoạt động) ──
    if ($hanhDong === 'xoaTaiKhoan') {
        $maTKXoa = trim($_POST['maTK'] ?? '');
        if ((int)$maTKXoa === (int)$_SESSION['MaTK']) {
            $thongBaoLoi = 'Bạn không thể tự xóa chính tài khoản mình đang đăng nhập.';
        } else {
            $stmtCheck = $pdo->prepare("
                SELECT COUNT(*) FROM don_dat_phong
                WHERE MaTK = :maTK AND TrangThaiDon IN ('ChoXacNhan','DaXacNhan')
            ");
            $stmtCheck->bindParam(':maTK', $maTKXoa);
            $stmtCheck->execute();
            if ((int)$stmtCheck->fetchColumn() > 0) {
                $thongBaoLoi = 'Không thể xóa tài khoản này vì còn đơn đặt phòng đang hoạt động.';
            } else {
                $pdo->prepare("DELETE FROM tai_khoan WHERE MaTK = :maTK")->execute([':maTK' => $maTKXoa]);
                $thongBaoThanhCong = 'Xóa tài khoản thành công.';
            }
        }
    }
}

// ════════════════════════════════════════════════════════════
// TRUY VẤN DỮ LIỆU HIỂN THỊ THEO TRANG ĐANG XEM
// ════════════════════════════════════════════════════════════

// Thống kê tổng quan cho Dashboard
$thongKePhong = $pdo->query("
    SELECT
      SUM(CASE WHEN TrangThai = 'Available' THEN 1 ELSE 0 END) AS trong,
      SUM(CASE WHEN TrangThai = 'Occupied' THEN 1 ELSE 0 END) AS coKhach,
      SUM(CASE WHEN TrangThai = 'Reserved' THEN 1 ELSE 0 END) AS daDat,
      COUNT(*) AS tongPhong
    FROM phong
")->fetch();
$tongKhachHang = (int)$pdo->query("SELECT COUNT(*) FROM tai_khoan WHERE VaiTro != 'Admin'")->fetchColumn();

// Danh sách đơn đặt phòng cho Dashboard
$dsDonDashboard = $pdo->query("
    SELECT ddp.MaDon, ddp.MaXacNhan, ddp.TrangThaiDon, tk.HoTen, lp.TenLoai
    FROM don_dat_phong ddp
    INNER JOIN tai_khoan tk ON ddp.MaTK = tk.MaTK
    INNER JOIN phong p ON ddp.MaPhong = p.MaPhong
    INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    ORDER BY ddp.NgayTao DESC LIMIT 50
")->fetchAll();

// Danh sách phòng (trang Quản Lý Phòng)
$dsPhongAdmin = $pdo->query("
    SELECT p.MaPhong, p.SoPhong, p.TrangThai, lp.TenLoai, lp.TienIch, ks.TenKS
    FROM phong p
    INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
    ORDER BY ks.TenKS, p.SoPhong
")->fetchAll();
$dsLoaiPhong = $pdo->query("SELECT MaLoai, TenLoai FROM loai_phong")->fetchAll();
$dsKhachSanChonLua = $pdo->query("SELECT MaKS, TenKS FROM khach_san")->fetchAll();

// Danh sách khuyến mãi (trang Quản Lý Khuyến Mãi)
$dsKhuyenMaiAdmin = $pdo->query("SELECT * FROM khuyen_mai ORDER BY NgayBatDau DESC")->fetchAll();
$soPhieuConHan = $pdo->query("SELECT COUNT(*) FROM khuyen_mai WHERE NgayKetThuc >= CURDATE()")->fetchColumn();
$soPhieuHetHan = $pdo->query("SELECT COUNT(*) FROM khuyen_mai WHERE NgayKetThuc < CURDATE()")->fetchColumn();

// Danh sách khách sạn (trang Thông Tin Khách Sạn)
$dsKhachSanAdmin = $pdo->query("
    SELECT ks.MaKS, ks.TenKS, ks.DiaChi, ks.MoTa,
           (SELECT COUNT(*) FROM phong WHERE MaKS = ks.MaKS) AS soPhong
    FROM khach_san ks ORDER BY ks.TenKS
")->fetchAll();

// Danh sách tài khoản (trang Quản Lý Tài Khoản)
$dsTaiKhoanAdmin = $pdo->query("SELECT MaTK, HoTen, Email, SDT, VaiTro FROM tai_khoan ORDER BY MaTK")->fetchAll();
$soAdmin = $pdo->query("SELECT COUNT(*) FROM tai_khoan WHERE VaiTro = 'Admin'")->fetchColumn();
$soThanhVien = $pdo->query("SELECT COUNT(*) FROM tai_khoan WHERE VaiTro = 'ThanhVien'")->fetchColumn();

$pageTitle = 'Luxury Hotel – Quản trị';
require_once '../includes/head.php';
?>
<style>
.admin-nav-item.active { background: rgba(201,168,76,0.15); color: var(--gold) !important; }
</style>

<!-- Top navbar riêng cho trang Admin -->
<nav class="navbar navbar-luxury">
  <div class="container-fluid px-3">
    <a href="index.php" class="navbar-brand-luxury">LUXURY <span>HOTEL</span></a>
    <div class="d-flex align-items-center gap-3">
      <span class="small" style="color:var(--gold-light)">🛡️ Quản trị viên: <?= h($_SESSION['HoTen']) ?></span>
      <a href="logout.php" class="btn btn-sm" style="border:1px solid rgba(201,168,76,0.4);color:#E0DDD5">Đăng xuất</a>
    </div>
  </div>
</nav>

<div class="d-flex flex-wrap flex-lg-nowrap">
  <!-- SIDEBAR -->
  <nav class="admin-sidebar p-2">
    <a class="admin-nav-item <?= $trang === 'dashboard' ? 'active' : '' ?>" href="admin.php?page=dashboard">🏠 Trang Chủ</a>
    <a class="admin-nav-item <?= $trang === 'rooms' ? 'active' : '' ?>" href="admin.php?page=rooms">🏨 Quản Lý Phòng</a>
    <a class="admin-nav-item <?= $trang === 'promotions' ? 'active' : '' ?>" href="admin.php?page=promotions">🎁 Quản Lý Khuyến Mãi</a>
    <a class="admin-nav-item <?= $trang === 'hotels' ? 'active' : '' ?>" href="admin.php?page=hotels">📍 Thông Tin Khách Sạn</a>
    <a class="admin-nav-item <?= $trang === 'accounts' ? 'active' : '' ?>" href="admin.php?page=accounts">👤 Quản Lý Tài Khoản</a>
    <hr class="border-secondary">
    <a class="admin-nav-item" href="index.php">← Về trang khách hàng</a>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="flex-fill p-3 p-md-4">

    <?php if ($thongBaoThanhCong): ?><div class="alert alert-success">✓ <?= h($thongBaoThanhCong) ?></div><?php endif; ?>
    <?php if ($thongBaoLoi): ?><div class="alert alert-danger">⚠ <?= h($thongBaoLoi) ?></div><?php endif; ?>

    <?php if ($trang === 'dashboard'): ?>
      <!-- ═══════════════ TRANG CHỦ (DASHBOARD) ═══════════════ -->
      <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Trang Chủ</h2>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="stat-card p-3"><div class="stat-label">Phòng Trống</div><div class="stat-value text-success"><?= (int)$thongKePhong['trong'] ?></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card p-3" style="border-top-color:#C0392B"><div class="stat-label">Phòng Có Khách</div><div class="stat-value text-danger"><?= (int)$thongKePhong['coKhach'] ?></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card p-3" style="border-top-color:#B7791F"><div class="stat-label">Phòng Đã Đặt</div><div class="stat-value text-warning"><?= (int)$thongKePhong['daDat'] ?></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card p-3" style="border-top-color:#1E5FA3"><div class="stat-label">Số Lượng Khách</div><div class="stat-value"><?= $tongKhachHang ?></div></div></div>
      </div>

      <div class="bg-white border rounded">
        <div class="p-3 border-bottom"><h3 class="h6 mb-0 font-playfair" style="color:var(--navy)">Danh sách đặt phòng</h3></div>
        <div class="table-responsive">
          <table class="table table-luxury mb-0">
            <thead><tr><th>STT</th><th>Mã Xác Nhận</th><th>Tên Khách</th><th>Loại Phòng</th><th>Trạng Thái</th></tr></thead>
            <tbody>
              <?php foreach ($dsDonDashboard as $i => $don): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= h($don['MaXacNhan']) ?></td>
                <td><?= h($don['HoTen']) ?></td>
                <td><?= h($don['TenLoai']) ?></td>
                <td><?= getStatusBadgeDonHang($don['TrangThaiDon']) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (count($dsDonDashboard) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Chưa có đơn đặt phòng nào.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($trang === 'rooms'): ?>
      <!-- ═══════════════ QUẢN LÝ PHÒNG (UC09) ═══════════════ -->
      <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Quản Lý Phòng</h2>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-4"><div class="stat-card p-3"><div class="stat-label">Số Lượng Phòng</div><div class="stat-value"><?= count($dsPhongAdmin) ?></div></div></div>
        <div class="col-6 col-md-4"><div class="stat-card p-3"><div class="stat-label">Loại phòng</div><div class="stat-value"><?= count($dsLoaiPhong) ?></div></div></div>
        <div class="col-12 col-md-4"><div class="stat-card p-3"><div class="stat-label">Chi nhánh</div><div class="stat-value"><?= count($dsKhachSanChonLua) ?></div></div></div>
      </div>

      <div class="bg-white border rounded">
        <div class="p-3 border-bottom d-flex justify-content-between flex-wrap gap-2">
          <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#modalThemPhong">+ Thêm phòng</button>
        </div>
        <div class="table-responsive">
          <table class="table table-luxury mb-0">
            <thead><tr><th>Mã Phòng</th><th>Số Phòng</th><th>Khách Sạn</th><th>Loại Phòng</th><th>Tiện Ích</th><th>Trạng Thái</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($dsPhongAdmin as $p): ?>
              <tr>
                <td><?= h($p['MaPhong']) ?></td>
                <td><?= h($p['SoPhong']) ?></td>
                <td><?= h($p['TenKS']) ?></td>
                <td><?= h($p['TenLoai']) ?></td>
                <td class="small"><?= h($p['TienIch']) ?></td>
                <td><?= getStatusBadgePhong($p['TrangThai']) ?></td>
                <td>
                  <form method="POST" action="admin.php?page=rooms" onsubmit="return confirm('Xóa phòng <?= h($p['SoPhong']) ?>?');" class="d-inline">
                    <input type="hidden" name="action" value="xoaPhong">
                    <input type="hidden" name="maPhong" value="<?= h($p['MaPhong']) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑 Xóa</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal thêm phòng -->
      <div class="modal fade" id="modalThemPhong" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="admin.php?page=rooms">
              <div class="modal-header"><h5 class="modal-title font-playfair" style="color:var(--navy)">Thêm phòng mới</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <input type="hidden" name="action" value="themPhong">
                <div class="row g-2">
                  <div class="col-6"><label class="form-label small">Mã Phòng *</label><input class="form-control" name="maPhong" placeholder="VD: H301" required></div>
                  <div class="col-6"><label class="form-label small">Số Phòng *</label><input class="form-control" name="soPhong" placeholder="VD: 301" required></div>
                </div>
                <div class="mb-2 mt-2">
                  <label class="form-label small">Chi Nhánh / Khách Sạn *</label>
                  <select class="form-select" name="maKS" required>
                    <option value="">-- Chọn khách sạn --</option>
                    <?php foreach ($dsKhachSanChonLua as $ks): ?>
                      <option value="<?= h($ks['MaKS']) ?>"><?= h($ks['TenKS']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-2">
                  <label class="form-label small">Loại Phòng *</label>
                  <select class="form-select" name="maLoai" required>
                    <option value="">-- Chọn loại --</option>
                    <?php foreach ($dsLoaiPhong as $lp): ?>
                      <option value="<?= h($lp['MaLoai']) ?>"><?= h($lp['TenLoai']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label class="form-label small">Trạng Thái</label>
                  <select class="form-select" name="trangThai">
                    <option value="Available">Trống</option>
                    <option value="Reserved">Đã đặt</option>
                    <option value="Occupied">Đang sử dụng</option>
                    <option value="Cleaning">Đang dọn dẹp</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-gold">Lưu phòng</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php elseif ($trang === 'promotions'): ?>
      <!-- ═══════════════ QUẢN LÝ KHUYẾN MÃI (UC08) ═══════════════ -->
      <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Quản Lý Khuyến Mãi</h2>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="stat-card p-3"><div class="stat-label">Còn Hạn</div><div class="stat-value text-success"><?= (int)$soPhieuConHan ?></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card p-3" style="border-top-color:#C0392B"><div class="stat-label">Hết Hạn</div><div class="stat-value text-danger"><?= (int)$soPhieuHetHan ?></div></div></div>
        <div class="col-12 col-md-6"><div class="stat-card p-3"><div class="stat-label">Tổng Số Phiếu</div><div class="stat-value"><?= count($dsKhuyenMaiAdmin) ?></div></div></div>
      </div>

      <div class="bg-white border rounded">
        <div class="p-3 border-bottom"><button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#modalThemKM">+ Thêm khuyến mãi</button></div>
        <div class="table-responsive">
          <table class="table table-luxury mb-0">
            <thead><tr><th>Mã Phiếu</th><th>Tên Chương Trình</th><th>% Giảm</th><th>Bắt Đầu</th><th>Kết Thúc</th><th>Trạng Thái</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($dsKhuyenMaiAdmin as $km):
                  $conHan = strtotime($km['NgayKetThuc']) >= time();
              ?>
              <tr>
                <td><?= h($km['MaKM']) ?></td>
                <td><?= h($km['TenKM']) ?></td>
                <td><?= (int)$km['PhanTramGiam'] ?>%</td>
                <td><?= fmtNgay($km['NgayBatDau']) ?></td>
                <td><?= fmtNgay($km['NgayKetThuc']) ?></td>
                <td><span class="badge <?= $conHan ? 'bg-success' : 'bg-secondary' ?>"><?= $conHan ? 'Còn hạn' : 'Hết hạn' ?></span></td>
                <td>
                  <form method="POST" action="admin.php?page=promotions" onsubmit="return confirm('Xóa khuyến mãi <?= h($km['MaKM']) ?>?');" class="d-inline">
                    <input type="hidden" name="action" value="xoaKhuyenMai">
                    <input type="hidden" name="maKM" value="<?= h($km['MaKM']) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal thêm khuyến mãi -->
      <div class="modal fade" id="modalThemKM" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="admin.php?page=promotions">
              <div class="modal-header"><h5 class="modal-title font-playfair" style="color:var(--navy)">Thêm khuyến mãi mới</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <input type="hidden" name="action" value="themKhuyenMai">
                <div class="row g-2">
                  <div class="col-6"><label class="form-label small">Mã Phiếu *</label><input class="form-control" name="maKM" placeholder="VD: SUMMER27" required></div>
                  <div class="col-6"><label class="form-label small">% Giảm Giá *</label><input class="form-control" type="number" min="1" max="100" name="phanTramGiam" required></div>
                </div>
                <div class="mb-2 mt-2"><label class="form-label small">Tên Chương Trình *</label><input class="form-control" name="tenKM" required></div>
                <div class="row g-2">
                  <div class="col-6"><label class="form-label small">Ngày Bắt Đầu</label><input class="form-control" type="date" name="ngayBatDau"></div>
                  <div class="col-6"><label class="form-label small">Ngày Kết Thúc</label><input class="form-control" type="date" name="ngayKetThuc"></div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-gold">Lưu</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php elseif ($trang === 'hotels'): ?>
      <!-- ═══════════════ THÔNG TIN KHÁCH SẠN (UC07) ═══════════════ -->
      <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Thông Tin Khách Sạn</h2>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="stat-card p-3"><div class="stat-label">Tổng Chi Nhánh</div><div class="stat-value"><?= count($dsKhachSanAdmin) ?></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card p-3"><div class="stat-label">Tổng Số Phòng</div><div class="stat-value"><?= array_sum(array_column($dsKhachSanAdmin, 'soPhong')) ?></div></div></div>
        <div class="col-12 col-md-6"><div class="stat-card p-3"><div class="stat-label">Hotline Tổng Đài</div><div class="stat-value" style="font-size:1.1rem">123-456-7899</div></div></div>
      </div>

      <div class="bg-white border rounded">
        <div class="p-3 border-bottom"><button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#modalThemKS">+ Thêm chi nhánh</button></div>
        <div class="table-responsive">
          <table class="table table-luxury mb-0">
            <thead><tr><th>Mã KS</th><th>Tên Khách Sạn</th><th>Địa Chỉ</th><th>Số Phòng</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($dsKhachSanAdmin as $ks): ?>
              <tr>
                <td><?= h($ks['MaKS']) ?></td>
                <td><?= h($ks['TenKS']) ?></td>
                <td><?= h($ks['DiaChi']) ?></td>
                <td><?= (int)$ks['soPhong'] ?></td>
                <td>
                  <form method="POST" action="admin.php?page=hotels" onsubmit="return confirm('Xóa khách sạn <?= h($ks['TenKS']) ?>?');" class="d-inline">
                    <input type="hidden" name="action" value="xoaKhachSan">
                    <input type="hidden" name="maKS" value="<?= h($ks['MaKS']) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal thêm khách sạn -->
      <div class="modal fade" id="modalThemKS" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="admin.php?page=hotels">
              <div class="modal-header"><h5 class="modal-title font-playfair" style="color:var(--navy)">Thêm chi nhánh mới</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <input type="hidden" name="action" value="themKhachSan">
                <div class="mb-2"><label class="form-label small">Mã Khách Sạn *</label><input class="form-control" name="maKS" placeholder="VD: HP01" required></div>
                <div class="mb-2"><label class="form-label small">Tên Khách Sạn *</label><input class="form-control" name="tenKS" required></div>
                <div class="mb-2"><label class="form-label small">Địa Chỉ *</label><input class="form-control" name="diaChi" required></div>
                <div><label class="form-label small">Mô Tả</label><textarea class="form-control" name="moTa" rows="2"></textarea></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-gold">Lưu</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php elseif ($trang === 'accounts'): ?>
      <!-- ═══════════════ QUẢN LÝ TÀI KHOẢN (UC06) ═══════════════ -->
      <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Quản Lý Tài Khoản</h2>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-4"><div class="stat-card p-3"><div class="stat-label">Tài Khoản Admin</div><div class="stat-value" style="color:var(--gold)"><?= (int)$soAdmin ?></div></div></div>
        <div class="col-6 col-md-4"><div class="stat-card p-3" style="border-top-color:#1E5FA3"><div class="stat-label">Tài Khoản Thành Viên</div><div class="stat-value"><?= (int)$soThanhVien ?></div></div></div>
        <div class="col-12 col-md-4"><div class="stat-card p-3" style="border-top-color:#1A7A4A"><div class="stat-label">Tổng Tài Khoản</div><div class="stat-value"><?= count($dsTaiKhoanAdmin) ?></div></div></div>
      </div>

      <div class="bg-white border rounded">
        <div class="p-3 border-bottom"><button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#modalThemTK">+ Thêm tài khoản</button></div>
        <div class="table-responsive">
          <table class="table table-luxury mb-0">
            <thead><tr><th>Mã TK</th><th>Họ Tên</th><th>Email</th><th>SĐT</th><th>Vai Trò</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($dsTaiKhoanAdmin as $tk): ?>
              <tr>
                <td><?= h($tk['MaTK']) ?></td>
                <td><?= h($tk['HoTen']) ?></td>
                <td><?= h($tk['Email']) ?></td>
                <td><?= h($tk['SDT']) ?></td>
                <td><span class="badge <?= $tk['VaiTro'] === 'Admin' ? 'bg-warning text-dark' : 'bg-info-subtle text-info-emphasis' ?>"><?= h($tk['VaiTro']) ?></span></td>
                <td>
                  <?php if ((int)$tk['MaTK'] !== (int)$_SESSION['MaTK']): ?>
                  <form method="POST" action="admin.php?page=accounts" onsubmit="return confirm('Xóa tài khoản <?= h($tk['HoTen']) ?>?');" class="d-inline">
                    <input type="hidden" name="action" value="xoaTaiKhoan">
                    <input type="hidden" name="maTK" value="<?= h($tk['MaTK']) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                  </form>
                  <?php else: ?>
                    <span class="small text-muted">Tài khoản hiện tại</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal thêm tài khoản -->
      <div class="modal fade" id="modalThemTK" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="admin.php?page=accounts">
              <div class="modal-header"><h5 class="modal-title font-playfair" style="color:var(--navy)">Thêm tài khoản mới</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <input type="hidden" name="action" value="themTaiKhoan">
                <div class="mb-2"><label class="form-label small">Họ và Tên *</label><input class="form-control" name="hoTen" required></div>
                <div class="mb-2"><label class="form-label small">Email *</label><input class="form-control" type="email" name="email" required></div>
                <div class="row g-2">
                  <div class="col-6"><label class="form-label small">SĐT *</label><input class="form-control" name="sdt" required></div>
                  <div class="col-6">
                    <label class="form-label small">Vai Trò *</label>
                    <select class="form-select" name="vaiTro">
                      <option value="ThanhVien">Thành viên</option>
                      <option value="Admin">Admin</option>
                    </select>
                  </div>
                </div>
                <div class="mt-2"><label class="form-label small">Mật khẩu *</label><input class="form-control" type="password" name="matKhau" placeholder="Ít nhất 6 ký tự" required></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-gold">Tạo tài khoản</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php endif; ?>
  </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
