<?php
/**
 * admin.php
 * Trang Quản trị (Thêm tab Quản Lý Đơn Đặt + băm Bcrypt)
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

yeuCauAdmin();

$trang = $_GET['page'] ?? 'dashboard';
$thongBaoLoi = '';
$thongBaoThanhCong = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hanhDong = $_POST['action'] ?? '';

    // ── XỬ LÝ ĐƠN ĐẶT PHÒNG (TÍNH NĂNG MỚI BỔ SUNG) ──
    if ($hanhDong === 'capNhatDon') {
        $maDon        = trim($_POST['maDon'] ?? '');
        $trangThaiMoi = trim($_POST['trangThaiMoi'] ?? '');
        if (in_array($trangThaiMoi, ['DaXacNhan', 'HoanTat', 'DaHuy'])) {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE don_dat_phong SET TrangThaiDon = :tt WHERE MaDon = :id")->execute([':tt' => $trangThaiMoi, ':id' => $maDon]);
            if ($trangThaiMoi === 'DaHuy' || $trangThaiMoi === 'HoanTat') {
                $stmtP = $pdo->prepare("SELECT MaPhong FROM don_dat_phong WHERE MaDon = :id"); $stmtP->execute([':id' => $maDon]);
                if ($maP = $stmtP->fetchColumn()) {
                    $pdo->prepare("UPDATE phong SET TrangThai = 'Available' WHERE MaPhong = :p")->execute([':p' => $maP]);
                }
            }
            $pdo->commit();
            $thongBaoThanhCong = "Cập nhật trạng thái đơn #$maDon thành công!";
        }
    }

    if ($hanhDong === 'themPhong') {
        $maPhong = trim($_POST['maPhong']??''); $soPhong = trim($_POST['soPhong']??'');
        $maKS = trim($_POST['maKS']??''); $maLoai = trim($_POST['maLoai']??'');
        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM phong WHERE MaPhong=?"); $stmtC->execute([$maPhong]);
        if ($stmtC->fetchColumn() > 0) { $thongBaoLoi = "Mã phòng đã tồn tại."; }
        else {
            $pdo->prepare("INSERT INTO phong (MaPhong, SoPhong, MaKS, MaLoai, TrangThai) VALUES (?,?,?,?,'Available')")->execute([$maPhong,$soPhong,$maKS,$maLoai]);
            $thongBaoThanhCong = "Thêm phòng thành công!";
        }
    }

    if ($hanhDong === 'xoaPhong') {
        $maP = $_POST['maPhong'];
        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM don_dat_phong WHERE MaPhong=? AND TrangThaiDon IN ('ChoXacNhan','DaXacNhan')"); $stmtC->execute([$maP]);
        if ($stmtC->fetchColumn() > 0) $thongBaoLoi = "Phòng đang có khách đặt, cấm xóa.";
        else { $pdo->prepare("DELETE FROM phong WHERE MaPhong=?")->execute([$maP]); $thongBaoThanhCong = "Xóa phòng xong."; }
    }

    if ($hanhDong === 'themTaiKhoan') {
        $ht = trim($_POST['hoTen']??''); $em = trim($_POST['email']??''); $dt = trim($_POST['sdt']??''); $vt = $_POST['vaiTro']??'ThanhVien'; $mk = $_POST['matKhau']??'';
        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM tai_khoan WHERE Email=? OR SDT=?"); $stmtC->execute([$em,$dt]);
        if ($stmtC->fetchColumn() > 0) $thongBaoLoi = "Email/SĐT đã tồn tại.";
        else {
            // ĐÃ VÁ LỖI BẢO MẬT: Băm Bcrypt mật khẩu
            $mkHash = password_hash($mk, PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO tai_khoan (HoTen,Email,SDT,MatKhau,VaiTro) VALUES (?,?,?,?,?)")->execute([$ht,$em,$dt,$mkHash,$vt]);
            $thongBaoThanhCong = "Tạo tài khoản thành công!";
        }
    }

    if ($hanhDong === 'xoaTaiKhoan') {
        $id = $_POST['maTK'];
        if ($id == $_SESSION['MaTK']) $thongBaoLoi = "Không thể tự xóa chính mình.";
        else { $pdo->prepare("DELETE FROM tai_khoan WHERE MaTK=?")->execute([$id]); $thongBaoThanhCong = "Xóa user xong."; }
    }
}

// DỮ LIỆU HIỂN THỊ
$dsDonAdmin = $pdo->query("SELECT ddp.*, tk.HoTen, tk.SDT, lp.TenLoai, ks.TenKS FROM don_dat_phong ddp JOIN tai_khoan tk ON ddp.MaTK=tk.MaTK JOIN phong p ON ddp.MaPhong=p.MaPhong JOIN loai_phong lp ON p.MaLoai=lp.MaLoai JOIN khach_san ks ON p.MaKS=ks.MaKS ORDER BY ddp.NgayTao DESC")->fetchAll();
$dsPhongAdmin = $pdo->query("SELECT p.*, lp.TenLoai, ks.TenKS FROM phong p JOIN loai_phong lp ON p.MaLoai=lp.MaLoai JOIN khach_san ks ON p.MaKS=ks.MaKS ORDER BY p.MaKS, p.SoPhong")->fetchAll();
$dsTaiKhoanAdmin = $pdo->query("SELECT * FROM tai_khoan ORDER BY MaTK DESC")->fetchAll();
$dsKS = $pdo->query("SELECT * FROM khach_san")->fetchAll(); $dsLP = $pdo->query("SELECT * FROM loai_phong")->fetchAll();

$pageTitle = 'Luxury Hotel – Admin'; require_once '../includes/head.php';
?>
<style>.admin-nav-item.active { background: rgba(201,168,76,0.15); color: var(--gold) !important; }</style>

<nav class="navbar navbar-luxury px-3">
  <a href="index.php" class="navbar-brand-luxury">LUXURY <span>ADMIN</span></a>
  <div><span class="small me-3" style="color:var(--gold-light)">🛡️ <?= h($_SESSION['HoTen']) ?></span> <a href="logout.php" class="btn btn-sm btn-outline-light">Đăng xuất</a></div>
</nav>

<div class="d-flex">
  <nav class="admin-sidebar p-2" style="min-width:220px;min-height:90vh;background:var(--navy)">
    <a class="admin-nav-item d-block p-2 text-white text-decoration-none <?= $trang==='dashboard'?'active':'' ?>" href="admin.php?page=dashboard">🏠 Trang Chủ</a>
    <a class="admin-nav-item d-block p-2 text-white text-decoration-none <?= $trang==='bookings'?'active':'' ?>" href="admin.php?page=bookings">📋 Quản Lý Đơn Đặt</a>
    <a class="admin-nav-item d-block p-2 text-white text-decoration-none <?= $trang==='rooms'?'active':'' ?>" href="admin.php?page=rooms">🏨 Quản Lý Phòng</a>
    <a class="admin-nav-item d-block p-2 text-white text-decoration-none <?= $trang==='accounts'?'active':'' ?>" href="admin.php?page=accounts">👤 Quản Lý Tài Khoản</a>
    <hr class="border-secondary"><a class="admin-nav-item d-block p-2 text-muted small text-decoration-none" href="index.php">← Về Web Khách</a>
  </nav>

  <main class="flex-fill p-4 bg-light">
    <?php if ($thongBaoThanhCong): ?><div class="alert alert-success">✓ <?= h($thongBaoThanhCong) ?></div><?php endif; ?>
    <?php if ($thongBaoLoi): ?><div class="alert alert-danger">⚠ <?= h($thongBaoLoi) ?></div><?php endif; ?>

    <?php if ($trang === 'dashboard'): ?>
      <h3 class="font-playfair mb-3">Tổng quan hệ thống</h3>
      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card p-3 shadow-sm border-0"><small class="text-muted">TỔNG ĐƠN ĐẶT PHÒNG</small><h3 class="fw-bold mt-1 text-navy"><?= count($dsDonAdmin) ?></h3></div></div>
        <div class="col-md-4"><div class="card p-3 shadow-sm border-0"><small class="text-muted">TỔNG SỐ PHÒNG</small><h3 class="fw-bold mt-1 text-success"><?= count($dsPhongAdmin) ?></h3></div></div>
        <div class="col-md-4"><div class="card p-3 shadow-sm border-0"><small class="text-muted">TÀI KHOẢN KHÁCH</small><h3 class="fw-bold mt-1 text-warning"><?= count($dsTaiKhoanAdmin) ?></h3></div></div>
      </div>

    <?php elseif ($trang === 'bookings'): ?>
      <h3 class="font-playfair mb-3">Quản lý Đơn đặt phòng</h3>
      <div class="card border-0 shadow-sm p-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light"><tr><th>Mã đơn</th><th>Khách hàng</th><th>Phòng</th><th>Ngày ở</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thao tác duyệt</th></tr></thead>
            <tbody>
              <?php foreach ($dsDonAdmin as $d): ?>
              <tr>
                <td><strong><?= h($d['MaXacNhan']) ?></strong></td>
                <td><?= h($d['HoTen']) ?><br><small class="text-muted"><?= h($d['SDT']) ?></small></td>
                <td><?= h($d['TenKS']) ?><br><strong class="text-navy"><?= h($d['TenLoai']) ?></strong></td>
                <td><?= fmtNgay($d['NgayNhan']) ?> → <?= fmtNgay($d['NgayTra']) ?></td>
                <td class="fw-bold"><?= fmtVND($d['TongTien']) ?></td>
                <td><?= getStatusBadgeDonHang($d['TrangThaiDon']) ?></td>
                <td>
                  <?php if ($d['TrangThaiDon'] !== 'HoanTat' && $d['TrangThaiDon'] !== 'DaHuy'): ?>
                    <form method="POST" action="admin.php?page=bookings" class="d-flex gap-1">
                      <input type="hidden" name="action" value="capNhatDon"><input type="hidden" name="maDon" value="<?= $d['MaDon'] ?>">
                      <?php if ($d['TrangThaiDon'] === 'ChoXacNhan'): ?>
                        <button name="trangThaiMoi" value="DaXacNhan" class="btn btn-sm btn-info text-white">Duyệt</button>
                      <?php endif; ?>
                      <button name="trangThaiMoi" value="HoanTat" class="btn btn-sm btn-success" title="Khách đã trả phòng">Hoàn tất</button>
                      <button name="trangThaiMoi" value="DaHuy" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hủy đơn này?')">Hủy</button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted">Đã chốt sổ</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($trang === 'rooms'): ?>
      <div class="d-flex justify-content-between mb-3"><h3 class="font-playfair mb-0">Quản lý phòng</h3><button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#mP">+ Thêm phòng</button></div>
      <div class="card border-0 shadow-sm p-3">
        <table class="table small align-middle">
          <thead><tr><th>Mã</th><th>Số phòng</th><th>Khách sạn</th><th>Loại</th><th>Trạng thái</th><th>Xóa</th></tr></thead>
          <tbody>
            <?php foreach($dsPhongAdmin as $p): ?>
            <tr><td><?= $p['MaPhong'] ?></td><td><strong><?= $p['SoPhong'] ?></strong></td><td><?= $p['TenKS'] ?></td><td><?= $p['TenLoai'] ?></td><td><?= getStatusBadgePhong($p['TrangThai']) ?></td>
            <td><form method="POST"><input type="hidden" name="action" value="xoaPhong"><input type="hidden" name="maPhong" value="<?= $p['MaPhong'] ?>"><button class="btn btn-sm btn-danger">🗑</button></form></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="modal fade" id="mP" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-3"><form method="POST"><input type="hidden" name="action" value="themPhong"><h5>Thêm phòng</h5><input class="form-control mb-2" name="maPhong" placeholder="Mã (VD: P999)" required><input class="form-control mb-2" name="soPhong" placeholder="Số phòng (VD: 999)" required><select class="form-select mb-2" name="maKS"><?php foreach($dsKS as $k) echo "<option value='{$k['MaKS']}'>{$k['TenKS']}</option>"; ?></select><select class="form-select mb-3" name="maLoai"><?php foreach($dsLP as $l) echo "<option value='{$l['MaLoai']}'>{$l['TenLoai']}</option>"; ?></select><button class="btn btn-gold w-100">Lưu</button></form></div></div></div>

    <?php elseif ($trang === 'accounts'): ?>
      <div class="d-flex justify-content-between mb-3"><h3 class="font-playfair mb-0">Tài khoản</h3><button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#mTK">+ Thêm user</button></div>
      <div class="card border-0 shadow-sm p-3">
        <table class="table small align-middle"><thead><tr><th>ID</th><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Quyền</th><th>Xóa</th></tr></thead>
        <tbody><?php foreach($dsTaiKhoanAdmin as $u): ?><tr><td><?= $u['MaTK'] ?></td><td><?= h($u['HoTen']) ?></td><td><?= h($u['Email']) ?></td><td><?= h($u['SDT']) ?></td><td><span class="badge bg-secondary"><?= $u['VaiTro'] ?></span></td><td><?php if($u['MaTK']!=$_SESSION['MaTK']): ?><form method="POST"><input type="hidden" name="action" value="xoaTaiKhoan"><input type="hidden" name="maTK" value="<?= $u['MaTK'] ?>"><button class="btn btn-sm btn-danger">🗑</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table>
      </div>
      <div class="modal fade" id="mTK" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-3"><form method="POST"><input type="hidden" name="action" value="themTaiKhoan"><h5>Thêm tài khoản</h5><input class="form-control mb-2" name="hoTen" placeholder="Họ tên" required><input class="form-control mb-2" type="email" name="email" placeholder="Email" required><input class="form-control mb-2" name="sdt" placeholder="SĐT" required><input class="form-control mb-2" type="password" name="matKhau" placeholder="Mật khẩu" required><select class="form-select mb-3" name="vaiTro"><option value="ThanhVien">Thành viên</option><option value="Admin">Admin</option></select><button class="btn btn-gold w-100">Tạo</button></form></div></div></div>
    <?php endif; ?>
  </main>
</div>
</body></html>