<?php
/**
 * controllers/ctrl_ql_phong.php
 * Xử lý logic CRUD cho phân hệ Quản lý Phòng (Admin)
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/mdl_phong.php';

yeuCauAdmin();

$action  = $_GET['action'] ?? 'list';
$error   = '';
$success = '';

// -------------------------------------------------------
// XỬ LÝ THÊM / SỬA
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    $maPhong   = trim($_POST['maPhong']   ?? '');
    $soPhong   = trim($_POST['soPhong']   ?? '');
    $maKS      = trim($_POST['maKS']      ?? '');
    $maLoai    = trim($_POST['maLoai']    ?? '');
    $trangThai = trim($_POST['trangThai'] ?? 'Available');

    if (empty($maPhong) || empty($soPhong) || empty($maKS) || empty($maLoai)) {
        $error = 'Vui lòng nhập đầy đủ Mã phòng, Số phòng, Khách sạn và Loại phòng.';
    } else {
        if ($action === 'add') {
            if (checkPhongExists($maPhong)) {
                $error = 'Mã phòng này đã tồn tại trong hệ thống.';
            } elseif (addPhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)) {
                header('Location: ../views/admin.php?tab=phong&msg=add_ok');
                exit;
            } else {
                $error = 'Lỗi hệ thống khi thêm dữ liệu.';
            }
        } else { // edit
            if (updatePhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)) {
                header('Location: ../views/admin.php?tab=phong&msg=edit_ok');
                exit;
            } else {
                $error = 'Lỗi hệ thống khi cập nhật dữ liệu.';
            }
        }
    }
}

// -------------------------------------------------------
// XỬ LÝ XÓA
// -------------------------------------------------------
if ($action === 'delete' && isset($_GET['maPhong'])) {
    $maPhong = $_GET['maPhong'];
    if (kiemTraPhongDangHoatDong($maPhong)) {
        header('Location: ../views/admin.php?tab=phong&msg=del_fail');
    } elseif (deletePhong($maPhong)) {
        header('Location: ../views/admin.php?tab=phong&msg=del_ok');
    } else {
        header('Location: ../views/admin.php?tab=phong&msg=sys_err');
    }
    exit;
}

// -------------------------------------------------------
// CHUẨN BỊ DỮ LIỆU ĐẨY RA VIEW
// -------------------------------------------------------
$danhSachPhong = [];
$danhSachKS    = [];
$danhSachLoai  = [];
$phong         = null;

if ($action === 'list') {
    $danhSachPhong = getAllPhongAdmin();
}
if ($action === 'edit' && isset($_GET['maPhong'])) {
    $phong = getPhongById($_GET['maPhong']);
}
if (in_array($action, ['add', 'edit'])) {
    $danhSachKS   = getDanhSachKS();
    $danhSachLoai = getDanhSachLoaiPhong();
}
