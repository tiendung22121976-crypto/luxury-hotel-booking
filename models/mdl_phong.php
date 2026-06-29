<?php

/**
 * models/mdl_phong.php
 * Các hàm truy vấn CSDL cho bảng phong, loai_phong
 * Bao gồm: tìm phòng trống, CRUD admin, helper dropdown
 */
require_once __DIR__ . '/../config/database.php';

// ============================================================
// HÀM DÙNG CHUNG CHO PHÍA KHÁCH (FRONTEND)
// ============================================================

/**
 * Tìm phòng trống theo địa điểm (MaKS) và khoảng ngày
 * Logic: phòng Available, chưa có đơn đặt còn hiệu lực trùng ngày
 */
function timPhongTrong($maKS, $ngayNhan, $ngayTra)
{
    global $pdo;
    $sql = "
        SELECT p.MaPhong, p.SoPhong, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch, lp.MoTa,
               ks.TenKS, ks.DiaChi
        FROM phong p
        JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
        JOIN khach_san  ks ON p.MaKS   = ks.MaKS
        WHERE p.MaKS = :maKS
          AND p.TrangThai = 'Available'
          AND p.MaPhong NOT IN (
              SELECT MaPhong FROM don_dat_phong
              WHERE TrangThaiDon != 'DaHuy'
                AND NgayNhan < :ngayTra
                AND NgayTra  > :ngayNhan
          )
        ORDER BY lp.DonGia ASC
    ";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':maKS' => $maKS, ':ngayNhan' => $ngayNhan, ':ngayTra' => $ngayTra]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Lấy chi tiết 1 phòng kèm thông tin loại và khách sạn (dùng cho trang room-detail)
 */
function getChiTietPhong($maPhong)
{
    global $pdo;
    $sql = "
        SELECT p.*, lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch, lp.MoTa AS MoTaLoai,
               ks.TenKS, ks.DiaChi
        FROM phong p
        JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
        JOIN khach_san  ks ON p.MaKS   = ks.MaKS
        WHERE p.MaPhong = :maPhong
    ";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':maPhong' => $maPhong]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Lấy danh sách phòng nổi bật cho trang chủ (Available, giới hạn số lượng)
 */
function getPhongNoiBat($limit = 6)
{
    global $pdo;
    $sql = "
        SELECT p.MaPhong, p.SoPhong, p.TrangThai,
               lp.TenLoai, lp.DonGia, lp.DienTich, lp.TienIch, lp.MoTa,
               ks.TenKS, ks.DiaChi
        FROM phong p
        JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
        JOIN khach_san  ks ON p.MaKS   = ks.MaKS
        WHERE p.TrangThai = 'Available'
        ORDER BY lp.DonGia DESC
        LIMIT :limit
    ";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ============================================================
// HÀM CRUD DÀNH CHO ADMIN
// ============================================================

function getAllPhongAdmin()
{
    global $pdo;
    $sql = "
        SELECT p.*, ks.TenKS, lp.TenLoai, lp.DonGia
        FROM phong p
        JOIN khach_san  ks ON p.MaKS   = ks.MaKS
        JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
        ORDER BY p.MaKS ASC, p.SoPhong ASC
    ";
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function getPhongById($maPhong)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM phong WHERE MaPhong = :maPhong");
        $stmt->execute([':maPhong' => $maPhong]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

function checkPhongExists($maPhong)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM phong WHERE MaPhong = :maPhong");
        $stmt->execute([':maPhong' => $maPhong]);
        return $stmt->fetch()['cnt'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Dropdown helper: danh sách khách sạn
function getDanhSachKS()
{
    global $pdo;
    try {
        return $pdo->query("SELECT MaKS, TenKS FROM khach_san ORDER BY TenKS ASC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Dropdown helper: danh sách loại phòng
function getDanhSachLoaiPhong()
{
    global $pdo;
    try {
        return $pdo->query("SELECT MaLoai, TenLoai, DonGia FROM loai_phong ORDER BY DonGia ASC")->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function addPhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO phong (MaPhong, SoPhong, MaKS, MaLoai, TrangThai)
             VALUES (:maPhong, :soPhong, :maKS, :maLoai, :trangThai)"
        );
        return $stmt->execute([
            ':maPhong'   => $maPhong,
            ':soPhong'   => $soPhong,
            ':maKS'      => $maKS,
            ':maLoai'    => $maLoai,
            ':trangThai' => $trangThai,
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function updatePhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "UPDATE phong SET SoPhong = :soPhong, MaKS = :maKS, MaLoai = :maLoai, TrangThai = :trangThai
             WHERE MaPhong = :maPhong"
        );
        return $stmt->execute([
            ':maPhong'   => $maPhong,
            ':soPhong'   => $soPhong,
            ':maKS'      => $maKS,
            ':maLoai'    => $maLoai,
            ':trangThai' => $trangThai,
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

// Kiểm tra trước khi xóa: không xóa nếu còn đơn đặt chưa kết thúc
function kiemTraPhongDangHoatDong($maPhong)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as cnt FROM don_dat_phong
             WHERE MaPhong = :maPhong AND TrangThaiDon NOT IN ('DaHuy','HoanTat')"
        );
        $stmt->execute([':maPhong' => $maPhong]);
        return $stmt->fetch()['cnt'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function deletePhong($maPhong)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM phong WHERE MaPhong = :maPhong");
        return $stmt->execute([':maPhong' => $maPhong]);
    } catch (PDOException $e) {
        return false;
    }
}
/**
 * Xóa đơn đặt phòng nếu phòng liên quan không ở trạng thái hoạt động
 * Trạng thái không được xóa: 'Reserved', 'Occupied' (hoặc đơn chưa kết thúc)
 */

