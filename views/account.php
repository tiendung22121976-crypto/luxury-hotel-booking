<?php 
include_once 'header.php'; 

if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='auth.php';</script>";
    exit();
}

$userLog = $_SESSION['user'];

// Truy vấn toàn bộ lịch sử hóa đơn đặt phòng của người dùng này
$stmtMyBooking = $pdo->prepare("SELECT ddp.*, p.SoPhong, lp.TenLoai, ks.TenKS 
                                FROM don_dat_phong ddp
                                INNER JOIN phong p ON ddp.MaPhong = p.MaPhong
                                INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
                                INNER JOIN khach_san ks ON p.MaKS = ks.MaKS
                                WHERE ddp.MaTK = ? 
                                ORDER BY ddp.MaDon DESC");
$stmtMyBooking->execute([$userLog['MaTK']]);
$myBookings = $stmtMyBooking->fetchAll();

// Ảnh minh họa khách sạn (Unsplash placeholder)
$bookingPhotos = [
    'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=300',
    'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=300',
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=300',
];

$statusLabel = [
    'ChoXacNhan' => ['Chờ xác nhận', 'warning'],
    'DaXacNhan'  => ['Đã xác nhận', 'info'],
    'HoanTat'    => ['Hoàn thành', 'success'],
    'DaHuy'      => ['Đã hủy', 'secondary'],
];
?>

<div class="row g-4 my-3">
    <!-- THÔNG TIN CÁ NHÂN -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm p-4 text-center bg-white rounded-3 position-sticky" style="top: 20px;">
            <div class="bg-navy text-gold rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; font-size: 1.5rem; font-weight: bold;">
                <?php echo strtoupper(substr($userLog['HoTen'], 0, 1)); ?>
            </div>
            <h5 class="font-luxury fw-bold text-navy mb-1"><?php echo htmlspecialchars($userLog['HoTen']); ?></h5>
            <span class="text-muted small mb-4">Thông tin cá nhân</span>
            
            <div class="text-start border-top pt-3 small text-muted mt-2">
                <p class="mb-2">SĐT: <span class="text-dark fw-medium"><?php echo htmlspecialchars($userLog['SDT']); ?></span></p>
                <p class="mb-3">email: <span class="text-dark fw-medium"><?php echo htmlspecialchars($userLog['Email']); ?></span></p>
            </div>

            <button class="btn btn-outline-navy btn-sm rounded-pill mb-2" disabled>Thay Đổi Thông Tin</button>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill">Đăng Xuất</a>
        </div>
    </div>
    
    <!-- ĐƠN ĐẶT PHÒNG -->
    <div class="col-lg-9">
        <h4 class="font-luxury fw-bold text-navy mb-4">Đơn Đặt Phòng Của Tôi</h4>

        <?php if(!empty($myBookings)): ?>
            <?php foreach($myBookings as $idx => $bk): ?>
                <?php 
                $photo = $bookingPhotos[$idx % count($bookingPhotos)];
                $stLabel = $statusLabel[$bk['TrangThaiDon']] ?? [$bk['TrangThaiDon'], 'secondary'];
                ?>
                <div class="card border-0 shadow-sm bg-white rounded-3 mb-3 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <img src="<?php echo $photo; ?>" class="w-100 h-100" style="object-fit: cover; min-height: 140px;" alt="">
                        </div>
                        <div class="col-md-9 p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong class="text-navy d-block text-uppercase small"><?php echo htmlspecialchars($bk['TenKS']); ?></strong>
                                <span class="badge bg-<?php echo $stLabel[1]; ?>"><?php echo $stLabel[0]; ?></span>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-7">
                                    <span class="text-dark fw-medium"><?php echo htmlspecialchars($bk['TenLoai']); ?></span>
                                    <div class="small text-muted mt-2">Thời gian</div>
                                    <div class="small text-secondary">📅 <?php echo $bk['NgayNhan']; ?> đến <?php echo $bk['NgayTra']; ?></div>
                                    <div class="fw-medium text-dark mt-2">Tổng cộng: <?php echo fmtVND($bk['TongTien']); ?></div>
                                </div>
                                <div class="col-md-5 text-md-end">
                                    <div class="small text-muted">Mã Đặt Phòng</div>
                                    <div class="fw-bold text-navy mb-3"><?php echo $bk['MaXacNhan']; ?></div>

                                    <?php if($bk['TrangThaiDon'] === 'ChoXacNhan' || $bk['TrangThaiDon'] === 'DaXacNhan'): ?>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill">Hủy Phòng</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-navy rounded-pill">Xem Chi Tiết</button>
                                    <?php endif; ?>
                                    <?php if($bk['TrangThaiDon'] === 'HoanTat'): ?>
                                        <button class="btn btn-sm btn-gold rounded-pill ms-1">Viết Đánh Giá</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card border-0 shadow-sm bg-white rounded-3 p-5 text-center text-muted">
                Bạn chưa thực hiện giao dịch đặt phòng nào.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'footer.php'; ?>
