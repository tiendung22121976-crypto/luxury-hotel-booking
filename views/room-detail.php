<?php 
include_once 'header.php'; 

$maPhong = isset($_GET['id']) ? trim($_GET['id']) : '';
$cin     = isset($_GET['cin']) ? trim($_GET['cin']) : '';
$cout    = isset($_GET['cout']) ? trim($_GET['cout']) : '';

// 1. Tải thông tin phòng chi tiết
$stmt = $pdo->prepare("SELECT p.*, ks.TenKS, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch, lp.MoTa 
                       FROM phong p
                       INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
                       INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
                       WHERE p.MaPhong = ?");
$stmt->execute([$maPhong]);
$room = $stmt->fetch();

if(!$room) {
    echo "<div class='alert alert-danger'>Phòng không tồn tại hoặc đã bị xóa.</div>";
    include_once 'footer.php';
    exit();
}

// Tính số đêm nghỉ trú
$days = (!empty($cin) && !empty($cout)) ? diffDays($cin, $cout) : 1;
$totalPay = $room['DonGia'] * $days;

// 2. XỬ LÝ BOOKING KHI BẤM NÚT XÁC NHẬN ĐẶT PHÒNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_book'])) {
    if (!isset($_SESSION['user'])) {
        echo "<script>alert('Vui lòng đăng nhập tài khoản trước khi thực hiện đặt phòng!'); window.location.href='auth.php';</script>";
        exit();
    }
    
    $maKM = !empty($_POST['promo_code']) ? trim($_POST['promo_code']) : null;
    $finalTotal = $totalPay;
    
    // Kiểm tra tính hợp lệ của mã giảm giá (Nếu có nhập)
    if($maKM) {
        $stmtKM = $pdo->prepare("SELECT * FROM khuyen_mai WHERE MaKM = ? AND NgayKetThuc >= CURDATE()");
        $stmtKM->execute([$maKM]);
        $promo = $stmtKM->fetch();
        if($promo) {
            $finalTotal = $totalPay - ($totalPay * ($promo['PhanTramGiam'] / 100));
        } else {
            $maKM = null; // Mã không khớp hoặc hết hạn
        }
    }
    
    $maXacNhan = genConfirmCode();
    $maTK = $_SESSION['user']['MaTK'];
    
    $sqlInsertBooking = "INSERT INTO don_dat_phong (MaTK, MaPhong, NgayNhan, NgayTra, TongTien, MaKM, MaXacNhan, TrangThaiDon) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'ChoXacNhan')";
    $stmtInsert = $pdo->prepare($sqlInsertBooking);
    
    if($stmtInsert->execute([$maTK, $maPhong, $cin, $cout, $finalTotal, $maKM, $maXacNhan])) {
        // Cập nhật trạng thái phòng sang Reserved
        $pdo->prepare("UPDATE phong SET TrangThai = 'Reserved' WHERE MaPhong = ?")->execute([$maPhong]);
        echo "<script>alert('Đặt phòng thành công! Mã xác nhận của bạn là: $maXacNhan'); window.location.href='account.php';</script>";
        exit();
    }
}

// 3. XỬ LÝ GỬI BÌNH LUẬN ĐÁNH GIÁ MỚI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_review'])) {
    if(isset($_SESSION['user']) && !empty($_POST['comment'])) {
        $stmtReview = $pdo->prepare("INSERT INTO danh_gia (MaTK, MucDo, BinhLuan, NgayDanhGia) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmtReview->execute([$_SESSION['user']['MaTK'], $_POST['rating'], trim($_POST['comment'])]);
        echo "<script>window.location.href=window.location.href;</script>";
        exit();
    }
}

// 4. LẤY TOÀN BỘ ĐÁNH GIÁ CŨ
$reviews = $pdo->query("SELECT dg.*, tk.HoTen FROM danh_gia dg INNER JOIN tai_khoan tk ON dg.MaTK = tk.MaTK ORDER BY dg.NgayDanhGia DESC")->fetchAll();

// Ảnh minh họa phòng (Unsplash placeholder)
$mainPhoto = 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=1200';
$sidePhotos = [
    'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=400',
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=400',
];
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="search.php" class="text-decoration-none text-muted">Phòng</a></li>
        <li class="breadcrumb-item active text-navy fw-medium" aria-current="page"><?php echo htmlspecialchars($room['TenLoai']); ?></li>
    </ol>
</nav>

