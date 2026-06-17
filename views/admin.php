<?php 
include_once 'header.php'; 

// // Chặn quyền nếu không phải Admin hệ thống
// if (!isset($_SESSION['user']) || $_SESSION['user']['VaiTro'] !== 'Admin') {
//     echo "<div class='alert alert-danger my-5 shadow-sm fw-bold text-center'>⚠️ Bạn không đủ thẩm quyền truy cập vùng quản trị cao cấp!</div>";
//     include_once 'footer.php';
//     exit();
// }

// Xử lý Thay đổi trạng thái đơn hàng nhanh chóng (Cập nhật trực tiếp qua URL query)
if(isset($_GET['update_order_id']) && isset($_GET['new_status'])) {
    $stmtUpdate = $pdo->prepare("UPDATE don_dat_phong SET TrangThaiDon = ? WHERE MaDon = ?");
    $stmtUpdate->execute([$_GET['new_status'], $_GET['update_order_id']]);
    echo "<script>window.location.href='admin.php';</script>";
    exit();
}

// Lấy danh sách toàn bộ các Đơn đặt phòng hiện có trên CSDL để thống kê quản lý
$allBookings = $pdo->query("SELECT ddp.*, tk.HoTen, tk.SDT, p.SoPhong 
                            FROM don_dat_phong ddp
                            INNER JOIN tai_khoan tk ON ddp.MaTK = tk.MaTK
                            INNER JOIN phong p ON ddp.MaPhong = p.MaPhong
                            ORDER BY ddp.MaDon DESC")->fetchAll();

// Thống kê nhanh cho dashboard (theo đúng số liệu mockup: Phòng Trống / Phòng Có Khách / Phòng Đã Được Đặt / Số Lượng Khách)
$soPhongTrong   = $pdo->query("SELECT COUNT(*) FROM phong WHERE TrangThai = 'Available'")->fetchColumn();
$soPhongCoKhach = $pdo->query("SELECT COUNT(*) FROM phong WHERE TrangThai = 'Occupied'")->fetchColumn();
$soPhongDaDat   = $pdo->query("SELECT COUNT(*) FROM phong WHERE TrangThai = 'Reserved'")->fetchColumn();
$soLuongKhach   = $pdo->query("SELECT COUNT(*) FROM tai_khoan WHERE VaiTro = 'ThanhVien'")->fetchColumn();
?>

<div class="row g-4 my-2">
    <!-- SIDEBAR QUẢN TRỊ -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm bg-white rounded-3 p-3 position-sticky" style="top: 20px;">
            <div class="d-flex align-items-center gap-2 px-2 py-3 border-bottom mb-2">
                <div class="bg-navy text-gold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <span>👤</span>
                </div>
                <!-- <strong class="text-navy"><?php echo htmlspecialchars($_SESSION['user']['HoTen']); ?></strong> -->
            </div>
            <div class="list-group list-group-flush">
                <a href="admin.php" class="list-group-item list-group-item-action border-0 rounded-3 fw-bold text-navy bg-light mb-1">🏠 Trang Chủ</a>
                <a href="admin.php" class="list-group-item list-group-item-action border-0 rounded-3 text-secondary mb-1">📋 Đơn Đặt Phòng</a>
                <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 text-muted mb-1" tabindex="-1">🛏️ Quản Lý Phòng <span class="badge bg-light text-muted border ms-1">sắp có</span></a>
                <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 text-muted mb-1" tabindex="-1">💰 Quản Lý Khuyến Mãi <span class="badge bg-light text-muted border ms-1">sắp có</span></a>
                <a href="#" class="list-group-item list-group-item-action border-0 rounded-3 text-muted mb-1" tabindex="-1">🏨 Thông Tin Khách Sạn <span class="badge bg-light text-muted border ms-1">sắp có</span></a>
                <hr>
                <a href="logout.php" class="list-group-item list-group-item-action border-0 rounded-3 text-danger">🚪 Đăng Xuất</a>
            </div>
        </div>
    </div>

    <!-- NỘI DUNG CHÍNH -->
    <div class="col-lg-9">
        <div class="bg-navy text-white p-4 rounded-3 shadow-sm mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="font-luxury text-gold fw-bold mb-0">HỆ THỐNG QUẢN TRỊ CAO CẤP</h2>
                <p class="small text-white-50 mb-0">Theo dõi dòng tiền, thông tin khách hàng và điều hành phòng nghỉ</p>
            </div>
            <span class="badge bg-danger p-2 fs-6">Quyền hạn: ROOT_ADMIN</span>
        </div>

        <!-- THỐNG KÊ NHANH -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                    <span class="text-muted small">Phòng Trống</span>
                    <h3 class="fw-bold text-navy mb-0"><?php echo $soPhongTrong; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                    <span class="text-muted small">Phòng Có Khách</span>
                    <h3 class="fw-bold text-info mb-0"><?php echo $soPhongCoKhach; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                    <span class="text-muted small">Phòng Đã Được Đặt</span>
                    <h3 class="fw-bold text-warning mb-0"><?php echo $soPhongDaDat; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white">
                    <span class="text-muted small">Số Lượng Khách</span>
                    <h3 class="fw-bold text-success mb-0"><?php echo $soLuongKhach; ?></h3>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-white rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="font-luxury text-navy fw-bold mb-0">DANH SÁCH TOÀN BỘ ĐƠN ĐẶT PHÒNG HỆ THỐNG</h4>
                <input type="text" id="bookingSearch" class="form-control form-control-sm w-auto" placeholder="🔍 Tìm Kiếm" onkeyup="filterBookingTable()">
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle small text-center" id="bookingTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Đơn</th>
                            <th>Mã Xác Nhận</th>
                            <th>Khách Hàng</th>
                            <th>Số Phòng</th>
                            <th>Thời Gian Nghỉ</th>
                            <th>Tổng Thanh Toán</th>
                            <th>Trạng Thái Đơn</th>
                            <th>Thao Tác Quản Lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($allBookings)): ?>
                            <?php foreach($allBookings as $ab): ?>
                                <tr>
                                    <td><?php echo $ab['MaDon']; ?></td>
                                    <td class="fw-bold text-primary"><?php echo $ab['MaXacNhan']; ?></td>
                                    <td class="text-start">
                                        <strong><?php echo htmlspecialchars($ab['HoTen']); ?></strong><br>
                                        <small class="text-muted">📞 <?php echo htmlspecialchars($ab['SDT']); ?></small>
                                    </td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($ab['SoPhong']); ?></td>
                                    <td><?php echo $ab['NgayNhan']; ?> / <?php echo $ab['NgayTra']; ?></td>
                                    <td class="text-danger fw-bold"><?php echo fmtVND($ab['TongTien']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $ab['TrangThaiDon'] === 'ChoXacNhan' ? 'warning' : ($ab['TrangThaiDon'] === 'DaXacNhan' ? 'info' : 'success'); ?>">
                                            <?php echo $ab['TrangThaiDon']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Thay đổi</button>
                                            <ul class="dropdown-menu shadow">
                                                <li><a class="dropdown-menu-item text-success dropdown-item small" href="admin.php?update_order_id=<?php echo $ab['MaDon']; ?>&new_status=DaXacNhan">✓ Xác nhận đơn</a></li>
                                                <li><a class="dropdown-menu-item text-primary dropdown-item small" href="admin.php?update_order_id=<?php echo $ab['MaDon']; ?>&new_status=HoanTat">🏆 Hoàn tất checkout</a></li>
                                                <li><a class="dropdown-menu-item text-danger dropdown-item small" href="admin.php?update_order_id=<?php echo $ab['MaDon']; ?>&new_status=DaHuy">✕ Hủy đơn đặt</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">Chưa ghi nhận dữ liệu giao dịch đơn hàng nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterBookingTable() {
    const input = document.getElementById('bookingSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#bookingTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>

<?php include_once 'footer.php'; ?>
