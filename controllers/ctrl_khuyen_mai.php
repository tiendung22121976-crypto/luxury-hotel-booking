<?php
require_once '../models/mdl_khuyen_mai.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $maKM = trim($_POST['maKM']);
        $tenKM = trim($_POST['tenKM']);
        $phanTram = intval($_POST['phanTram']);
        $ngayBD = $_POST['ngayBD'];
        $ngayKT = $_POST['ngayKT'];

        $today = strtotime(date('Y-m-d'));

        // Validation
        if (empty($maKM) || empty($phanTram)) {
            $error = 'Thiếu thông tin bắt buộc (Mã code, % giảm giá).';
        } elseif ($phanTram < 1 || $phanTram > 100) {
            $error = 'Mức % giảm giá phải là số nguyên từ 1 đến 100.';
        } elseif (strtotime($ngayBD) < $today) {
            $error = 'Ngày bắt đầu không được nhỏ hơn ngày hiện tại.';
        } elseif (strtotime($ngayKT) < strtotime($ngayBD)) {
            $error = 'Ngày kết thúc không hợp lệ (nhỏ hơn ngày bắt đầu).';
        } else {
            if ($action == 'add') {
                if (checkKhuyenMaiExists($maKM)) {
                    $error = 'Mã code đã tồn tại.';
                } else {
                    if (addKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT)) {
                        $success = 'Thêm mới khuyến mãi thành công!';
                        // JavaScript alert for success and redirect
                        echo "<script>alert('$success'); window.location.href='ctrl_khuyen_mai.php?action=list';</script>";
                        exit();
                    } else {
                        $error = 'Lỗi thêm dữ liệu.';
                    }
                }
            } elseif ($action == 'edit') {
                if (updateKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT)) {
                    $success = 'Cập nhật khuyến mãi thành công!';
                    echo "<script>alert('$success'); window.location.href='ctrl_khuyen_mai.php?action=list';</script>";
                    exit();
                } else {
                    $error = 'Lỗi cập nhật dữ liệu.';
                }
            }
        }
        
        // Show alert if there is an error
        if (!empty($error)) {
            echo "<script>alert('$error');</script>";
        }
    }
}

if ($action == 'delete' && isset($_GET['maKM'])) {
    $maKM = $_GET['maKM'];
    if (deleteKhuyenMai($maKM)) {
        echo "<script>alert('Xóa khuyến mãi thành công!'); window.location.href='ctrl_khuyen_mai.php?action=list';</script>";
        exit();
    } else {
        echo "<script>alert('Lỗi khi xóa khuyến mãi.'); window.location.href='ctrl_khuyen_mai.php?action=list';</script>";
        exit();
    }
}

// Fetch data for views
if ($action == 'edit' && isset($_GET['maKM'])) {
    $khuyenMai = getKhuyenMaiById($_GET['maKM']);
}

if ($action == 'list') {
    $danhSachKM = getAllKhuyenMai();
}

// Require view
require_once '../views/khuyenmai.php';
?>
