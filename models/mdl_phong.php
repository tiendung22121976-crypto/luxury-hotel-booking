<?php
// Gọi file kết nối Database vào đây
require_once '../config/database.php';

function timPhongTrong($maKS, $ngayNhan, $ngayTra)
{
    global $pdo; // Sử dụng biến kết nối PDO từ database.php

    // Câu lệnh SQL "Lõi"
    $sql = "
    SELECT p.MaPhong, p.SoPhong, lp.TenLoai, lp.DonGia, lp.MoTa 
    FROM phong p
    JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
    WHERE p.MaKS = :maKS 
    AND p.TrangThai = 'Available'
    AND p.MaPhong NOT IN (
        SELECT MaPhong 
        FROM don_dat_phong 
        WHERE TrangThaiDon != 'DaHuy' 
        AND (NgayNhan < :ngayTra AND NgayTra > :ngayNhan)
    )
";

    try {
        $stmt = $pdo->prepare($sql);
        // Gắn dữ liệu an toàn để chống SQL Injection
        $stmt->bindParam(':maKS', $maKS);
        $stmt->bindParam(':ngayNhan', $ngayNhan);
        $stmt->bindParam(':ngayTra', $ngayTra);

        $stmt->execute();

        // Trả về mảng chứa danh sách các phòng trống tìm được
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Lỗi thuật toán tìm phòng: " . $e->getMessage();
        return [];
    }
}
// =======================================================
// CÁC HÀM DÀNH CHO PHÂN HỆ QUẢN TRỊ ADMIN (CRUD PHÒNG)
// =======================================================

function getAllPhongAdmin() {
    global $pdo;
    // Dùng JOIN để lấy tên thật của Khách sạn và Loại phòng thay vì chỉ hiển thị Mã code
    $sql = "SELECT p.*, ks.TenKS, lp.TenLoai 
            FROM phong p 
            JOIN khach_san ks ON p.MaKS = ks.MaKS 
            JOIN loai_phong lp ON p.MaLoai = lp.MaLoai 
            ORDER BY p.MaKS ASC, p.SoPhong ASC";
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) { return []; }
}

function getPhongById($maPhong) {
    global $pdo;
    $sql = "SELECT * FROM phong WHERE MaPhong = :maPhong";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maPhong', $maPhong);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) { return false; }
}

function checkPhongExists($maPhong) {
    global $pdo;
    $sql = "SELECT COUNT(*) as count FROM phong WHERE MaPhong = :maPhong";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maPhong', $maPhong);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    } catch(PDOException $e) { return false; }
}

// Hai hàm hỗ trợ lấy danh sách thả xuống (Dropdown list)
function getDanhSachKS() {
    global $pdo;
    $sql = "SELECT MaKS, TenKS FROM khach_san";
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) { return []; }
}

function getDanhSachLoaiPhong() {
    global $pdo;
    $sql = "SELECT MaLoai, TenLoai FROM loai_phong";
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) { return []; }
}

function addPhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai) {
    global $pdo;
    $sql = "INSERT INTO phong (MaPhong, SoPhong, MaKS, MaLoai, TrangThai) VALUES (:maPhong, :soPhong, :maKS, :maLoai, :trangThai)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maPhong', $maPhong);
        $stmt->bindParam(':soPhong', $soPhong);
        $stmt->bindParam(':maKS', $maKS);
        $stmt->bindParam(':maLoai', $maLoai);
        $stmt->bindParam(':trangThai', $trangThai);
        return $stmt->execute();
    } catch(PDOException $e) { return false; }
}

function updatePhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai) {
    global $pdo;
    $sql = "UPDATE phong SET SoPhong = :soPhong, MaKS = :maKS, MaLoai = :maLoai, TrangThai = :trangThai WHERE MaPhong = :maPhong";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maPhong', $maPhong);
        $stmt->bindParam(':soPhong', $soPhong);
        $stmt->bindParam(':maKS', $maKS);
        $stmt->bindParam(':maLoai', $maLoai);
        $stmt->bindParam(':trangThai', $trangThai);
        return $stmt->execute();
    } catch(PDOException $e) { return false; }
}

function kiemTraPhongDangHoatDong($maPhong) {
    global $pdo;
    $sql = "SELECT COUNT(*) as count FROM don_dat_phong WHERE MaPhong = :maPhong AND TrangThaiDon NOT IN ('DaHuy', 'HoanTat')";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maPhong', $maPhong);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    } catch(PDOException $e) { return false; }
}

function deletePhong($maPhong) {
    global $pdo;
    $sql = "DELETE FROM phong WHERE MaPhong = :maPhong";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maPhong', $maPhong);
        return $stmt->execute();
    } catch(PDOException $e) { return false; }
}
