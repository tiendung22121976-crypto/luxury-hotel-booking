<?php
/**
 * controllers/ctrl_timkiem.php
 * Xử lý logic tìm phòng trống theo địa điểm và khoảng ngày
 * Được gọi từ search.php
 */
require_once __DIR__ . '/../models/mdl_phong.php';

$danhSachPhongTrong = [];
$daTimKiem = false;
$errTimKiem = '';

if (isset($_GET['btn_timkiem'])) {
    $daTimKiem = true;
    $diaDiem  = trim($_GET['diaDiem']  ?? '');
    $ngayNhan = trim($_GET['ngayNhan']  ?? '');
    $ngayTra  = trim($_GET['ngayTra'] ?? '');

    if (empty($diaDiem) || empty($ngayNhan) || empty($ngayTra)) {
        $errTimKiem = 'Vui lòng nhập đầy đủ thông tin tìm kiếm.';
    } elseif (strtotime($ngayNhan) < strtotime(date('Y-m-d'))) {
        $errTimKiem = 'Ngày nhận phòng không được nhỏ hơn ngày hôm nay.';
    } elseif (strtotime($ngayTra) <= strtotime($ngayNhan)) {
        $errTimKiem = 'Ngày trả phòng phải sau ngày nhận phòng.';
    } else {
        $danhSachPhongTrong = timPhongTrong($diaDiem, $ngayNhan, $ngayTra);
    }
}
