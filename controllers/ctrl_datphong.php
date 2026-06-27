<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'datphong') {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['maTK'])) {
        die("Vui lòng đăng nhập để thực hiện đặt phòng!");
    }

    // Lấy dữ liệu theo chuẩn camelCase
    $maTK = $_SESSION['maTK'];
    $maPhong = $_POST['maPhong'];
    $ngayNhan = $_POST['ngayNhan'];
    $ngayTra = $_POST['ngayTra'];
    $maKM = !empty($_POST['maKM']) ? $_POST['maKM'] : null;

    // Tính số đêm lưu trú
    $timeNhan = strtotime($ngayNhan);
    $timeTra = strtotime($ngayTra);
    if ($timeTra <= $timeNhan) {
        die("Ngày trả phòng phải sau ngày nhận phòng.");
    }
    $soDem = ($timeTra - $timeNhan) / (60 * 60 * 24);

    // Lấy giá phòng gốc từ CSDL (Join giữa bảng phong và loai_phong)
    $stmtGia = $pdo->prepare("SELECT lp.DonGia FROM phong p JOIN loai_phong lp ON p.MaLoai = lp.MaLoai WHERE p.MaPhong = ?");
    $stmtGia->execute([$maPhong]);
    $phong = $stmtGia->fetch(PDO::FETCH_ASSOC);
    
    if (!$phong) {
        die("Phòng không tồn tại.");
    }

    $donGia = $phong['DonGia'];
    $tienPhong = $soDem * $donGia;
    $tongTien = $tienPhong;

    // Xử lý mã khuyến mãi nếu có
    if ($maKM) {
        $stmtKM = $pdo->prepare("SELECT PhanTramGiam FROM khuyen_mai WHERE MaKM = ? AND NgayBatDau <= CURRENT_DATE AND NgayKetThuc >= CURRENT_DATE");
        $stmtKM->execute([$maKM]);
        $khuyenMai = $stmtKM->fetch(PDO::FETCH_ASSOC);

        if ($khuyenMai) {
            $tienKhuyenMai = $tienPhong * ($khuyenMai['PhanTramGiam'] / 100);
            $tongTien = $tienPhong - $tienKhuyenMai;
        } else {
            die("Mã khuyến mãi không tồn tại hoặc đã hết hạn.");
        }
    }

    // Sinh mã xác nhận đặt phòng duy nhất
    $maXacNhan = 'LH' . strtoupper(substr(uniqid(), -6));

    try {
        // Sử dụng Transaction để đảm bảo tính toàn vẹn dữ liệu
        $pdo->beginTransaction();

        // Thêm đơn đặt phòng vào Database
        $stmtDatPhong = $pdo->prepare("INSERT INTO don_dat_phong (MaTK, MaPhong, NgayNhan, NgayTra, TongTien, MaKM, MaXacNhan, TrangThaiDon) VALUES (?, ?, ?, ?, ?, ?, ?, 'ChoXacNhan')");
        $stmtDatPhong->execute([$maTK, $maPhong, $ngayNhan, $ngayTra, $tongTien, $maKM, $maXacNhan]);

        // Cập nhật trạng thái phòng thành 'Reserved'
        $stmtCapNhatPhong = $pdo->prepare("UPDATE phong SET TrangThai = 'Reserved' WHERE MaPhong = ?");
        $stmtCapNhatPhong->execute([$maPhong]);

        // Hoàn tất lưu
        $pdo->commit();
        
        echo "Đặt phòng thành công! Mã xác nhận của bạn là: " . $maXacNhan;

    } catch (Exception $e) {
        // Rollback nếu có lỗi hệ thống
        $pdo->rollBack();
        die("Hệ thống đang bận, thao tác đặt phòng chưa thành công. Vui lòng thử lại sau.");
    }
}
?>