<!-- Ảnh phòng -->
<div class="row g-2 mb-4">
    <div class="col-md-8">
        <img src="<?php echo $mainPhoto; ?>" alt="<?php echo htmlspecialchars($room['TenLoai']); ?>" class="w-100 rounded-3" style="height: 360px; object-fit: cover;">
    </div>
    <div class="col-md-4">
        <div class="row g-2 h-100">
            <div class="col-12" style="height: 50%;">
                <img src="<?php echo $sidePhotos[0]; ?>" class="w-100 h-100 rounded-3" style="object-fit: cover;">
            </div>
            <div class="col-12" style="height: 50%;">
                <img src="<?php echo $sidePhotos[1]; ?>" class="w-100 h-100 rounded-3" style="object-fit: cover;">
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4">
            <div class="text-warning mb-2">★★★★<span class="text-muted">☆</span> <span class="text-secondary small ms-1">Cao cấp</span></div>
            <h2 class="font-luxury fw-bold text-navy mb-1"><?php echo htmlspecialchars($room['TenLoai']); ?> - Số <?php echo htmlspecialchars($room['SoPhong']); ?></h2>
            <p class="text-gold fw-bold mb-3">🏢 Khách sạn chủ quản: <?php echo htmlspecialchars($room['TenKS']); ?></p>
            
            <p class="text-secondary"><?php echo nl2br(htmlspecialchars($room['MoTa'])); ?></p>
            
            <h5 class="fw-bold text-dark mt-4 mb-2">Trang thiết bị & Tiện nghi đi kèm</h5>
            <div>
                <?php 
                $amenities = explode(',', $room['TienIch']);
                foreach($amenities as $am): if(!empty(trim($am))):
                ?>
                    <span class="badge bg-navy text-gold p-2 me-2 mb-2 font-monospace">✓ <?php echo htmlspecialchars(trim($am)); ?></span>
                <?php endif; endforeach; ?>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
            <h4 class="font-luxury fw-bold text-navy mb-4">Đánh giá từ khách lưu trú</h4>
            
            <?php if(isset($_SESSION['user'])): ?>
                <form method="POST" class="mb-4 p-3 bg-light rounded">
                    <label class="form-label fw-bold small">Trải nghiệm của bạn</label>
                    <div class="mb-2">
                        <select name="rating" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="3">Excellent (Hài lòng tốt)</option>
                            <option value="2">Normal (Bình thường)</option>
                            <option value="1">Bad (Kém chất lượng)</option>
                        </select>
                    </div>
                    <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Nhập nhận xét chi tiết..." required></textarea>
                    <button type="submit" name="btn_review" class="btn btn-sm btn-navy text-white">Gửi phản hồi</button>
                </form>
            <?php endif; ?>
            
            <div class="review-list">
                <?php foreach($reviews as $rv): ?>
                    <div class="border-bottom py-3">
                        <div class="d-flex justify-content-between">
                            <strong class="text-dark"><?php echo htmlspecialchars($rv['HoTen']); ?></strong>
                            <small class="text-muted"><?php echo $rv['NgayDanhGia']; ?></small>
                        </div>
                        <div class="text-warning small mb-1">★★★★<span class="text-muted">☆</span></div>
                        <p class="mb-0 text-secondary small mt-1">"<?php echo htmlspecialchars($rv['BinhLuan']); ?>"</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 bg-navy text-white rounded-3 position-sticky" style="top: 20px;">
            <h4 class="font-luxury text-gold fw-bold border-bottom border-secondary pb-2 mb-3">Tóm tắt đặt phòng</h4>
            
            <form method="POST">
                <div class="mb-2 small d-flex justify-content-between">
                    <span>Đơn giá niêm yết:</span>
                    <strong><?php echo fmtVND($room['DonGia']); ?></strong>
                </div>

                <div class="mb-2 small">
                    <label class="text-gold d-block mb-1">📅 Ngày nhận</label>
                    <input type="date" name="cin_display" class="form-control form-control-sm" value="<?php echo htmlspecialchars($cin); ?>" disabled>
                </div>
                <div class="mb-2 small">
                    <label class="text-gold d-block mb-1">📅 Ngày trả</label>
                    <input type="date" name="cout_display" class="form-control form-control-sm" value="<?php echo htmlspecialchars($cout); ?>" disabled>
                </div>
                <div class="mb-2 small">
                    <label class="text-gold d-block mb-1">👤 Số khách</label>
                    <select name="so_khach" class="form-select form-select-sm">
                        <option value="1">1 người lớn</option>
                        <option value="2" selected>2 người lớn</option>
                        <option value="3">3 người lớn</option>
                        <option value="4">4 người lớn</option>
                    </select>
                </div>

                <div class="mb-2 small d-flex justify-content-between mt-3">
                    <span>Tổng số đêm tính toán:</span>
                    <strong><?php echo $days; ?> đêm</strong>
                </div>
                <hr class="border-secondary">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <span class="text-light fs-5">Tạm tính hóa đơn:</span>
                    <h4 class="text-warning fw-bold mb-0"><?php echo fmtVND($totalPay); ?></h4>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small text-gold">Nhập mã giảm giá Khuyến Mãi (Nếu có)</label>
                    <input type="text" name="promo_code" class="form-control form-control-sm text-uppercase" placeholder="Ví dụ: SUMMER26">
                </div>
                
                <button type="submit" name="btn_book" class="btn btn-gold w-100 py-2 fw-bold text-dark rounded-3 shadow">XÁC NHẬN ĐẶT PHÒNG</button>
            </form>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
