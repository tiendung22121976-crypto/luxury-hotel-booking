<?php 
include_once 'header.php'; 

// Truy vấn danh sách chi nhánh khách sạn
$hotels = $pdo->query("SELECT * FROM khach_san")->fetchAll();

// Truy vấn các phòng nổi bật (giá cao nhất) để gợi ý trên trang chủ - "Có Thể Bạn Quan Tâm?"
$featuredRooms = $pdo->query("SELECT p.MaPhong, p.SoPhong, ks.TenKS, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch
                              FROM phong p
                              INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
                              INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
                              ORDER BY lp.DonGia DESC LIMIT 2")->fetchAll();

// Ảnh minh họa (Unsplash placeholder, xoay vòng)
$roomPhotos = [
    'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=400',
    'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=400',
];
$placePhotos = [
    'https://images.unsplash.com/photo-1583417319070-4a69db38a482?q=80&w=500',
    'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?q=80&w=500',
    'https://images.unsplash.com/photo-1573270689103-d7a4e42b609a?q=80&w=500',
];
?>

<!-- HERO + SEARCH BOX -->
<div class="p-5 text-center bg-navy text-white rounded-4 shadow-sm mb-5 position-relative overflow-hidden" style="background: linear-gradient(rgba(13,27,42,0.85), rgba(13,27,42,0.85)), url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1200') center/cover;">
    <div class="py-4">
        <h1 class="display-4 font-luxury fw-bold text-gold mb-3">Welcome to Luxury Hotel</h1>
        <p class="fs-5 text-light opacity-75 mb-5">Khám phá không gian lưu trú sang trọng và đẳng cấp tại Luxury Hotel. Đặt phòng ngay để nhận ưu đãi</p>
        
        <div class="card p-4 shadow text-dark border-0 mx-auto" style="max-width: 900px; background-color: rgba(255,255,255,0.95)">
            <form action="search.php" method="GET" class="row g-3">
                <div class="col-md-4 text-start">
                    <label class="form-label fw-bold small text-muted">📍 Địa điểm</label>
                    <select name="location" class="form-select" required>
                        <option value="">Chọn khách sạn...</option>
                        <?php foreach($hotels as $h): ?>
                            <option value="<?php echo $h['MaKS']; ?>"><?php echo htmlspecialchars($h['TenKS']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 text-start">
                    <label class="form-label fw-bold small text-muted">📅 Ngày nhận</label>
                    <input type="date" name="checkin" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3 text-start">
                    <label class="form-label fw-bold small text-muted">📅 Ngày trả</label>
                    <input type="date" name="checkout" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-gold w-100 py-2 fw-bold shadow-sm">TÌM KIẾM</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CÓ THỂ BẠN QUAN TÂM -->
<div class="mb-5">
    <h3 class="font-luxury fw-bold text-navy mb-4">Có Thể Bạn Quan Tâm?</h3>

    <div class="row g-4">
        <?php foreach($featuredRooms as $idx => $r): ?>
            <?php $photo = $roomPhotos[$idx % count($roomPhotos)]; ?>
            <div class="col-12">
                <div class="card card-custom shadow-sm border-0 bg-white overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <img src="<?php echo $photo; ?>" class="w-100 h-100" style="object-fit: cover; min-height: 180px;" alt="<?php echo htmlspecialchars($r['TenLoai']); ?>">
                        </div>
                        <div class="col-md-7 p-4">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-medium text-secondary small"><?php echo htmlspecialchars($r['TenKS']); ?></span>
                                <span class="text-warning small">★★★★<span class="text-muted">☆</span></span>
                            </div>
                            <h4 class="font-luxury fw-bold text-dark mb-2"><?php echo htmlspecialchars($r['TenLoai']); ?></h4>

                            <div class="d-flex align-items-center gap-3 text-muted small mb-3">
                                <span>👤 2 Người lớn</span>
                                <span>📐 <?php echo htmlspecialchars($r['DienTich']); ?>m²</span>
                            </div>

                            <div>
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
                            <a href="room-detail.php?id=<?php echo $r['MaPhong']; ?>" class="btn btn-gold fw-bold w-100 rounded-pill">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ƯU ĐÃI -->
<div class="bg-light rounded-4 p-5 mb-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3 class="font-luxury fw-bold text-navy mb-2">Ưu đãi</h3>
        <p class="text-secondary mb-0">Khuyến mãi, giảm giá và ưu đãi đặc biệt dành riêng cho bạn.</p>
    </div>
    <a href="search.php" class="btn btn-outline-navy rounded-pill px-4 fw-bold">Tìm hiểu ngay →</a>
</div>

<!-- MỘT SỐ ĐỊA ĐIỂM NỔI TIẾNG -->
<div class="mb-5">
    <h3 class="font-luxury fw-bold text-navy mb-4">Một Số Địa Điểm Nổi Tiếng</h3>
    <div class="row g-3">
        <div class="col-md-4">
            <img src="<?php echo $placePhotos[0]; ?>" class="w-100 rounded-3" style="height: 220px; object-fit: cover;" alt="Địa điểm nổi tiếng">
        </div>
        <div class="col-md-4">
            <img src="<?php echo $placePhotos[1]; ?>" class="w-100 rounded-3" style="height: 220px; object-fit: cover;" alt="Địa điểm nổi tiếng">
        </div>
        <div class="col-md-4">
            <img src="<?php echo $placePhotos[2]; ?>" class="w-100 rounded-3" style="height: 220px; object-fit: cover;" alt="Địa điểm nổi tiếng">
        </div>
    </div>
</div>

<!-- CÁC CHI NHÁNH CỦA CHÚNG TÔI -->
<div class="mb-5">
    <div class="text-center mb-5">
        <h2 class="font-luxury fw-bold text-navy">CÁC CHI NHÁNH CỦA CHÚNG TÔI</h2>
        <div class="mx-auto bg-warning mb-3" style="width: 60px; height: 3px;"></div>
    </div>
    
    <div class="row g-4 justify-content-center">
        <?php foreach($hotels as $h): ?>
            <div class="col-md-6 col-lg-5">
                <div class="card card-custom h-100 shadow-sm bg-white">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="font-luxury fw-bold text-navy mb-2">🏨 <?php echo htmlspecialchars($h['TenKS']); ?></h4>
                            <p class="text-secondary small mb-3">📍 <em><?php echo htmlspecialchars($h['DiaChi']); ?></em></p>
                            <p class="text-muted text-justify small"><?php echo htmlspecialchars($h['MoTa']); ?></p>
                        </div>
                        <div class="text-end mt-4">
                            <a href="search.php?location=<?php echo $h['MaKS']; ?>" class="btn btn-outline-navy btn-sm rounded-pill px-4">Xem chi tiết cơ sở →</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include_once 'footer.php'; ?>
