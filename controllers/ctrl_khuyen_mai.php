<?php
/**
 * controllers/ctrl_khuyen_mai.php
 * Xử lý logic CRUD cho phân hệ Quản lý Khuyến mãi (Admin)
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/mdl_khuyen_mai.php';

yeuCauAdmin();

$action  = $_GET['action'] ?? 'list';
$error   = '';
$success = '';

// -------------------------------------------------------
// XỬ LÝ THÊM / SỬA
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    $maKM     = trim($_POST['maKM']     ?? '');
    $tenKM    = trim($_POST['tenKM']    ?? '');
    $phanTram = intval($_POST['phanTram'] ?? 0);
    $ngayBD   = $_POST['ngayBD'] ?? '';
    $ngayKT   = $_POST['ngayKT'] ?? '';
    $today    = strtotime(date('Y-m-d'));

    if (empty($maKM) || empty($phanTram)) {
        $error = 'Thiếu thông tin bắt buộc (Mã code, % giảm giá).';
    } elseif ($phanTram < 1 || $phanTram > 100) {
        $error = 'Mức % giảm giá phải là số nguyên từ 1 đến 100.';
    } elseif ($action === 'add' && strtotime($ngayBD) < $today) {
        $error = 'Ngày bắt đầu không được nhỏ hơn ngày hiện tại.';
    } elseif (strtotime($ngayKT) < strtotime($ngayBD)) {
        $error = 'Ngày kết thúc không hợp lệ (phải sau ngày bắt đầu).';
    } else {
        if ($action === 'add') {
            if (checkKhuyenMaiExists($maKM)) {
                $error = 'Mã code đã tồn tại.';
            } elseif (addKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT)) {
                header('Location: ../views/admin.php?tab=khuyenmai&msg=add_ok');
                exit;
            } else {
                $error = 'Lỗi hệ thống khi thêm dữ liệu.';
            }
        } else { // edit
            if (updateKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT)) {
                header('Location: ../views/admin.php?tab=khuyenmai&msg=edit_ok');
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
if ($action === 'delete' && isset($_GET['maKM'])) {
    $maKM = $_GET['maKM'];
    if (deleteKhuyenMai($maKM)) {
        header('Location: ../views/admin.php?tab=khuyenmai&msg=del_ok');
    } else {
        header('Location: ../views/admin.php?tab=khuyenmai&msg=sys_err');
    }
    exit;
}

// -------------------------------------------------------
// CHUẨN BỊ DỮ LIỆU ĐẨY RA VIEW
// -------------------------------------------------------
$danhSachKM = [];
$khuyenMai  = null;

if ($action === 'list') {
    $danhSachKM = getAllKhuyenMai();
}
if ($action === 'edit' && isset($_GET['maKM'])) {
    $khuyenMai = getKhuyenMaiById($_GET['maKM']);
}
