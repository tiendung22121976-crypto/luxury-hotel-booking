<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'guiDanhGia') {
    yeuCauDangNhap();
    
    $maPhong  = trim($_POST['maPhong'] ?? '');
    $maDon    = trim($_POST['maDon'] ?? '');
    $mucDo    = (int)($_POST['mucDo'] ?? 0);
    $binhLuan = trim($_POST['binhLuan'] ?? '');

    if (!$maDon || $mucDo < 1 || $mucDo > 3 || $binhLuan === '') {
        header('Location: ../views/room-detail.php?id=' . urlencode($maPhong) . '&err=' . urlencode('Vui lòng chọn mức độ đánh giá và nhập nhận xét.'));
        exit;
    } else {
        $stmtInsertDG = $pdo->prepare("INSERT INTO danh_gia (MaTK, MaDon, MucDo, BinhLuan) VALUES (:maTK, :maDon, :mucDo, :binhLuan)");
        $stmtInsertDG->execute([':maTK' => $_SESSION['MaTK'], ':maDon' => $maDon, ':mucDo' => $mucDo, ':binhLuan' => $binhLuan]);
        
        header('Location: ../views/room-detail.php?id=' . urlencode($maPhong) . '&msg=' . urlencode('Cảm ơn bạn đã đánh giá!') . '&type=success');
        exit;
    }
} else {
    header('Location: ../views/search.php');
    exit;
}