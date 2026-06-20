<?php
/**
 * controllers/ctrl_ql_khachsan.php
 * Xử lý logic CRUD cho phân hệ Quản lý Khách sạn (Admin)
 * Sau khi xử lý xong sẽ include view tương ứng trong admin.php
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/mdl_khach_san.php';

yeuCauAdmin(); // Chỉ Admin mới được vào

$action  = $_GET['action'] ?? 'list';
$error   = '';
$success = '';

// -------------------------------------------------------
// XỬ LÝ THÊM / SỬA
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    $maKS   = trim($_POST['maKS']   ?? '');
    $tenKS  = trim($_POST['tenKS']  ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $moTa   = trim($_POST['moTa']   ?? '');

    if (empty($maKS) || empty($tenKS) || empty($diaChi)) {
        $error = 'Vui lòng nhập đầy đủ Mã, Tên và Địa chỉ khách sạn.';
    } else {
        if ($action === 'add') {
            if (checkKhachSanExists($maKS)) {
                $error = 'Mã khách sạn này đã tồn tại.';
            } elseif (addKhachSan($maKS, $tenKS, $diaChi, $moTa)) {
                header('Location: ../views/admin.php?tab=khachsan&msg=add_ok');
                exit;
            } else {
                $error = 'Lỗi hệ thống khi thêm dữ liệu.';
            }
        } else { // edit
            if (updateKhachSan($maKS, $tenKS, $diaChi, $moTa)) {
                header('Location: ../views/admin.php?tab=khachsan&msg=edit_ok');
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
if ($action === 'delete' && isset($_GET['maKS'])) {
    $maKS = $_GET['maKS'];
    if (kiemTraKhachSanCoPhong($maKS)) {
        header('Location: ../views/admin.php?tab=khachsan&msg=del_fail');
    } elseif (deleteKhachSan($maKS)) {
        header('Location: ../views/admin.php?tab=khachsan&msg=del_ok');
    } else {
        header('Location: ../views/admin.php?tab=khachsan&msg=sys_err');
    }
    exit;
}

// -------------------------------------------------------
// CHUẨN BỊ DỮ LIỆU ĐẨY RA VIEW
// -------------------------------------------------------
$danhSachKS = [];
$khachSan   = null;

if ($action === 'list') {
    $danhSachKS = getAllKhachSan();
}
if ($action === 'edit' && isset($_GET['maKS'])) {
    $khachSan = getKhachSanById($_GET['maKS']);
}
