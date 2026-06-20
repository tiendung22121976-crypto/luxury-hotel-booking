<?php
/**
 * models/mdl_khach_san.php
 * Các hàm truy vấn CSDL cho bảng khach_san (CRUD Admin)
 */
require_once __DIR__ . '/../config/database.php';

function getAllKhachSan() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM khach_san ORDER BY MaKS ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) { return []; }
}

function getKhachSanById($maKS) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM khach_san WHERE MaKS = :maKS");
        $stmt->execute([':maKS' => $maKS]);
        return $stmt->fetch();
    } catch (PDOException $e) { return false; }
}

function checkKhachSanExists($maKS) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM khach_san WHERE MaKS = :maKS");
        $stmt->execute([':maKS' => $maKS]);
        return $stmt->fetch()['cnt'] > 0;
    } catch (PDOException $e) { return false; }
}

// Kiểm tra trước khi xóa: không xóa nếu khách sạn còn phòng
function kiemTraKhachSanCoPhong($maKS) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM phong WHERE MaKS = :maKS");
        $stmt->execute([':maKS' => $maKS]);
        return $stmt->fetch()['cnt'] > 0;
    } catch (PDOException $e) { return false; }
}

function addKhachSan($maKS, $tenKS, $diaChi, $moTa) {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO khach_san (MaKS, TenKS, DiaChi, MoTa) VALUES (:maKS, :tenKS, :diaChi, :moTa)"
        );
        return $stmt->execute([':maKS' => $maKS, ':tenKS' => $tenKS, ':diaChi' => $diaChi, ':moTa' => $moTa]);
    } catch (PDOException $e) { return false; }
}

function updateKhachSan($maKS, $tenKS, $diaChi, $moTa) {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "UPDATE khach_san SET TenKS = :tenKS, DiaChi = :diaChi, MoTa = :moTa WHERE MaKS = :maKS"
        );
        return $stmt->execute([':maKS' => $maKS, ':tenKS' => $tenKS, ':diaChi' => $diaChi, ':moTa' => $moTa]);
    } catch (PDOException $e) { return false; }
}

function deleteKhachSan($maKS) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM khach_san WHERE MaKS = :maKS");
        return $stmt->execute([':maKS' => $maKS]);
    } catch (PDOException $e) { return false; }
}
