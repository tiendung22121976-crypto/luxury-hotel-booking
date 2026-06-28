<?php
/**
 * room-detail.php
 * Trang Chi tiết phòng (View thuần MVC)
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../models/mdl_phong.php';
require_once '../models/mdl_khuyen_mai.php';

$maPhong       = trim($_GET['id'] ?? '');
$ngayNhanQuery = trim($_GET['ngayNhan'] ?? '');
$ngayTraQuery  = trim($_GET['ngayTra'] ?? '');

if ($maPhong === '') {
  header('Location: search.php');
  exit;
}

$thongBaoLoi       = trim($_GET['err'] ?? '');
$thongBaoThanhCong = trim($_GET['res_code'] ?? '');

$stmt = $pdo->prepare("
    SELECT p.MaPhong, p.SoPhong, p.TrangThai,
           lp.MaLoai, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch, lp.MoTa,
           ks.MaKS, ks.TenKS, ks.DiaChi
    FROM phong p
    INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
    WHERE p.MaPhong = :maPhong LIMIT 1
");
$stmt->bindParam(':maPhong', $maPhong);
$stmt->execute();
$phong = $stmt->fetch();

if (!$phong) {
  header('Location: search.php');
  exit;
}

$soSao     = maLoaiSangSoSao($phong['TenLoai']);
$dsTienIch = tachTienIch($phong['TienIch']);

$stmtDG = $pdo->prepare("
    SELECT dg.MaDG, dg.MucDo, dg.BinhLuan, dg.NgayDanhGia, tk.HoTen
    FROM danh_gia dg
    INNER JOIN don_dat_phong ddp ON dg.MaDon = ddp.MaDon
    INNER JOIN tai_khoan tk ON dg.MaTK = tk.MaTK
    WHERE ddp.MaPhong = :maPhong ORDER BY dg.NgayDanhGia DESC
");
$stmtDG->bindParam(':maPhong', $maPhong);
$stmtDG->execute();
$dsDanhGia = $stmtDG->fetchAll();

$donDuDieuKienDanhGia = null;
if (daDangNhap()) {
  $stmtKT = $pdo->prepare("
        SELECT ddp.MaDon FROM don_dat_phong ddp
        LEFT JOIN danh_gia dg ON dg.MaDon = ddp.MaDon
        WHERE ddp.MaTK = :maTK AND ddp.MaPhong = :maPhong
          AND ddp.TrangThaiDon = 'HoanTat' AND dg.MaDG IS NULL LIMIT 1
    ");
  $stmtKT->bindParam(':maTK', $_SESSION['MaTK']);
  $stmtKT->bindParam(':maPhong', $maPhong);
  $stmtKT->execute();
  $donDuDieuKienDanhGia = $stmtKT->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'guiDanhGia') {
  yeuCauDangNhap();
  $maDon    = trim($_POST['maDon'] ?? '');
  $mucDo    = (int)($_POST['mucDo'] ?? 0);
  $binhLuan = trim($_POST['binhLuan'] ?? '');

  if (!$maDon || $mucDo < 1 || $mucDo > 3 || $binhLuan === '') {
    $thongBaoLoi = 'Vui lòng chọn mức độ đánh giá và nhập nhận xét.';
  } else {
    $stmtInsertDG = $pdo->prepare("INSERT INTO danh_gia (MaTK, MaDon, MucDo, BinhLuan) VALUES (:maTK, :maDon, :mucDo, :binhLuan)");
    $stmtInsertDG->execute([':maTK' => $_SESSION['MaTK'], ':maDon' => $maDon, ':mucDo' => $mucDo, ':binhLuan' => $binhLuan]);
    header('Location: room-detail.php?id=' . urlencode($maPhong) . '&msg=' . urlencode('Cảm ơn bạn đã đánh giá!') . '&type=success');
    exit;
  }
}

$pageTitle = 'Luxury Hotel – ' . $phong['TenLoai'];
require_once '../includes/head.php';
require_once '../includes/navbar.php';
?>

<div class="container my-4">
  <nav class="small mb-3"><a href="index.php">Trang chủ</a> › <a href="search.php">Phòng</a> › <span class="text-muted">Phòng <?= h($phong['TenLoai']) ?></span></nav>
  <div class="row g-4">
    <div class="col-12 col-lg-8">
      <div class="row g-2 mb-4" style="height:260px">
        <div class="col-8"><div class="gallery-img h-100 rounded" style="font-size:5rem">🏨</div></div>
        <div class="col-4 d-flex flex-column gap-2" style="height:260px">
          <div class="gallery-img rounded flex-fill" style="font-size:2.5rem">🛏️</div>
          <div class="gallery-img rounded flex-fill" style="font-size:2.5rem">🛁</div>
        </div>
      </div>
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <div class="mb-2"><span class="badge bg-info-subtle text-info-emphasis">Tiêu chuẩn</span> <span class="stars"><?= renderSao($soSao) ?></span></div>
          <h1 class="font-playfair h2" style="color:var(--navy)">Phòng <?= h($phong['TenLoai']) ?> – <?= h($phong['SoPhong']) ?></h1>
          <p class="text-muted small"><?= h($phong['TenKS']) ?> · <?= h($phong['DiaChi']) ?></p>
        </div>
        <?= getStatusBadgePhong($phong['TrangThai']) ?>
      </div>
      <p class="text-secondary mb-4"><?= h($phong['MoTa']) ?></p>

      <div class="p-3 rounded mb-4" style="background:var(--gold-pale)">
        <h3 class="font-playfair h5 mb-3" style="color:var(--navy)">Tiện nghi phòng</h3>
        <div class="row g-2">
          <?php foreach ($dsTienIch as $tienIch): ?>
            <div class="col-6 col-md-4 d-flex align-items-center gap-2 small" style="color:var(--navy)"><span class="fs-5"><?= iconTienIch($tienIch) ?></span><span><?= h($tienIch) ?></span></div>
          <?php endforeach; ?>
        </div>
      </div>

      <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Đánh giá khách hàng (<?= count($dsDanhGia) ?>)</h2>
      <?php if ($thongBaoLoi && $thongBaoLoi !== 'CONFLICT'): ?><div class="alert alert-danger">⚠ <?= h($thongBaoLoi) ?></div><?php endif; ?>
      <div id="reviews-list">
        <?php if (count($dsDanhGia) === 0): ?><p class="text-muted small">Chưa có đánh giá nào.</p><?php endif; ?>
        <?php foreach ($dsDanhGia as $dg): ?>
          <div class="review-card p-3 mb-2">
            <div class="d-flex justify-content-between mb-1">
              <div><div class="reviewer-name"><?= h($dg['HoTen']) ?></div><div class="small text-muted"><?= fmtNgay($dg['NgayDanhGia']) ?></div></div>
              <div class="small fw-medium"><?= ['1'=>'😞 Kém','2'=>'😐 Trung bình','3'=>'😊 Tốt'][$dg['MucDo']]??'' ?></div>
            </div>
            <p class="small text-secondary fst-italic mb-0">"<?= h($dg['BinhLuan']) ?>"</p>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (daDangNhap() && $donDuDieuKienDanhGia): ?>
        <div class="mt-4">
          <h3 class="font-playfair h6 mb-2" style="color:var(--navy)">Viết đánh giá của bạn</h3>
          <form method="POST" action="room-detail.php?id=<?= urlencode($maPhong) ?>">
            <input type="hidden" name="action" value="guiDanhGia">
            <input type="hidden" name="maDon" value="<?= h($donDuDieuKienDanhGia) ?>">
            <select name="mucDo" class="form-select form-select-sm mb-2" style="width:auto"><option value="3">😊 Tốt</option><option value="2" selected>😐 Trung bình</option><option value="1">😞 Kém</option></select>
            <textarea name="binhLuan" class="form-control mb-2" rows="3" placeholder="Nhận xét..." required></textarea>
            <button type="submit" class="btn btn-gold btn-sm">Gửi đánh giá</button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-12 col-lg-4">
      <div class="booking-widget bg-white border rounded p-3">
        <div class="price-big"><?= fmtVND($phong['DonGia']) ?></div>
        <p class="small text-muted mb-3">/đêm · Hủy miễn phí</p>
        <?php if ($thongBaoThanhCong): ?>
          <div class="text-center p-3 rounded mb-3" style="background:var(--navy);color:#fff">
            <div class="booking-success-icon mb-2">✓</div><p class="small mb-1" style="color:var(--gold-light)">Mã đặt phòng</p>
            <div class="reservation-code"><?= h($thongBaoThanhCong) ?></div>
          </div>
          <a href="account.php" class="btn btn-gold w-100 mb-2">Xem đơn của tôi</a>
        <?php elseif ($thongBaoLoi === 'CONFLICT'): ?>
          <div class="alert alert-warning">⚠️ Phòng vừa có người khác đặt xong!</div>
          <a href="search.php" class="btn btn-gold w-100">Chọn phòng khác</a>
        <?php else: ?>
          <div class="bg-light rounded p-2 mb-3 border">
            <div class="row g-0 border rounded overflow-hidden">
              <div class="col-6 border-end p-2"><div class="small text-muted" style="font-size:.7rem">NGÀY NHẬN</div><input type="date" id="wd-ngayNhan" class="form-control form-control-sm border-0 p-0" value="<?= h($ngayNhanQuery) ?>" min="<?= date('Y-m-d') ?>"></div>
              <div class="col-6 p-2"><div class="small text-muted" style="font-size:.7rem">NGÀY TRẢ</div><input type="date" id="wd-ngayTra" class="form-control form-control-sm border-0 p-0" value="<?= h($ngayTraQuery) ?>"></div>
            </div>
          </div>
          <div id="wd-date-err" class="alert alert-warning small d-none py-2"></div>
          <button type="button" class="btn btn-gold w-100" id="btn-open-booking-modal">Đặt phòng ngay</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0"><div><h5 class="modal-title fw-bold">Hoàn tất đặt phòng</h5><p class="small text-secondary mb-0" id="modal-summary"></p></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="POST" action="../controllers/ctrl_datphong.php" id="form-booking-modal">
          <input type="hidden" name="action" value="datPhong">
          <input type="hidden" name="maPhong" value="<?= h($maPhong) ?>">
          <input type="hidden" name="ngayNhan" id="modal-ngayNhan">
          <input type="hidden" name="ngayTra" id="modal-ngayTra">

          <div class="row g-2 mb-2">
            <div class="col-6"><label class="form-label small fw-medium">Tên *</label><input type="text" class="form-control form-control-sm" name="ten" value="<?= daDangNhap()?h(explode(' ',$_SESSION['HoTen']??'')[0]??''):'' ?>" required></div>
            <div class="col-6"><label class="form-label small fw-medium">Họ *</label><input type="text" class="form-control form-control-sm" name="ho" value="<?= daDangNhap()?h(substr($_SESSION['HoTen']??'',strlen(explode(' ',$_SESSION['HoTen']??'')[0]??'')+1)):'' ?>" required></div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6"><label class="form-label small fw-medium">Email *</label><input type="email" class="form-control form-control-sm" name="email" value="<?= daDangNhap()?h($_SESSION['Email']??''):'' ?>" required></div>
            <div class="col-6"><label class="form-label small fw-medium">SĐT *</label><input type="tel" class="form-control form-control-sm" name="sdt" pattern="[0-9]{10,11}" required></div>
          </div>
          <textarea class="form-control form-control-sm mb-3" name="yeuCauDacBiet" rows="2" placeholder="Yêu cầu đặc biệt..."></textarea>
          
          <p class="fw-semibold small mb-1">Thanh toán</p>
          <div class="border rounded p-2 mb-2"><div class="form-check"><input class="form-check-input" type="radio" name="payment_method" checked><label class="form-check-label small">Thẻ tín dụng / Ghi nợ (Sandbox)</label></div></div>
          <input class="form-control form-control-sm mb-3" name="maKM" placeholder="Mã khuyến mãi (nếu có)">

          <div class="d-flex justify-content-end gap-2 pt-2 border-top">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-gold btn-sm">Xác nhận &amp; Thanh toán →</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
<script>
  document.getElementById('btn-open-booking-modal').addEventListener('click', function() {
    const nNhan = document.getElementById('wd-ngayNhan').value, nTra = document.getElementById('wd-ngayTra').value;
    const err = document.getElementById('wd-date-err'), today = new Date().toISOString().split('T')[0];
    if (!nNhan || !nTra) { err.textContent = 'Vui lòng chọn ngày.'; err.classList.remove('d-none'); return; }
    if (nNhan < today) { err.textContent = 'Ngày nhận không được ở quá khứ.'; err.classList.remove('d-none'); return; }
    if (nTra <= nNhan) { err.textContent = 'Ngày trả phải sau ngày nhận.'; err.classList.remove('d-none'); return; }
    err.classList.add('d-none');
    document.getElementById('modal-ngayNhan').value = nNhan; document.getElementById('modal-ngayTra').value = nTra;
    const nights = Math.round((new Date(nTra) - new Date(nNhan))/86400000);
    document.getElementById('modal-summary').textContent = '<?= addslashes($phong['TenLoai']) ?> · ' + nights + ' đêm';
    new bootstrap.Modal(document.getElementById('bookingModal')).show();
  });
</script>