<?php
/**
 * index.php
 * -----------------------------------------------------------
 * Trang chủ - Luxury Hotel
 * Chức năng:
 *  - Hiển thị form tìm kiếm phòng trực quan (chuyển hướng sang search.php)
 *  - Lấy danh sách 3 phòng nổi bật (còn trống) từ CSDL để hiển thị
 *  - Hiển thị các điểm đến nổi bật và banner khuyến mãi
 * -----------------------------------------------------------
 */
session_start();
require_once '../config/database.php';
require_once '../models/mdl_phong.php';
require_once '../models/mdl_khuyen_mai.php';
require_once '../includes/functions.php';

$pageTitle = 'Luxury Hotel – Trang chủ';

// ── Truy vấn lấy 3 phòng nổi bật còn trống (Available) ──
// Join 3 bảng: phong - loai_phong - khach_san để lấy đầy đủ thông tin hiển thị
$sqlPhongNoiBat = "
    SELECT p.MaPhong, p.SoPhong, p.TrangThai,
           lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch,
           ks.TenKS, ks.DiaChi
    FROM phong p
    INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
    WHERE p.TrangThai = 'Available'
    ORDER BY lp.DonGia DESC
    LIMIT 3
";
$stmt = $pdo->query($sqlPhongNoiBat);
$dsPhongNoiBat = $stmt->fetchAll();

$activePage = 'home';
require_once '../includes/head.php';
require_once '../includes/navbar.php';
?>

<!-- ═══ HERO + SEARCH BAR ═══ -->
<div class="search-hero">
  <h1 class="font-playfair mb-2">Welcome to Luxury Hotel</h1>
  <p class="mb-4">Khám phá không gian lưu trú sang trọng và đẳng cấp tại Luxury Hotel<br>Đặt phòng ngay để nhận ưu đãi đặc biệt</p>

  <form action="search.php" method="GET" class="search-bar-card p-3 rounded shadow-sm">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-3 text-start">
        <label>📍 Địa điểm</label>
        <input type="text" name="diaDiem" class="form-control" placeholder="Bạn muốn đi đâu?" autocomplete="off">
      </div>
      <div class="col-12 col-md-3 text-start">
        <label>📅 Ngày nhận phòng</label>
        <input type="date" name="ngayNhan" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-12 col-md-3 text-start">
        <label>📅 Ngày trả phòng</label>
        <input type="date" name="ngayTra" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
      </div>
      <div class="col-8 col-md-2 text-start">
        <label>👤 Số khách</label>
        <select name="soKhach" class="form-select">
          <option value="1">1 người lớn</option>
          <option value="2" selected>2 người lớn</option>
          <option value="3">3 người lớn</option>
          <option value="4">4 người lớn</option>
        </select>
      </div>
      <div class="col-4 col-md-1">
        <button type="submit" class="btn btn-gold w-100">Tìm</button>
      </div>
    </div>
  </form>
</div>

<!-- ═══ PHÒNG NỔI BẬT ═══ -->
<div class="container py-5">
  <p class="text-uppercase small fw-medium" style="color:var(--gold);letter-spacing:1.5px">CÓ THỂ BẠN QUAN TÂM</p>
  <h2 class="font-playfair mb-3" style="color:var(--navy)">Phòng nổi bật</h2>
  <hr style="border-top:2px solid var(--gold);width:60px;opacity:1">

  <div class="row g-4 mt-3">
    <?php if (count($dsPhongNoiBat) === 0): ?>
      <p class="text-muted">Hiện chưa có phòng trống để hiển thị.</p>
    <?php endif; ?>

    <?php foreach ($dsPhongNoiBat as $phong):
        $soSao = maLoaiSangSoSao($phong['TenLoai']);
        $dsTienIch = tachTienIch($phong['TienIch']);
    ?>
    <div class="col-12 col-md-4">
      <div class="card h-100 shadow-sm" style="cursor:pointer" onclick="location.href='room-detail.php?id=<?= urlencode($phong['MaPhong']) ?>'">
        <div class="room-card-img" style="height:180px">🏨</div>
        <div class="card-body d-flex flex-column">
          <p class="room-card-meta mb-1"><?= h($phong['TenKS']) ?></p>
          <h3 class="room-card-title h5 mb-2">Phòng <?= h($phong['TenLoai']) ?></h3>
          <div class="d-flex gap-3 small text-secondary mb-2">
            <span>📐 <?= h($phong['DienTich']) ?>m²</span>
          </div>
          <div class="mb-2">
            <?php foreach (array_slice($dsTienIch, 0, 3) as $tienIch): ?>
              <span class="amenity-tag d-inline-block mb-1"><?= h($tienIch) ?></span>
            <?php endforeach; ?>
          </div>
          <div class="stars mb-2"><?= renderSao($soSao) ?></div>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <div class="room-price"><?= fmtVND($phong['DonGia']) ?> <span class="small text-muted fw-normal">/đêm</span></div>
            <a href="room-detail.php?id=<?= urlencode($phong['MaPhong']) ?>" class="btn btn-gold btn-sm" onclick="event.stopPropagation()">Xem chi tiết</a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-4">
    <a href="search.php" class="btn btn-outline-navy">Xem tất cả phòng →</a>
  </div>
