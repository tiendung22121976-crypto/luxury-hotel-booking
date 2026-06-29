<?php

/**
 * controllers/ctrl_datphong.php
 * Controller xử lý nghiệp vụ đặt phòng (UC03)
 */
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../models/mdl_phong.php';
require_once '../models/mdl_khuyen_mai.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'datPhong') {

    $maPhong       = trim($_POST['maPhong'] ?? '');
    $ngayNhan      = trim($_POST['ngayNhan'] ?? '');
    $ngayTra       = trim($_POST['ngayTra'] ?? '');
    $hoTenNguoiDat = trim($_POST['hoTen'] ?? (trim($_POST['ten'] ?? '') . ' ' . trim($_POST['ho'] ?? '')));
    $emailNguoiDat = trim($_POST['email'] ?? '');
    $sdtNguoiDat   = trim($_POST['sdt'] ?? '');
    $yeuCauDacBiet = trim($_POST['yeuCauDacBiet'] ?? '');
    $maKM          = trim($_POST['maKM'] ?? '');

    if ($maPhong === '') {
        header('Location: ../views/search.php');
        exit;
    }

    // 1. Validate dữ liệu đầu vào cơ bản
    if (!$ngayNhan || !$ngayTra) {
        header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&err=" . urlencode("Vui lòng chọn ngày nhận và trả phòng."));
        exit;
    }
    if (strtotime($ngayTra) <= strtotime($ngayNhan)) {
        header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&err=" . urlencode("Ngày trả phải sau ngày nhận phòng."));
        exit;
    }
    if (strtotime($ngayNhan) < strtotime(date('Y-m-d'))) {
        header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&err=" . urlencode("Ngày nhận phòng không thể là ngày trong quá khứ."));
        exit;
    }
    if (!$hoTenNguoiDat || !$emailNguoiDat || !$sdtNguoiDat) {
        header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&err=" . urlencode("Vui lòng điền đầy đủ thông tin người đặt."));
        exit;
    }

    // 2. Lấy thông tin đơn giá và địa chỉ phòng từ CSDL
    $stmtP = $pdo->prepare("
        SELECT p.MaPhong, lp.DonGia, ks.DiaChi 
        FROM phong p 
        INNER JOIN loai_phong lp ON p.MaLoai = lp.MaLoai 
        INNER JOIN khach_san ks ON p.MaKS = ks.MaKS 
        WHERE p.MaPhong = :maPhong LIMIT 1
    ");
    $stmtP->execute([':maPhong' => $maPhong]);
    $phong = $stmtP->fetch();

    if (!$phong) {
        header('Location: ../views/search.php');
        exit;
    }

    // 3. Tính toán tài chính
    $soDem     = tinhSoDem($ngayNhan, $ngayTra);
    $tienPhong = $phong['DonGia'] * $soDem;

    $phanTramGiam = 0;
    $maKMHopLe    = null;
    if ($maKM !== '') {
        $stmtKM = $pdo->prepare("
            SELECT MaKM, PhanTramGiam FROM khuyen_mai
            WHERE MaKM = :maKM AND CURDATE() BETWEEN NgayBatDau AND NgayKetThuc
        ");
        $stmtKM->execute([':maKM' => $maKM]);
        $km = $stmtKM->fetch();
        if ($km) {
            $phanTramGiam = (int)$km['PhanTramGiam'];
            $maKMHopLe    = $km['MaKM'];
        } else {
            header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&ngayNhan=" . urlencode($ngayNhan) . "&ngayTra=" . urlencode($ngayTra) . "&err=" . urlencode("Mã khuyến mãi không tồn tại hoặc đã hết hạn."));
            exit;
        }
    }
    $tongThanhToan = round($tienPhong * (1 - $phanTramGiam / 100));

    // 4. Sinh mã xác nhận đặt phòng duy nhất
    $maTinh = strpos($phong['DiaChi'], 'Đà Nẵng') !== false ? 'DA'
        : (strpos($phong['DiaChi'], 'Hồ Chí Minh') !== false ? 'SG' : 'HN');
    do {
        $maXacNhanMoi = sinhMaXacNhan($maTinh);
        $stmtTrung = $pdo->prepare("SELECT COUNT(*) FROM don_dat_phong WHERE MaXacNhan = :ma");
        $stmtTrung->execute([':ma' => $maXacNhanMoi]);
    } while ((int)$stmtTrung->fetchColumn() > 0);

    // 5. Chạy Transaction bảo mật cao
    try {
        $pdo->beginTransaction();

        // Khóa phòng này lại để tránh Race Condition
        $stmtLock = $pdo->prepare("SELECT MaPhong FROM phong WHERE MaPhong = :maPhong FOR UPDATE");
        $stmtLock->execute(['maPhong' => $maPhong]);

        // Kiểm tra trùng lịch bằng hàm DATE()
        $stmtCheck = $pdo->prepare("
            SELECT COUNT(*) FROM don_dat_phong
            WHERE MaPhong = :maPhong 
              AND TrangThaiDon != 'DaHuy'
              AND DATE(NgayNhan) < DATE(:ngayTra) 
              AND DATE(NgayTra) > DATE(:ngayNhan)
        ");
        $stmtCheck->execute([
            'maPhong'  => $maPhong,
            'ngayNhan' => $ngayNhan,
            'ngayTra'  => $ngayTra
        ]);

        // Nếu đã có người đặt trong khung giờ này, báo lỗi CONFLICT
        if ((int)$stmtCheck->fetchColumn() > 0) {
            $pdo->rollBack();
            header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&err=CONFLICT");
            exit;
        }

        // Tiến hành chèn đơn đặt phòng mới
        $stmtInsert = $pdo->prepare("
            INSERT INTO don_dat_phong (MaTK, MaPhong, NgayNhan, NgayTra, TongTien, MaKM, MaXacNhan, TrangThaiDon, TenKhachVangLai)
            VALUES (:maTK, :maPhong, :ngayNhan, :ngayTra, :tongTien, :maKM, :maXacNhan, 'ChoXacNhan', :tenKhach)
        ");

        $maTKInsert = daDangNhap() ? ($_SESSION['MaTK'] ?? null) : null;

        $stmtInsert->execute([
            'maTK'       => $maTKInsert,
            'maPhong'    => $maPhong,
            'ngayNhan'   => $ngayNhan,
            'ngayTra'    => $ngayTra,
            'tongTien'   => $tongThanhToan,
            'maKM'       => $maKMHopLe,
            'maXacNhan'  => $maXacNhanMoi,
            'tenKhach'   => $hoTenNguoiDat
        ]);

        if ($ngayNhan <= date('Y-m-d')) {
            $pdo->prepare("UPDATE phong SET TrangThai = 'Reserved' WHERE MaPhong = :maPhong")
                ->execute(['maPhong' => $maPhong]);
        }

        $pdo->commit();

        header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&res_code=" . urlencode($maXacNhanMoi));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&err=" . urlencode("Hệ thống đang bận, thao tác đặt phòng chưa thành công. Vui lòng thử lại sau."));
        exit;
    }
    
} else {
    header('Location: ../views/search.php');
    exit;
}