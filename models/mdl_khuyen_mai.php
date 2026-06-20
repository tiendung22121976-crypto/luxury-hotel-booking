<?php
/**
 * models/mdl_khuyen_mai.php
 * Các hàm truy vấn CSDL cho bảng khuyen_mai (CRUD Admin + helper frontend)
 */
require_once __DIR__ . '/../config/database.php';

function getAllKhuyenMai() {
    global $pdo;
    try {
        return $pdo->query("SELECT * FROM khuyen_mai ORDER BY NgayBatDau DESC")->fetchAll();
    } catch (PDOException $e) { return []; }
}

/**
 * Lấy danh sách khuyến mãi đang còn hiệu lực (dùng cho dropdown khi đặt phòng)
 */
function getKhuyenMaiConHieuLuc() {
    global $pdo;
    try {
        $today = date('Y-m-d');
        $stmt  = $pdo->prepare(
            "SELECT * FROM khuyen_mai
             WHERE NgayBatDau <= :today AND NgayKetThuc >= :today
             ORDER BY PhanTramGiam DESC"
        );
        $stmt->execute([':today' => $today]);
        return $stmt->fetchAll();
    } catch (PDOException $e) { return []; }
}

function getKhuyenMaiById($maKM) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM khuyen_mai WHERE MaKM = :maKM");
        $stmt->execute([':maKM' => $maKM]);
        return $stmt->fetch();
    } catch (PDOException $e) { return false; }
}

function checkKhuyenMaiExists($maKM) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM khuyen_mai WHERE MaKM = :maKM");
        $stmt->execute([':maKM' => $maKM]);
        return $stmt->fetch()['cnt'] > 0;
    } catch (PDOException $e) { return false; }
}

function addKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT) {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO khuyen_mai (MaKM, TenKM, PhanTramGiam, NgayBatDau, NgayKetThuc)
             VALUES (:maKM, :tenKM, :phanTram, :ngayBD, :ngayKT)"
        );
        return $stmt->execute([
            ':maKM'     => $maKM,
            ':tenKM'    => $tenKM,
            ':phanTram' => $phanTram,
            ':ngayBD'   => $ngayBD,
            ':ngayKT'   => $ngayKT,
        ]);
    } catch (PDOException $e) { return false; }
}

function updateKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT) {
    global $pdo;
    try {
        $stmt = $pdo->prepare(
            "UPDATE khuyen_mai
             SET TenKM = :tenKM, PhanTramGiam = :phanTram, NgayBatDau = :ngayBD, NgayKetThuc = :ngayKT
             WHERE MaKM = :maKM"
        );
        return $stmt->execute([
            ':maKM'     => $maKM,
            ':tenKM'    => $tenKM,
            ':phanTram' => $phanTram,
            ':ngayBD'   => $ngayBD,
            ':ngayKT'   => $ngayKT,
        ]);
    } catch (PDOException $e) { return false; }
}

function deleteKhuyenMai($maKM) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM khuyen_mai WHERE MaKM = :maKM");
        return $stmt->execute([':maKM' => $maKM]);
    } catch (PDOException $e) { return false; }
}
