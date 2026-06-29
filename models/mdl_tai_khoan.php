<?php

/**
 * models/mdl_tai_khoan.php
 * Các hàm truy vấn CSDL cho bảng tai_khoan (CRUD Admin)
 * Dùng cho phân hệ Quản Lý Tài Khoản trong views/admin.php
 */
require_once __DIR__ . '/../config/database.php';

/**
 * Lấy toàn bộ tài khoản, có thể lọc theo từ khóa tìm kiếm (họ tên/email/sđt)
 */
function getAllTaiKhoanAdmin($tuKhoa = '')
{
    global $pdo;
    try {
        if ($tuKhoa !== '') {
            $stmt = $pdo->prepare(
                "SELECT * FROM tai_khoan
                 WHERE HoTen LIKE :tk OR Email LIKE :tk OR SDT LIKE :tk
                 ORDER BY MaTK ASC"
            );
            $stmt->execute([':tk' => '%' . $tuKhoa . '%']);
        } else {
            $stmt = $pdo->query("SELECT * FROM tai_khoan ORDER BY MaTK ASC");
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function getTaiKhoanById($maTK)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM tai_khoan WHERE MaTK = :maTK");
        $stmt->execute([':maTK' => $maTK]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

function checkTaiKhoanTrungEmailSdt($email, $sdt, $maTKLoaiTru = null)
{
    global $pdo;
    try {
        $sql = "SELECT COUNT(*) as cnt FROM tai_khoan WHERE (Email = :email OR SDT = :sdt)";
        $params = [':email' => $email, ':sdt' => $sdt];
        if ($maTKLoaiTru !== null) {
            $sql .= " AND MaTK != :maTK";
            $params[':maTK'] = $maTKLoaiTru;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['cnt'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Thêm tài khoản mới (dùng bởi Admin). Mật khẩu được mã hóa Bcrypt.
 */
function addTaiKhoan($hoTen, $email, $sdt, $matKhau, $vaiTro, $trangThai = 'HoatDong')
{
    global $pdo;
    try {
        $matKhauHash = password_hash($matKhau, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO tai_khoan (HoTen, Email, SDT, MatKhau, VaiTro, TrangThai)
             VALUES (:hoTen, :email, :sdt, :matKhau, :vaiTro, :trangThai)"
        );
        return $stmt->execute([
            ':hoTen'     => $hoTen,
            ':email'     => $email,
            ':sdt'       => $sdt,
            ':matKhau'   => $matKhauHash,
            ':vaiTro'    => $vaiTro,
            ':trangThai' => $trangThai,
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Cập nhật thông tin tài khoản. Nếu $matKhauMoi rỗng thì giữ mật khẩu cũ.
 */
function updateTaiKhoan($maTK, $hoTen, $email, $sdt, $vaiTro, $trangThai, $matKhauMoi = '')
{
    global $pdo;
    try {
        if ($matKhauMoi !== '') {
            $stmt = $pdo->prepare(
                "UPDATE tai_khoan
                 SET HoTen = :hoTen, Email = :email, SDT = :sdt, VaiTro = :vaiTro,
                     TrangThai = :trangThai, MatKhau = :matKhau
                 WHERE MaTK = :maTK"
            );
            return $stmt->execute([
                ':hoTen'     => $hoTen,
                ':email'     => $email,
                ':sdt'       => $sdt,
                ':vaiTro'    => $vaiTro,
                ':trangThai' => $trangThai,
                ':matKhau'   => password_hash($matKhauMoi, PASSWORD_BCRYPT),
                ':maTK'      => $maTK,
            ]);
        }
        $stmt = $pdo->prepare(
            "UPDATE tai_khoan
             SET HoTen = :hoTen, Email = :email, SDT = :sdt, VaiTro = :vaiTro, TrangThai = :trangThai
             WHERE MaTK = :maTK"
        );
        return $stmt->execute([
            ':hoTen'     => $hoTen,
            ':email'     => $email,
            ':sdt'       => $sdt,
            ':vaiTro'    => $vaiTro,
            ':trangThai' => $trangThai,
            ':maTK'      => $maTK,
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Kiểm tra trước khi xóa: không xóa nếu tài khoản còn đơn đặt phòng hoặc đánh giá
function kiemTraTaiKhoanCoDuLieu($maTK)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM don_dat_phong WHERE MaTK = :maTK1) +
                (SELECT COUNT(*) FROM danh_gia WHERE MaTK = :maTK2) AS cnt"
        );
        $stmt->execute([':maTK1' => $maTK, ':maTK2' => $maTK]);
        return $stmt->fetch()['cnt'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function deleteTaiKhoan($maTK)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM tai_khoan WHERE MaTK = :maTK");
        return $stmt->execute([':maTK' => $maTK]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Khóa / Mở khóa nhanh một tài khoản (toggle)
 */
function toggleKhoaTaiKhoan($maTK)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "UPDATE tai_khoan
             SET TrangThai = IF(TrangThai = 'HoatDong', 'BiKhoa', 'HoatDong')
             WHERE MaTK = :maTK"
        );
        return $stmt->execute([':maTK' => $maTK]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Thống kê số lượng tài khoản theo vai trò (cho stat-card trang Tài khoản)
 */
function getThongKeTaiKhoan()
{
    global $pdo;
    try {
        $stmt = $pdo->query(
            "SELECT
                SUM(CASE WHEN VaiTro = 'Admin' THEN 1 ELSE 0 END) AS admin,
                SUM(CASE WHEN VaiTro = 'ThanhVien' THEN 1 ELSE 0 END) AS thanh_vien,
                COUNT(*) AS tong
             FROM tai_khoan"
        );
        return $stmt->fetch();
    } catch (PDOException $e) {
        return ['admin' => 0, 'thanh_vien' => 0, 'tong' => 0];
    }
}
