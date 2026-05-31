<?php
require_once '../config/database.php';

function getAllKhuyenMai() {
    global $pdo;
    $sql = "SELECT * FROM khuyen_mai ORDER BY NgayBatDau DESC";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function getKhuyenMaiById($maKM) {
    global $pdo;
    $sql = "SELECT * FROM khuyen_mai WHERE MaKM = :maKM";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKM', $maKM);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

function checkKhuyenMaiExists($maKM) {
    global $pdo;
    $sql = "SELECT COUNT(*) as count FROM khuyen_mai WHERE MaKM = :maKM";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKM', $maKM);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    } catch(PDOException $e) {
        return false;
    }
}

function addKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT) {
    global $pdo;
    $sql = "INSERT INTO khuyen_mai (MaKM, TenKM, PhanTramGiam, NgayBatDau, NgayKetThuc) 
            VALUES (:maKM, :tenKM, :phanTram, :ngayBD, :ngayKT)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKM', $maKM);
        $stmt->bindParam(':tenKM', $tenKM);
        $stmt->bindParam(':phanTram', $phanTram);
        $stmt->bindParam(':ngayBD', $ngayBD);
        $stmt->bindParam(':ngayKT', $ngayKT);
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

function updateKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT) {
    global $pdo;
    $sql = "UPDATE khuyen_mai 
            SET TenKM = :tenKM, PhanTramGiam = :phanTram, NgayBatDau = :ngayBD, NgayKetThuc = :ngayKT 
            WHERE MaKM = :maKM";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKM', $maKM);
        $stmt->bindParam(':tenKM', $tenKM);
        $stmt->bindParam(':phanTram', $phanTram);
        $stmt->bindParam(':ngayBD', $ngayBD);
        $stmt->bindParam(':ngayKT', $ngayKT);
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

function deleteKhuyenMai($maKM) {
    global $pdo;
    $sql = "DELETE FROM khuyen_mai WHERE MaKM = :maKM";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':maKM', $maKM);
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}
?>
