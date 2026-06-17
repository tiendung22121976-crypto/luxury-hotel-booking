<?php
require_once '../config/database.php';

function getAllKhachSan() {
    global $pdo;
    $sql = "SELECT * FROM khach_san ORDER BY MaKS ASC";
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) { return []; }
}

function getKhachSanById($maKS) {
    global $pdo;
    $sql = "SELECT * FROM khach_san WHERE MaKS = :maKS";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKS', $maKS);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) { return false; }
}

function checkKhachSanExists($maKS) {
    global $pdo;
    $sql = "SELECT COUNT(*) as count FROM khach_san WHERE MaKS = :maKS";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKS', $maKS);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    } catch(PDOException $e) { return false; }
}

// Hàm quan trọng: Kiểm tra trước khi xóa để không bị lỗi CSDL[cite: 4]
function kiemTraKhachSanCoPhong($maKS) {
    global $pdo;
    $sql = "SELECT COUNT(*) as count FROM phong WHERE MaKS = :maKS";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKS', $maKS);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0; // Trả về true nếu có phòng
    } catch(PDOException $e) { return false; }
}

function addKhachSan($maKS, $tenKS, $diaChi, $moTa) {
    global $pdo;
    $sql = "INSERT INTO khach_san (MaKS, TenKS, DiaChi, MoTa) VALUES (:maKS, :tenKS, :diaChi, :moTa)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKS', $maKS);
        $stmt->bindParam(':tenKS', $tenKS);
        $stmt->bindParam(':diaChi', $diaChi);
        $stmt->bindParam(':moTa', $moTa);
        return $stmt->execute();
    } catch(PDOException $e) { return false; }
}

function updateKhachSan($maKS, $tenKS, $diaChi, $moTa) {
    global $pdo;
    $sql = "UPDATE khach_san SET TenKS = :tenKS, DiaChi = :diaChi, MoTa = :moTa WHERE MaKS = :maKS";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKS', $maKS);
        $stmt->bindParam(':tenKS', $tenKS);
        $stmt->bindParam(':diaChi', $diaChi);
        $stmt->bindParam(':moTa', $moTa);
        return $stmt->execute();
    } catch(PDOException $e) { return false; }
}

function deleteKhachSan($maKS) {
    global $pdo;
    $sql = "DELETE FROM khach_san WHERE MaKS = :maKS";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKS', $maKS);
        return $stmt->execute();
    } catch(PDOException $e) { return false; }
}
?>