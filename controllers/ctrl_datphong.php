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

    // 3. Kiểm tra xung đột phòng lần cuối (chống trùng lịch khi 2 người đặt cùng lúc)
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) FROM don_dat_phong
        WHERE MaPhong = :maPhong AND TrangThaiDon != 'DaHuy'
          AND NgayNhan < :ngayTra AND NgayTra > :ngayNhan
    ");
    $stmtCheck->execute([
        ':maPhong'  => $maPhong,
        ':ngayNhan' => $ngayNhan,
        ':ngayTra'  => $ngayTra
    ]);

    if ((int)$stmtCheck->fetchColumn() > 0) {
        header("Location: ../views/room-detail.php?id=" . urlencode($maPhong) . "&err=CONFLICT");
        exit;
    }

    // 4. Tính toán tài chính
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
        }
    }
    $tongThanhToan = round($tienPhong * (1 - $phanTramGiam / 100));

    // 5. Sinh mã xác nhận đặt phòng duy nhất
    $maTinh = strpos($phong['DiaChi'], 'Đà Nẵng') !== false ? 'DA'
        : (strpos($phong['DiaChi'], 'Hồ Chí Minh') !== false ? 'SG' : 'HN');
    do {
        $maXacNhanMoi = sinhMaXacNhan($maTinh);
        $stmtTrung = $pdo->prepare("SELECT COUNT(*) FROM don_dat_phong WHERE MaXacNhan = :ma");
        $stmtTrung->execute([':ma' => $maXacNhanMoi]);
    } while ((int)$stmtTrung->fetchColumn() > 0);

    // 6. Ghi vào CSDL bằng Transaction
    try {
        $pdo->beginTransaction();

        $stmtInsert = $pdo->prepare("
            INSERT INTO don_dat_phong (MaTK, MaPhong, NgayNhan, NgayTra, TongTien, MaKM, MaXacNhan, TrangThaiDon)
            VALUES (:maTK, :maPhong, :ngayNhan, :ngayTra, :tongTien, :maKM, :maXacNhan, 'DaXacNhan')
        ");
        $maTKInsert = daDangNhap() ? ($_SESSION['MaTK'] ?? null) : null;
        $stmtInsert->bindValue(':maTK', $maTKInsert, $maTKInsert === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmtInsert->execute([
            ':maPhong'   => $maPhong,
            ':ngayNhan'  => $ngayNhan,
            ':ngayTra'   => $ngayTra,
            ':tongTien'  => $tongThanhToan,
            ':maKM'      => $maKMHopLe,
            ':maXacNhan' => $maXacNhanMoi
        ]);

        if ($ngayNhan <= date('Y-m-d')) {
            $pdo->prepare("UPDATE phong SET TrangThai = 'Reserved' WHERE MaPhong = :maPhong")
                ->execute([':maPhong' => $maPhong]);
        }

        $pdo->commit();

        // Thành công -> Bắt hướng trở lại trang chi tiết kèm mã xác nhận
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
