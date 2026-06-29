<?php
/**
 * models/mdl_don_dat_phong.php
 * Các hàm truy vấn CSDL cho bảng don_dat_phong dùng cho phân hệ
 * Quản Lý Đặt Phòng và Trang Chủ (Dashboard) của Admin.
 */
require_once __DIR__ . '/../config/database.php';

/**
 * Lấy danh sách đơn đặt phòng kèm thông tin khách, phòng, khách sạn.
 * $limit = 0 nghĩa là lấy tất cả.
 */
function getDonDatPhongAdmin($limit = 0, $tuKhoa = '') {
    global $pdo;
    $sql = "
        SELECT ddp.*, p.SoPhong, lp.TenLoai, ks.TenKS,
               COALESCE(tk.HoTen, ddp.TenKhachVangLai, 'Khách lẻ') AS TenKhach
        FROM don_dat_phong ddp
        JOIN phong p       ON ddp.MaPhong = p.MaPhong
        JOIN loai_phong lp ON p.MaLoai    = lp.MaLoai
        JOIN khach_san ks  ON p.MaKS      = ks.MaKS
        LEFT JOIN tai_khoan tk ON ddp.MaTK = tk.MaTK
    ";
    $params = [];
    if ($tuKhoa !== '') {
        $sql .= " WHERE tk.HoTen LIKE :tk OR ddp.MaXacNhan LIKE :tk OR p.SoPhong LIKE :tk";
        $params[':tk'] = '%' . $tuKhoa . '%';
    }
    $sql .= " ORDER BY ddp.NgayTao DESC";
    if ($limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) { return []; }
}

function getDonDatPhongById($maDon) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT ddp.*, p.SoPhong, lp.TenLoai, ks.TenKS,
                   COALESCE(tk.HoTen, 'Khách lẻ (không tài khoản)') AS TenKhach
            FROM don_dat_phong ddp
            JOIN phong p       ON ddp.MaPhong = p.MaPhong
            JOIN loai_phong lp ON p.MaLoai    = lp.MaLoai
            JOIN khach_san ks  ON p.MaKS      = ks.MaKS
            LEFT JOIN tai_khoan tk ON ddp.MaTK = tk.MaTK
            WHERE ddp.MaDon = :maDon
        ");
        $stmt->execute([':maDon' => $maDon]);
        return $stmt->fetch();
    } catch (PDOException $e) { return false; }
}

/**
 * Xác nhận đơn đặt phòng: ChoXacNhan -> DaXacNhan
 */
function xacNhanDonDatPhong($maDon) {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "UPDATE don_dat_phong SET TrangThaiDon = 'DaXacNhan'
             WHERE MaDon = :maDon AND TrangThaiDon = 'ChoXacNhan'"
        );
        return $stmt->execute([':maDon' => $maDon]);
    } catch (PDOException $e) { return false; }
}

/**
 * Hủy đơn đặt phòng (Admin) + trả phòng về trạng thái Available
 */
function huyDonDatPhongAdmin($maDon) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT MaPhong, TrangThaiDon FROM don_dat_phong WHERE MaDon = :maDon");
        $stmt->execute([':maDon' => $maDon]);
        $don = $stmt->fetch();
        if (!$don || $don['TrangThaiDon'] === 'DaHuy') return false;

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE don_dat_phong SET TrangThaiDon = 'DaHuy' WHERE MaDon = :maDon")
            ->execute([':maDon' => $maDon]);
        $pdo->prepare("UPDATE phong SET TrangThai = 'Available' WHERE MaPhong = :maPhong")
            ->execute([':maPhong' => $don['MaPhong']]);
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

/**
 * Thống kê tổng quan cho Dashboard và tab Quản Lý Đặt Phòng
 */
