<?php 
include_once 'header.php'; 

// Nhận và làm sạch các bộ lọc đầu vào từ URL (GET Method)
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$checkin  = isset($_GET['checkin']) ? trim($_GET['checkin']) : '';
$checkout = isset($_GET['checkout']) ? trim($_GET['checkout']) : '';
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;

// Xây dựng câu lệnh truy vấn động an toàn chống SQL Injection
$queryStr = "SELECT p.MaPhong, p.SoPhong, p.TrangThai, ks.MaKS, ks.TenKS, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch 
             FROM phong p
             INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
             INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
             WHERE 1=1";
$params = [];

if (!empty($location)) {
    $queryStr .= " AND p.MaKS = :location";
    $params['location'] = $location;
}
if ($maxPrice > 0) {
    $queryStr .= " AND lp.DonGia <= :maxprice";
    $params['maxprice'] = $maxPrice;
}

$stmt = $pdo->prepare($queryStr);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$hotelsList = $pdo->query("SELECT * FROM khach_san")->fetchAll();

// Ảnh minh họa cho từng phòng (Unsplash placeholder, xoay vòng theo MaPhong)
$roomPhotos = [
    'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=400',
    'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=400',
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=400',
    'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=400',
];
?>

<div class="my-4">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
        <h3 class="font-luxury fw-bold text-navy mb-0">Kết quả tìm kiếm</h3>
        <span class="text-muted small">(<?php echo count($rooms); ?> phòng được tìm thấy)</span>
    </div>
    <hr>

    <div class="row g-4">
        <!-- BỘ LỌC -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm p-4 bg-white rounded-3 position-sticky" style="top: 20px;">
                <h6 class="fw-bold text-navy mb-3">🔽 Bộ lọc</h6>

                <form action="search.php" method="GET">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Mức giá tối đa (VNĐ)</label>
                        <input type="range" class="form-range" name="max_price" min="500000" max="10000000" step="500000"
                               value="<?php echo $maxPrice > 0 ? $maxPrice : 10000000; ?>"
                               oninput="document.getElementById('priceOut').innerText = Number(this.value).toLocaleString('vi-VN');">
                        <div class="d-flex justify-content-between small text-muted">
                            <span>500k</span>
                            <span id="priceOut" class="fw-bold text-navy"><?php echo $maxPrice > 0 ? number_format($maxPrice,0,',','.') : '10.000.000'; ?></span>
                            <span>10tr</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted d-block">Vị trí khách sạn</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="location" id="locAll" value="" <?php echo empty($location) ? 'checked' : ''; ?>>
                            <label class="form-check-label small" for="locAll">Tất cả</label>
                        </div>
                        <?php foreach($hotelsList as $h): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="location" id="loc<?php echo $h['MaKS']; ?>" value="<?php echo $h['MaKS']; ?>" <?php echo ($location == $h['MaKS']) ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="loc<?php echo $h['MaKS']; ?>"><?php echo htmlspecialchars($h['TenKS']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Ngày nhận-Ngày trả</label>
                        <div class="d-flex gap-2">
                            <input type="date" name="checkin" class="form-control form-control-sm" value="<?php echo htmlspecialchars($checkin); ?>">
                            <input type="date" name="checkout" class="form-control form-control-sm" value="<?php echo htmlspecialchars($checkout); ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gold w-100 fw-bold rounded-pill">Áp dụng bộ lọc</button>
                </form>
            </div>
        </div>

        <!-- DANH SÁCH PHÒNG -->
        <div class="col-lg-9">
            <div class="row g-4">
                <?php if(!empty($rooms)): ?>
                    <?php foreach($rooms as $idx => $r): ?>
                        <?php $photo = $roomPhotos[$idx % count($roomPhotos)]; ?>
                        <div class="col-12">
                            <div class="card card-custom shadow-sm border-0 bg-white overflow-hidden">
                                <div class="row g-0">
                                    <div class="col-md-3">
                                        <img src="<?php echo $photo; ?>" alt="<?php echo htmlspecialchars($r['TenLoai']); ?>" class="w-100 h-100" style="object-fit: cover; min-height: 180px;">
                                    </div>
                                    <div class="col-md-7 p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-medium text-secondary small"><?php echo htmlspecialchars($r['TenKS']); ?></span>
                                            <span class="text-warning small">★★★★<span class="text-muted">☆</span></span>
                                        </div>
                                        <h4 class="font-luxury fw-bold text-dark mb-2"><?php echo htmlspecialchars($r['TenLoai']); ?></h4>

                                        <div class="d-flex align-items-center gap-3 text-muted small mb-3">
                                            <span>👤 2 Người lớn</span>
                                            <span>📐 <?php echo htmlspecialchars($r['DienTich']); ?> m²</span>
                                        </div>

                                        <div class="mt-2">
                                            <?php 
                                            $tags = explode(',', $r['TienIch']);
                                            foreach($tags as $t): if(!empty(trim($t))):
                                            ?>
                                                <span class="badge bg-light text-secondary border me-1 mb-1"><?php echo htmlspecialchars(trim($t)); ?></span>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-2 p-4 bg-light d-flex flex-column justify-content-center border-start text-end">
                                        <h5 class="text-danger fw-bold mb-3"><?php echo fmtVND($r['DonGia']); ?>/Đêm</h5>

                                        <?php if($r['TrangThai'] === 'Available'): ?>
                                            <a href="room-detail.php?id=<?php echo $r['MaPhong']; ?>&cin=<?php echo urlencode($checkin); ?>&cout=<?php echo urlencode($checkout); ?>" class="btn btn-gold fw-bold w-100 rounded-pill">Xem chi tiết</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100 rounded-pill" disabled>Hết phòng</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                        <p class="text-muted fs-5 mb-0">Hệ thống không tìm thấy phòng trống nào phù hợp với điều kiện lọc hiện tại.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
