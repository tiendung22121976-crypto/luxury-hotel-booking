<?php

/**
 * room-detail.php
 * Trang Chi tiết phòng (View thuần)
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

// Hứng kết quả từ Controller chuyển hướng về
$thongBaoLoi       = trim($_GET['err'] ?? '');
$thongBaoThanhCong = trim($_GET['res_code'] ?? '');

// ── Truy vấn thông tin chi tiết phòng (JOIN 3 bảng) ──
$stmt = $pdo->prepare("
    SELECT p.MaPhong, p.SoPhong, p.TrangThai,
           lp.MaLoai, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch, lp.MoTa,
           ks.MaKS, ks.TenKS, ks.DiaChi
    FROM phong p
    INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
    WHERE p.MaPhong = :maPhong
    LIMIT 1
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

// ── Truy vấn danh sách đánh giá của phòng này ──
$stmtDG = $pdo->prepare("
    SELECT dg.MaDG, dg.MucDo, dg.BinhLuan, dg.NgayDanhGia, tk.HoTen
    FROM danh_gia dg
    INNER JOIN don_dat_phong ddp ON dg.MaDon = ddp.MaDon
    INNER JOIN tai_khoan tk ON dg.MaTK = tk.MaTK
    WHERE ddp.MaPhong = :maPhong
    ORDER BY dg.NgayDanhGia DESC
");
$stmtDG->bindParam(':maPhong', $maPhong);
$stmtDG->execute();
$dsDanhGia = $stmtDG->fetchAll();

// ── Kiểm tra tài khoản đang đăng nhập có đơn đặt phòng đã HoanTat tại phòng này không ──
$donDuDieuKienDanhGia = null;
if (daDangNhap()) {
  $stmtKT = $pdo->prepare("
        SELECT ddp.MaDon FROM don_dat_phong ddp
        LEFT JOIN danh_gia dg ON dg.MaDon = ddp.MaDon
        WHERE ddp.MaTK = :maTK AND ddp.MaPhong = :maPhong
          AND ddp.TrangThaiDon = 'HoanTat' AND dg.MaDG IS NULL
        LIMIT 1
    ");
  $stmtKT->bindParam(':maTK', $_SESSION['MaTK']);
  $stmtKT->bindParam(':maPhong', $maPhong);
  $stmtKT->execute();
  $donDuDieuKienDanhGia = $stmtKT->fetchColumn();
}

// ── XỬ LÝ FORM GỬI ĐÁNH GIÁ (UC04) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guiDanhGia') {
  yeuCauDangNhap();
  $maDon    = trim($_POST['maDon'] ?? '');
  $mucDo    = (int)($_POST['mucDo'] ?? 0);
  $binhLuan = trim($_POST['binhLuan'] ?? '');

  if (!$maDon || $mucDo < 1 || $mucDo > 3 || $binhLuan === '') {
    $thongBaoLoi = 'Vui lòng chọn mức độ đánh giá và nhập nhận xét.';
  } else {
    $stmtInsertDG = $pdo->prepare("
            INSERT INTO danh_gia (MaTK, MaDon, MucDo, BinhLuan) VALUES (:maTK, :maDon, :mucDo, :binhLuan)
        ");
    $stmtInsertDG->execute([
      ':maTK'     => $_SESSION['MaTK'],
      ':maDon'    => $maDon,
      ':mucDo'    => $mucDo,
      ':binhLuan' => $binhLuan
    ]);

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
    <!-- ═══ CỘT TRÁI: Thông tin phòng + đánh giá ═══ -->
    <div class="col-12 col-lg-8">
      <div class="row g-2 mb-4" style="height:260px">
        <div class="col-8">
          <div class="gallery-img h-100 rounded" style="font-size:5rem">🏨</div>
        </div>
        <div class="col-4 d-flex flex-column gap-2" style="height:260px">
          <div class="gallery-img rounded flex-fill" style="font-size:2.5rem">🛏️</div>
          <div class="gallery-img rounded flex-fill" style="font-size:2.5rem">🛁</div>
        </div>
      </div>

      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <div class="mb-2">
            <span class="badge bg-info-subtle text-info-emphasis"><?= $soSao >= 5 ? 'Cao cấp' : ($soSao >= 4 ? 'Tiêu chuẩn cao' : 'Tiêu chuẩn') ?></span>
            <span class="stars"><?= renderSao($soSao) ?></span>
          </div>
          <h1 class="font-playfair h2" style="color:var(--navy)">Phòng <?= h($phong['TenLoai']) ?> – <?= h($phong['SoPhong']) ?></h1>
          <p class="text-muted small"><?= h($phong['TenKS']) ?> · <?= h($phong['DiaChi']) ?></p>
        </div>
        <?= getStatusBadgePhong($phong['TrangThai']) ?>
      </div>

      <p class="text-secondary mb-4"><?= h($phong['MoTa'] ?: 'Trải nghiệm sự sang trọng tuyệt đối với không gian rộng rãi, nội thất sang trọng và dịch vụ đẳng cấp.') ?></p>

      <div class="p-3 rounded mb-4" style="background:var(--gold-pale)">
        <h3 class="font-playfair h5 mb-3" style="color:var(--navy)">Tiện nghi phòng</h3>
        <div class="row g-2">
          <?php foreach ($dsTienIch as $tienIch): ?>
            <div class="col-6 col-md-4 d-flex align-items-center gap-2 small" style="color:var(--navy)">
              <span class="fs-5"><?= iconTienIch($tienIch) ?></span><span><?= h($tienIch) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ═══ ĐÁNH GIÁ ═══ -->
      <h2 class="font-playfair h4 mb-3" style="color:var(--navy)">Đánh giá của khách hàng (<?= count($dsDanhGia) ?>)</h2>

      <?php if ($thongBaoLoi && $thongBaoLoi !== 'CONFLICT'): ?>
        <div class="alert alert-danger">⚠ <?= h($thongBaoLoi) ?></div>
      <?php endif; ?>

      <div id="reviews-list">
        <?php if (count($dsDanhGia) === 0): ?>
          <p class="text-muted small">Chưa có đánh giá nào cho phòng này.</p>
        <?php endif; ?>
        <?php foreach ($dsDanhGia as $dg):
          $mucDoLabel = ['1' => '😞 Kém', '2' => '😐 Trung bình', '3' => '😊 Tốt'][$dg['MucDo']] ?? '';
        ?>
          <div class="review-card p-3 mb-2">
            <div class="d-flex justify-content-between mb-1">
              <div>
                <div class="reviewer-name"><?= h($dg['HoTen']) ?></div>
                <div class="small text-muted"><?= fmtNgay($dg['NgayDanhGia']) ?></div>
              </div>
              <div class="small fw-medium"><?= $mucDoLabel ?></div>
            </div>
            <p class="small text-secondary fst-italic mb-0">"<?= h($dg['BinhLuan']) ?>"</p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Form viết đánh giá (chỉ hiện nếu đủ điều kiện theo UC04) -->
      <?php if (daDangNhap() && $donDuDieuKienDanhGia): ?>
        <div class="mt-4">
          <h3 class="font-playfair h6 mb-2" style="color:var(--navy)">Viết đánh giá của bạn</h3>
          <form method="POST" action="room-detail.php?id=<?= urlencode($maPhong) ?>">
            <input type="hidden" name="action" value="guiDanhGia">
            <input type="hidden" name="maDon" value="<?= h($donDuDieuKienDanhGia) ?>">
            <div class="mb-2">
              <select name="mucDo" class="form-select form-select-sm" style="width:auto">
                <option value="3">😊 Tốt</option>
                <option value="2" selected>😐 Trung bình</option>
                <option value="1">😞 Kém</option>
              </select>
            </div>
            <textarea name="binhLuan" class="form-control mb-2" rows="3" placeholder="Nhận xét của bạn về phòng..." required></textarea>
            <button type="submit" class="btn btn-gold btn-sm">Gửi đánh giá</button>
          </form>
        </div>
      <?php elseif (daDangNhap()): ?>
        <p class="small text-muted mt-3">Bạn cần có đơn đặt phòng đã hoàn tất tại phòng này để viết đánh giá.</p>
      <?php endif; ?>
    </div>

    <!-- ═══ CỘT PHẢI: Widget đặt phòng ═══ -->
    <div class="col-12 col-lg-4">
      <div class="booking-widget bg-white border rounded p-3">
        <div class="price-big"><?= fmtVND($phong['DonGia']) ?></div>
        <p class="small text-muted mb-3">/đêm · Hủy miễn phí trước 24h</p>

        <?php if ($thongBaoThanhCong): ?>
          <!-- ═══ THÔNG BÁO ĐẶT PHÒNG THÀNH CÔNG ═══ -->
          <div class="text-center p-3 rounded mb-3" style="background:var(--navy);color:#fff">
            <div class="booking-success-icon mb-2">✓</div>
            <p class="small mb-1" style="color:var(--gold-light)">Mã đặt phòng</p>
            <div class="reservation-code"><?= h($thongBaoThanhCong) ?></div>
            <p class="small mt-2 mb-0">Xuất trình khi nhận phòng</p>
          </div>
          <a href="account.php" class="btn btn-gold w-100 mb-2">Xem đơn của tôi</a>
          <a href="index.php" class="btn btn-outline-navy w-100">Về trang chủ</a>
        <?php elseif ($thongBaoLoi === 'CONFLICT'): ?>
          <div class="alert alert-warning">⚠️ Phòng đã được đặt bởi người khác trong lúc điền form. Vui lòng chọn phòng khác hoặc đổi ngày.</div>
          <a href="search.php" class="btn btn-gold w-100">Chọn phòng khác</a>
        <?php else: ?>
          <!-- Widget chọn ngày — bấm "Đặt phòng" mở modal -->
          <div class="bg-light rounded p-2 mb-3 border">
            <div class="row g-0 border rounded overflow-hidden">
              <div class="col-6 border-end p-2">
                <div class="small text-muted text-uppercase" style="font-size:.7rem">Ngày nhận</div>
                <input type="date" id="wd-ngayNhan" class="form-control form-control-sm border-0 p-0"
                  value="<?= h($ngayNhanQuery) ?>" min="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-6 p-2">
                <div class="small text-muted text-uppercase" style="font-size:.7rem">Ngày trả</div>
                <input type="date" id="wd-ngayTra" class="form-control form-control-sm border-0 p-0"
                  value="<?= h($ngayTraQuery) ?>">
              </div>
            </div>
          </div>
          <div id="wd-date-err" class="alert alert-warning small d-none py-2"></div>
          <button type="button" class="btn btn-gold w-100" id="btn-open-booking-modal">
            Đặt phòng ngay
          </button>
          <p class="small text-muted text-center mt-2 mb-0">Không tính phí cho đến khi xác nhận</p>


        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
<!-- ═══ MODAL HOÀN TẤT ĐẶT PHÒNG ═══ -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title fw-bold" id="bookingModalLabel">Hoàn tất đặt phòng</h5>
          <p class="small text-secondary mb-0" id="modal-summary">
            <?= h($phong['TenLoai']) ?> · <?= h($phong['TenKS']) ?>
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="room-detail.php?id=<?= urlencode($maPhong) ?>" id="form-booking-modal">
          <input type="hidden" name="action" value="datPhong">
          <input type="hidden" name="ngayNhan" id="modal-ngayNhan">
          <input type="hidden" name="ngayTra" id="modal-ngayTra">

          <!-- Thông tin người đặt -->
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label small fw-medium">Tên <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" name="ten"
                placeholder="Nguyễn"
                value="<?= daDangNhap() ? h(explode(' ', $_SESSION['HoTen'] ?? '')[0] ?? '') : '' ?>" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-medium">Họ <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" name="ho"
                placeholder="Văn An"
                value="<?php
                        if (daDangNhap()) {
                          $parts = explode(' ', $_SESSION['HoTen'] ?? '');
                          array_shift($parts);
                          echo h(implode(' ', $parts));
                        }
                        ?>" required>
            </div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label small fw-medium">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control form-control-sm" name="email"
                placeholder="email@gmail.com"
                value="<?= daDangNhap() ? h($_SESSION['Email'] ?? '') : '' ?>" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-medium">Số điện thoại <span class="text-danger">*</span></label>
              <input type="tel" class="form-control form-control-sm" name="sdt"
                placeholder="0912-345-678"
                pattern="[0-9]{10,11}" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-medium">Yêu cầu đặc biệt (không bắt buộc)</label>
            <textarea class="form-control form-control-sm" name="yeuCauDacBiet" rows="2"
              placeholder="Phòng không hút thuốc, view biển..."></textarea>
          </div>

          <!-- Phương thức thanh toán -->
          <p class="fw-semibold small mb-2">Phương thức thanh toán</p>
          <div class="border rounded p-2 mb-2" id="payment-card-box">
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="payment_method"
                id="pm-card" value="card" checked>
              <label class="form-check-label small fw-medium" for="pm-card">
                Thẻ tín dụng / Ghi nợ
              </label>
            </div>
            <div id="card-fields">
              <input type="text" class="form-control form-control-sm mb-1"
                name="card_number" placeholder="Số thẻ">
              <div class="row g-1">
                <div class="col-6">
                  <input type="text" class="form-control form-control-sm"
                    name="card_expiry" placeholder="MM/YY">
                </div>
                <div class="col-6">
                  <input type="text" class="form-control form-control-sm"
                    name="card_cvc" placeholder="CVC">
                </div>
              </div>
            </div>
          </div>
          <div class="border rounded p-2 mb-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method"
                id="pm-transfer" value="transfer">
              <label class="form-check-label small fw-medium" for="pm-transfer">
                Chuyển khoản ngân hàng
              </label>
            </div>
          </div>

          <!-- Mã khuyến mãi -->
          <input class="form-control form-control-sm mb-3" name="maKM"
            placeholder="Mã khuyến mãi (nếu có)">

          <div class="d-flex justify-content-end gap-2 pt-2 border-top">
            <button type="button" class="btn btn-outline-secondary btn-sm px-4"
              data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-gold btn-sm px-4">
              Xác nhận &amp; Thanh toán →
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div><!-- /modal -->
<?php require_once '../includes/footer.php'; ?>
<script>
  // ── Mở modal đặt phòng: validate ngày trước, rồi truyền ngày vào form modal ──
  document.getElementById('btn-open-booking-modal').addEventListener('click', function() {
    const ngayNhan = document.getElementById('wd-ngayNhan').value;
    const ngayTra = document.getElementById('wd-ngayTra').value;
    const errBox = document.getElementById('wd-date-err');
    const today = new Date().toISOString().split('T')[0];

    if (!ngayNhan || !ngayTra) {
      errBox.textContent = 'Vui lòng chọn ngày nhận và ngày trả phòng.';
      errBox.classList.remove('d-none');
      return;
    }
    if (ngayNhan < today) {
      errBox.textContent = 'Ngày nhận phòng không được là ngày trong quá khứ.';
      errBox.classList.remove('d-none');
      return;
    }
    if (ngayTra <= ngayNhan) {
      errBox.textContent = 'Ngày trả phòng phải sau ngày nhận phòng.';
      errBox.classList.remove('d-none');
      return;
    }
    errBox.classList.add('d-none');

    // Truyền ngày vào hidden inputs của form modal
    document.getElementById('modal-ngayNhan').value = ngayNhan;
    document.getElementById('modal-ngayTra').value = ngayTra;

    // Cập nhật dòng tóm tắt trong modal
    const fmt = d => d.split('-').reverse().join('/');
    const nights = Math.round((new Date(ngayTra) - new Date(ngayNhan)) / 86400000);
    const donGia = <?= (int)$phong['DonGia'] ?>;
    const total = (donGia * nights).toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('modal-summary').textContent =
      '<?= addslashes($phong['TenLoai']) ?> · <?= addslashes($phong['TenKS']) ?> · ' +
      nights + ' đêm · Tổng: ' + total;

    new bootstrap.Modal(document.getElementById('bookingModal')).show();
  });

  // ── Toggle hiện/ẩn fields thẻ tín dụng ──
  const pmCard = document.getElementById('pm-card');
  const pmTransfer = document.getElementById('pm-transfer');
  const cardFields = document.getElementById('card-fields');

  function toggleCard() {
    cardFields.style.display = pmTransfer.checked ? 'none' : '';
  }
  pmCard.addEventListener('change', toggleCard);
  pmTransfer.addEventListener('change', toggleCard);
</script>