function getThongKeDonDatPhong() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT
                SUM(CASE WHEN TrangThaiDon = 'ChoXacNhan' THEN 1 ELSE 0 END) AS cho_duyet,
                SUM(CASE WHEN TrangThaiDon = 'DaXacNhan'  THEN 1 ELSE 0 END) AS da_xac_nhan,
                SUM(CASE WHEN TrangThaiDon = 'DaHuy'      THEN 1 ELSE 0 END) AS da_huy,
                SUM(CASE WHEN TrangThaiDon = 'HoanTat'    THEN 1 ELSE 0 END) AS hoan_tat,
                COUNT(*) AS tong_dat_phong,
                COALESCE(SUM(CASE WHEN TrangThaiDon != 'DaHuy'
                                   AND MONTH(NgayTao) = MONTH(CURDATE())
                                   AND YEAR(NgayTao) = YEAR(CURDATE())
                              THEN TongTien ELSE 0 END), 0) AS doanh_thu_thang,
                COALESCE(SUM(CASE WHEN TrangThaiDon != 'DaHuy' THEN TongTien ELSE 0 END), 0) AS doanh_thu_tong
            FROM don_dat_phong
        ");
        return $stmt->fetch();
    } catch (PDOException $e) {
        return [
            'cho_duyet' => 0, 'da_xac_nhan' => 0, 'da_huy' => 0, 'hoan_tat' => 0,
            'tong_dat_phong' => 0, 'doanh_thu_thang' => 0, 'doanh_thu_tong' => 0,
        ];
    }
}

/**
 * Thống kê trạng thái phòng (cho stat-card Dashboard)
 */
function getThongKePhongTheoTrangThai() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT
                COUNT(*) AS tong_phong,
                SUM(CASE WHEN TrangThai = 'Available' THEN 1 ELSE 0 END) AS phong_trong,
                SUM(CASE WHEN TrangThai = 'Occupied'  THEN 1 ELSE 0 END) AS phong_co_khach,
                SUM(CASE WHEN TrangThai = 'Reserved'  THEN 1 ELSE 0 END) AS phong_da_dat,
                SUM(CASE WHEN TrangThai = 'Cleaning'  THEN 1 ELSE 0 END) AS phong_dang_don
            FROM phong
        ");
        return $stmt->fetch();
    } catch (PDOException $e) {
        return ['tong_phong' => 0, 'phong_trong' => 0, 'phong_co_khach' => 0, 'phong_da_dat' => 0, 'phong_dang_don' => 0];
    }
}
function xoaDonDatPhongAdmin($maDon)
{
    global $pdo;
    try {
        $pdo->beginTransaction();

        // 1. Lấy thông tin trạng thái phòng và trạng thái đơn của đơn đặt phòng này
        $stmt = $pdo->prepare("
            SELECT d.MaDon, p.TrangThai AS TrangThaiPhong, d.TrangThaiDon 
            FROM don_dat_phong d
            INNER JOIN phong p ON d.MaPhong = p.MaPhong
            WHERE d.MaDon = :maDon
            FOR UPDATE
        ");
        $stmt->execute([':maDon' => $maDon]);
        $booking = $stmt->fetch();

        if (!$booking) {
            $pdo->rollBack();
            return false;
        }

        // 2. Kiểm tra nếu phòng đang hoạt động (Đang ở hoặc Đã đặt trước) 
        // Hoặc đơn đặt phòng đang trong quá trình xử lý chưa hủy/hoàn tất
        $phongDangHoatDong = in_array($booking['TrangThaiPhong'], ['Reserved', 'Occupied']);
        $donChuaKetThuc    = in_array($booking['TrangThaiDon'], ['ChoXacNhan', 'DaXacNhan']);

        if ($phongDangHoatDong || $donChuaKetThuc) {
            $pdo->rollBack();
            return 'ACTIVE_ROOM'; // Trả về mã lỗi cụ thể để báo cho View
        }

        // 3. Tiến hành xóa đơn nếu hợp lệ
        $stmtDelete = $pdo->prepare("DELETE FROM don_dat_phong WHERE MaDon = :maDon");
        $stmtDelete->execute([':maDon' => $maDon]);

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}