</div>

<!-- ═══ KHUYẾN MÃI ═══ -->
<div style="background:var(--navy)" class="py-5">
  <div class="container">
    <p class="text-uppercase small fw-medium" style="color:var(--gold);letter-spacing:1.5px">ƯU ĐÃI</p>
    <h2 class="font-playfair text-white mb-2">Khuyến mãi đặc biệt</h2>
    <hr style="border-top:2px solid var(--gold);width:60px;opacity:1">
    <p class="text-light mb-4">Khuyến mãi, giảm giá và ưu đãi đặc biệt dành riêng cho bạn. <a href="auth.php">Tìm hiểu ngay →</a></p>
    <?php
    // Lấy khuyến mãi đang còn hiệu lực từ CSDL để hiển thị động
    $sqlKM = "SELECT MaKM, TenKM, PhanTramGiam, NgayBatDau, NgayKetThuc FROM khuyen_mai
              WHERE NgayKetThuc >= CURDATE() ORDER BY NgayBatDau ASC LIMIT 3";
    $dsKM = $pdo->query($sqlKM)->fetchAll();
    $icons = ['🎁', '🌙', '📱'];
    ?>
    <div class="row g-3">
      <?php if (count($dsKM) === 0): ?>
        <p class="text-light">Hiện chưa có chương trình khuyến mãi nào đang hoạt động.</p>
      <?php endif; ?>
      <?php foreach ($dsKM as $i => $km): ?>
      <div class="col-12 col-md-4">
        <div class="p-4 rounded" style="background:rgba(255,255,255,0.05);border:1px solid rgba(201,168,76,0.3)">
          <div class="fs-2 mb-2"><?= $icons[$i % 3] ?></div>
          <h4 style="color:var(--gold)" class="h6 mb-2"><?= h($km['TenKM']) ?> – Giảm <?= (int)$km['PhanTramGiam'] ?>%</h4>
          <p class="text-light small mb-0">Áp dụng từ <?= fmtNgay($km['NgayBatDau']) ?> đến <?= fmtNgay($km['NgayKetThuc']) ?>. Mã: <strong><?= h($km['MaKM']) ?></strong></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ═══ ĐỊA ĐIỂM NỔI TIẾNG ═══ -->
<div class="container py-5">
  <p class="text-uppercase small fw-medium" style="color:var(--gold);letter-spacing:1.5px">ĐỊA ĐIỂM NỔI TIẾNG</p>
  <h2 class="font-playfair mb-3" style="color:var(--navy)">Tìm kiếm tại những điểm đến hàng đầu</h2>
  <hr style="border-top:2px solid var(--gold);width:60px;opacity:1">

  <?php
  // Lấy danh sách khách sạn thực tế từ CSDL (thay cho 3 ô tĩnh trước đây)
  $dsKS = $pdo->query("SELECT MaKS, TenKS, DiaChi FROM khach_san")->fetchAll();
  $icons2 = ['🏛️', '🌊', '🌆', '🏮'];
  ?>
  <div class="row g-3 mt-3">
    <?php foreach ($dsKS as $i => $ks): ?>
    <div class="col-12 col-md-4">
      <div class="dest-tile rounded p-4 text-white d-flex align-items-end" style="height:220px;background:linear-gradient(180deg,rgba(13,27,42,.1) 0%,rgba(13,27,42,.8) 100%),#E0DDD5;position:relative;overflow:hidden"
           onclick="location.href='search.php?diaDiem=<?= urlencode($ks['TenKS']) ?>'">
        <div class="position-absolute top-50 start-50 translate-middle" style="font-size:4rem;opacity:.3"><?= $icons2[$i % 4] ?></div>
        <div class="position-relative">
          <h3 class="font-playfair mb-0"><?= h($ks['TenKS']) ?></h3>
          <p class="small mb-0" style="color:var(--gold-light)"><?= h($ks['DiaChi']) ?></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ═══ THÔNG TIN KHÁCH SẠN ═══ -->
<div class="container pb-5">
  <div class="p-4 rounded d-flex gap-3 flex-wrap" style="background:var(--gold-pale);border-left:4px solid var(--gold)">
    <div class="fs-1">🏨</div>
    <div>
      <h3 class="font-playfair mb-2" style="color:var(--navy)">Thông tin khách sạn</h3>
      <p class="text-secondary small mb-2" style="max-width:700px">Luxury Hotel là chuỗi khách sạn cao cấp mới gia nhập thị trường Việt Nam, dự kiến chính thức đi vào hoạt động vào cuối năm 2026 với định hướng mang đến trải nghiệm lưu trú hiện đại, tiện nghi và chuyên nghiệp.</p>
      <a href="#" class="small fw-medium">Tìm hiểu thêm →</a>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
