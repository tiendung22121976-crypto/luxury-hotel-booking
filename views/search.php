<?php
/**
 * search.php
 * -----------------------------------------------------------
 * Trang Tìm kiếm phòng (UC01: Tìm kiếm thông tin)
 * Chức năng chính:
 *  - Nhận tham số tìm kiếm qua GET: diaDiem, ngayNhan, ngayTra, giaMax,
 *    loaiPhong, tienIch[], sapXep.
 *  - Lọc ra các phòng CÒN TRỐNG trong khoảng thời gian [ngayNhan, ngayTra)
 *    bằng Sub-query "NOT EXISTS" để chống trùng lịch đặt phòng đã có
 *    trong bảng don_dat_phong (loại trừ đơn đã hủy "DaHuy").
 *  - Đảm bảo an toàn: dùng PDO Prepared Statement (bindParam) toàn bộ.
 * -----------------------------------------------------------
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../models/mdl_phong.php';

$pageTitle = 'Luxury Hotel – Tìm phòng';

// ── Lấy & làm sạch tham số tìm kiếm từ URL (GET) ──
$diaDiem    = trim($_GET['diaDiem'] ?? '');
$ngayNhan   = trim($_GET['ngayNhan'] ?? '');
$ngayTra    = trim($_GET['ngayTra'] ?? '');
$soKhach    = trim($_GET['soKhach'] ?? '2');
$giaMax     = isset($_GET['giaMax']) && $_GET['giaMax'] !== '' ? (float)$_GET['giaMax'] : 15000000;
$loaiPhong  = trim($_GET['loaiPhong'] ?? '');
$sapXep     = trim($_GET['sapXep'] ?? '');
$tienIchLoc = $_GET['tienIch'] ?? []; // mảng: minibar, bontam, banlv

// Đảm bảo dữ liệu gửi lên luôn ép về mảng
if (!is_array($tienIchLoc)) {
    $tienIchLoc = [];
}

$loiNgay = '';
$homNay = date('Y-m-d');
// Kiểm tra logic ngày nếu người dùng có nhập (không bắt buộc khi mới vào trang)
if ($ngayNhan !== '' && strtotime($ngayNhan) < strtotime($homNay)) {
    $loiNgay = 'Ngày nhận phòng phải lớn hơn hoặc bằng ngày hiện tại.';
} elseif ($ngayNhan && $ngayTra && strtotime($ngayTra) <= strtotime($ngayNhan)) {
    $loiNgay = 'Ngày trả phải sau ngày nhận phòng.';
}

// ── Xây dựng câu lệnh SQL động với Prepared Statement ──
// Sub-query chống trùng lịch: một phòng được coi là CÒN TRỐNG cho khoảng
// [ngayNhan, ngayTra) nếu KHÔNG TỒN TẠI đơn đặt phòng nào (chưa hủy) của
// phòng đó có khoảng thời gian giao nhau với khoảng khách đang tìm.
// Công thức giao nhau 2 khoảng thời gian: (NgayNhan_moi < NgayTra_cu) AND (NgayTra_moi > NgayNhan_cu)
$sql = "
    SELECT p.MaPhong, p.SoPhong, p.TrangThai,
           lp.MaLoai, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch,
           ks.MaKS, ks.TenKS, ks.DiaChi
    FROM phong p
    INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
    WHERE p.TrangThai != 'Cleaning'
";
$params = [];

// Lọc theo địa điểm (tên khách sạn hoặc địa chỉ) — dùng LIKE an toàn qua bindParam
if ($diaDiem !== '') {
    $sql .= " AND (ks.TenKS LIKE :diaDiem OR ks.DiaChi LIKE :diaDiem2) ";
    $params[':diaDiem']  = '%' . $diaDiem . '%';
    $params[':diaDiem2'] = '%' . $diaDiem . '%';
}

// Lọc theo loại phòng
if ($loaiPhong !== '') {
    $sql .= " AND lp.TenLoai = :loaiPhong ";
    $params[':loaiPhong'] = $loaiPhong;
}

// Lọc theo giá tối đa
$sql .= " AND lp.DonGia <= :giaMax ";
$params[':giaMax'] = $giaMax;

// Lọc theo tiện ích đã chọn (kiểm tra chuỗi TienIch có chứa từ khóa)
$mapTienIch = ['minibar' => 'Mini-bar', 'bontam' => 'Bồn tắm', 'banlv' => 'Bàn làm việc'];
foreach ($tienIchLoc as $key) {
    if (isset($mapTienIch[$key])) {
        $paramName = ':ti_' . $key;
        $sql .= " AND lp.TienIch LIKE $paramName ";
        $params[$paramName] = '%' . $mapTienIch[$key] . '%';
    }
}

// SUB-QUERY CHỐNG TRÙNG LỊCH ĐẶT PHÒNG: chỉ áp dụng khi người dùng đã chọn đủ ngày nhận/trả
if ($ngayNhan !== '' && $ngayTra !== '' && $loiNgay === '') {
    $sql .= "
        AND NOT EXISTS (
            SELECT 1 FROM don_dat_phong ddp
            WHERE ddp.MaPhong = p.MaPhong
              AND ddp.TrangThaiDon != 'DaHuy'
              AND ddp.NgayNhan < :ngayTra
              AND ddp.NgayTra   > :ngayNhan
        )
    ";
    $params[':ngayNhan'] = $ngayNhan;
    $params[':ngayTra']  = $ngayTra;
} else {
    // Khi chưa chọn ngày cụ thể, chỉ hiển thị phòng đang ở trạng thái Available
    $sql .= " AND p.TrangThai = 'Available' ";
}

// Sắp xếp kết quả
switch ($sapXep) {
    case 'asc':  $sql .= " ORDER BY lp.DonGia ASC ";  break;
    case 'desc': $sql .= " ORDER BY lp.DonGia DESC "; break;
    case 'star': $sql .= " ORDER BY lp.DonGia DESC "; break; // dùng giá làm proxy cho "đánh giá cao" (xếp hạng theo phân khúc)
    default:     $sql .= " ORDER BY ks.TenKS ASC ";   break;
}

// ── Thực thi truy vấn bằng Prepared Statement (an toàn chống SQL Injection) ──
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$dsPhong = $stmt->fetchAll();

$soDem = ($ngayNhan && $ngayTra) ? tinhSoDem($ngayNhan, $ngayTra) : 1;

$activePage = 'search';
require_once '../includes/head.php';
require_once '../includes/navbar.php';
?>

<!-- ═══ THANH TÌM KIẾM ═══ -->
<div style="background:var(--navy)" class="py-3 border-bottom border-3" >
  <div class="container">
    <form action="search.php" method="GET" class="search-bar-card p-3 rounded">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-3 text-start">
          <label>📍 Địa điểm</label>
          <input type="text" name="diaDiem" class="form-control" value="<?= h($diaDiem) ?>" placeholder="Bạn muốn đi đâu?">
        </div>
        <div class="col-6 col-md-3 text-start">
          <label>📅 Ngày nhận</label>
          <input type="date" name="ngayNhan" class="form-control" value="<?= h($ngayNhan) ?>" min="<?= $homNay ?>">
        </div>
        <div class="col-6 col-md-3 text-start">
          <label>📅 Ngày trả</label>
          <input type="date" name="ngayTra" class="form-control" value="<?= h($ngayTra) ?>" min="<?= h($ngayNhan ?: $homNay) ?>">
        </div>
        <div class="col-8 col-md-2 text-start">
          <label>👤 Số khách</label>
          <select name="soKhach" class="form-select">
            <?php foreach ([1,2,3,4] as $n): ?>
              <option value="<?= $n ?>" <?= $soKhach == $n ? 'selected' : '' ?>><?= $n ?> người lớn</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-4 col-md-1">
          <button type="submit" class="btn btn-gold w-100">Tìm</button>
        </div>
      </div>
      <?php if ($loiNgay): ?>
        <div class="alert alert-danger mt-2 mb-0 py-2 small">⚠ <?= h($loiNgay) ?></div>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="container my-4">
  <div class="row g-4">

    <!-- ═══ BỘ LỌC ═══ -->
    <div class="col-12 col-lg-3">
      <form action="search.php" method="GET" id="filterForm">
        <!-- Giữ lại các tham số tìm kiếm chính khi áp dụng bộ lọc -->
        <input type="hidden" name="diaDiem" value="<?= h($diaDiem) ?>">
        <input type="hidden" name="ngayNhan" value="<?= h($ngayNhan) ?>">
        <input type="hidden" name="ngayTra" value="<?= h($ngayTra) ?>">
        <input type="hidden" name="soKhach" value="<?= h($soKhach) ?>">

        <div class="filter-sidebar bg-white border rounded p-3">
          <h6 class="fw-medium mb-3" style="color:var(--navy)">🔽 Bộ lọc</h6>

          <div class="mb-3">
            <label class="small text-uppercase text-muted">Mức giá tối đa (VNĐ)</label>
            <input type="range" name="giaMax" class="form-range" min="500000" max="15000000" step="500000" value="<?= (int)$giaMax ?>" oninput="document.getElementById('priceLabel').textContent=(this.value/1000000).toFixed(1)+'tr'">
            <div class="d-flex justify-content-between small text-muted">
              <span>500K</span><span id="priceLabel" style="color:var(--navy);font-weight:500"><?= number_format($giaMax/1000000, 1) ?>tr</span><span>15tr</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="small text-uppercase text-muted d-block mb-2">Loại phòng</label>
            <select name="loaiPhong" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <?php foreach (['Standard','Deluxe','Suite'] as $lp): ?>
                <option value="<?= $lp ?>" <?= $loaiPhong === $lp ? 'selected' : '' ?>><?= $lp ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="small text-uppercase text-muted d-block mb-2">Tiện nghi</label>
            <?php foreach (['minibar' => 'Mini-Bar', 'bontam' => 'Bồn tắm', 'banlv' => 'Bàn làm việc'] as $key => $label): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tienIch[]" value="<?= $key ?>" id="ti-<?= $key ?>" <?= in_array($key, $tienIchLoc) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="ti-<?= $key ?>"><?= $label ?></label>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mb-3">
            <label class="small text-uppercase text-muted d-block mb-2">Sắp xếp</label>
            <select name="sapXep" class="form-select form-select-sm">
              <option value="">Mặc định</option>
              <option value="asc"  <?= $sapXep === 'asc'  ? 'selected' : '' ?>>Giá thấp → cao</option>
              <option value="desc" <?= $sapXep === 'desc' ? 'selected' : '' ?>>Giá cao → thấp</option>
              <option value="star" <?= $sapXep === 'star' ? 'selected' : '' ?>>Đánh giá cao nhất</option>
            </select>
          </div>

          <button type="submit" class="btn btn-outline-navy btn-sm w-100 mb-2">Áp dụng bộ lọc</button>
          <a href="search.php" class="btn btn-link btn-sm w-100">Xóa bộ lọc</a>
        </div>
      </form>
    </div>

    <!-- ═══ KẾT QUẢ ═══ -->
    <div class="col-12 col-lg-9">
      <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
          <h2 class="font-playfair h4 mb-0" style="color:var(--navy)">Kết quả tìm kiếm</h2>
          <p class="small text-muted mb-0"><?= count($dsPhong) ?> phòng được tìm thấy</p>
        </div>
      </div>

      <?php if (count($dsPhong) === 0): ?>
        <div class="text-center text-muted py-5">
          <div class="fs-1 mb-3">🔍</div>
          <h5 style="color:var(--navy)">Không có phòng trống</h5>
          <p>Không có phòng trống. Vui lòng thay đổi ngày hoặc địa điểm.</p>
        </div>
      <?php endif; ?>

      <?php foreach ($dsPhong as $phong):
          $soSao = maLoaiSangSoSao($phong['TenLoai']);
          $dsTienIch = tachTienIch($phong['TienIch']);
          $tongTien = $phong['DonGia'] * $soDem;
          $linkChiTiet = 'room-detail.php?' . http_build_query([
              'id' => $phong['MaPhong'], 'ngayNhan' => $ngayNhan, 'ngayTra' => $ngayTra,
          ]);
      ?>
      <div class="card mb-3 shadow-sm flex-row flex-wrap" style="cursor:pointer" onclick="location.href='<?= h($linkChiTiet) ?>'">
        <div class="room-card-img" style="width:220px;min-height:160px">🏨</div>
        <div class="card-body">
          <p class="room-card-meta mb-1"><?= h($phong['TenKS']) ?></p>
          <h3 class="room-card-title h5">Phòng <?= h($phong['TenLoai']) ?> – <?= h($phong['SoPhong']) ?></h3>
          <div class="d-flex gap-3 small text-secondary mb-2">
            <span>👥 <?= h($soKhach) ?> người lớn</span>
            <span>📐 <?= h($phong['DienTich']) ?>m²</span>
          </div>
          <div class="mb-2">
            <?php foreach ($dsTienIch as $tienIch): ?>
              <span class="amenity-tag d-inline-block mb-1"><?= iconTienIch($tienIch) ?> <?= h($tienIch) ?></span>
            <?php endforeach; ?>
          </div>
          <div class="stars mb-2"><?= renderSao($soSao) ?></div>
          <div class="d-flex justify-content-between align-items-center mt-2">
            <div>
              <div class="room-price"><?= fmtVND($phong['DonGia']) ?> <span class="small text-muted fw-normal">/đêm</span></div>
              <?php if ($soDem > 1): ?>
                <p class="small text-muted mb-0">Tổng <?= $soDem ?> đêm: <?= fmtVND($tongTien) ?></p>
              <?php endif; ?>
            </div>
            <a href="<?= h($linkChiTiet) ?>" class="btn btn-gold btn-sm" onclick="event.stopPropagation()">Xem chi tiết</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
