<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Phòng Khách Sạn</title>
    <!-- Nhúng Bootstrap 5 CSS để giao diện đẹp và responsive nhanh -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .room-card { transition: transform 0.2s; }
        .room-card:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<?php
// Giả lập dữ liệu phòng từ Database để đổ ra giao diện bằng PHP
$rooms = [
    [
        "id" => 1,
        "name" => "Phòng Standard (STD)",
        "price" => 500000,
        "image" => "https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=500",
        "desc" => "Phòng tiêu chuẩn diện tích 22m², giường Queen, hướng phố nội khu yên tĩnh.",
        "utilities" => ["Wifi miễn phí", "Điều hòa", "Tivi"]
    ],
    [
        "id" => 2,
        "name" => "Phòng Deluxe Ocean View (DLX)",
        "price" => 1200000,
        "image" => "https://images.unsplash.com/photo-1590490360182-c33d57733427?w=500",
        "desc" => "Phòng cao cấp 30m², ban công rộng hướng trực diện biển, đầy đủ tiện nghi sang trọng.",
        "utilities" => ["Wifi miễn phí", "Điều hòa", "Ban công biển", "Bồn tắm", "Mini Bar"]
    ]
];

// Xử lý khi ấn nút Đặt phòng (Giả lập nhận dữ liệu)
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_booking'])) {
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $room_id = $_POST['room_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    
    // Tìm tên phòng đã chọn
    $selected_room = "";
    foreach ($rooms as $r) {
        if ($r['id'] == $room_id) {
            $selected_room = $r['name'];
            break;
        }
    }

    $message = "<div class='alert alert-success mt-3'>
                    <strong>Đặt phòng thành công!</strong><br>
                    Khách hàng: $customer_name ($phone)<br>
                    Loại phòng: $selected_room<br>
                    Thời gian: $checkin đến $checkout
                </div>";
}
?>

<div class="container my-5">
    <h2 class="text-center mb-4 text-uppercase fw-bold text-primary">Hệ Thống Đặt Phòng Trực Tuyến</h2>
    
    <!-- Hiển thị thông báo đặt phòng thành công nếu có -->
    <?php echo $message; ?>

    <div class="row g-4">
        <!-- CỘT TRÁI: DANH SÁCH PHÒNG TRỐNG -->
        <div class="col-lg-8">
            <h4 class="mb-3 fw-semibold">Chọn loại phòng</h4>
            
            <?php foreach ($rooms as $room): ?>
                <div class="card mb-4 room-card overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="<?php echo $room['image']; ?>" class="img-fluid h-100 w-100 object-cover" alt="<?php echo $room['name']; ?>" style="min-height: 200px;">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body d-flex flex-column h-100">
                                <h5 class="card-title fw-bold text-dark"><?php echo $room['name']; ?></h5>
                                <p class="card-text text-muted small flex-grow-1"><?php echo $room['desc']; ?></p>
                                
                                <div class="mb-3">
                                    <?php foreach ($room['utilities'] as $util): ?>
                                        <span class="badge bg-light text-dark border me-1"><?php echo $util; ?></span>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="text-danger fw-bold fs-5"><?php echo number_format($room['price'], 0, ',', '.'); ?> đ / đêm</span>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectRoom(<?php echo $room['id']; ?>, '<?php echo $room['name']; ?>')">Chọn phòng này</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- CỘT PHẢI: FORM THÔNG TIN ĐẶT PHÒNG -->
        <div class="col-lg-4">
            <div class="card p-4 shadow-sm sticky-top" style="top: 20px; z-index: 10;">
                <h4 class="mb-3 fw-semibold text-center border-bottom pb-2">Thông tin đặt phòng</h4>
                
                <form action="" method="POST">
                    <!-- Chọn phòng ẩn, được điền tự động khi ấn "Chọn phòng này" -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Phòng đã chọn</label>
                        <select class="form-select" name="room_id" id="room_select" required>
                            <option value="" disabled selected>-- Vui lòng chọn phòng --</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>"><?php echo $room['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Họ và tên</label>
                        <input type="text" class="form-control" name="customer_name" placeholder="Nguyễn Văn A" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Số điện thoại</label>
                        <input type="tel" class="form-control" name="phone" placeholder="0901234567" required>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-medium">Ngày nhận</label>
                            <input type="date" class="form-control" name="checkin" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-medium">Ngày trả</label>
                            <input type="date" class="form-control" name="checkout" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                    </div>

                    <button type="submit" name="btn_booking" class="btn btn-primary w-100 py-2 fw-bold mt-2">XÁC NHẬN ĐẶT PHÒNG</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript bổ trợ trải nghiệm người dùng -->
<script>
function selectRoom(roomId, roomName) {
    // Tự động thay đổi giá trị trong thẻ select ở cột phải khi khách bấm nút chọn phòng ở cột trái
    const roomSelect = document.getElementById('room_select');
    roomSelect.value = roomId;
    
    // Cuộn màn hình mượt mà đến khu vực điền form nếu dùng trên điện thoại
    roomSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>