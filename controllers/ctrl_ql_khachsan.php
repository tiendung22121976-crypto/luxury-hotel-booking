<?php
require_once '../models/mdl_khach_san.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $maKS = trim($_POST['maKS']);
        $tenKS = trim($_POST['tenKS']);
        $diaChi = trim($_POST['diaChi']);
        $moTa = trim($_POST['moTa']);

        // Validation cơ bản
        if (empty($maKS) || empty($tenKS) || empty($diaChi)) {
            $error = 'Vui lòng nhập đầy đủ Mã, Tên và Địa chỉ khách sạn.';
        } else {
            if ($action == 'add') {
                if (checkKhachSanExists($maKS)) {
                    $error = 'Mã khách sạn này đã tồn tại.';
                } else {
                    if (addKhachSan($maKS, $tenKS, $diaChi, $moTa)) {
                        echo "<script>alert('Thêm khách sạn thành công!'); window.location.href='ctrl_ql_khachsan.php?action=list';</script>";
                        exit();
                    } else {
                        $error = 'Lỗi hệ thống khi thêm dữ liệu.';
                    }
                }
            } elseif ($action == 'edit') {
                if (updateKhachSan($maKS, $tenKS, $diaChi, $moTa)) {
                    echo "<script>alert('Cập nhật khách sạn thành công!'); window.location.href='ctrl_ql_khachsan.php?action=list';</script>";
                    exit();
                } else {
                    $error = 'Lỗi hệ thống khi cập nhật dữ liệu.';
                }
            }
        }
        
        // Hiển thị lỗi nếu có
        if (!empty($error)) {
            echo "<script>alert('$error');</script>";
        }
    }
}

// Xử lý Xóa khách sạn
if ($action == 'delete' && isset($_GET['maKS'])) {
    $maKS = $_GET['maKS'];
    
    // Kiểm tra ràng buộc nghiệp vụ: Không xóa nếu có phòng hoạt động[cite: 4]
    if (kiemTraKhachSanCoPhong($maKS)) {
        echo "<script>alert('Không thể xóa khách sạn vì có phòng đang hoạt động!'); window.location.href='ctrl_ql_khachsan.php?action=list';</script>";
        exit();
    } else {
        if (deleteKhachSan($maKS)) {
            echo "<script>alert('Xóa khách sạn thành công!'); window.location.href='ctrl_ql_khachsan.php?action=list';</script>";
            exit();
        } else {
            echo "<script>alert('Lỗi hệ thống khi xóa khách sạn.'); window.location.href='ctrl_ql_khachsan.php?action=list';</script>";
            exit();
        }
    }
}

// Lấy dữ liệu đẩy ra View
if ($action == 'edit' && isset($_GET['maKS'])) {
    $khachSan = getKhachSanById($_GET['maKS']);
}

if ($action == 'list') {
    $danhSachKS = getAllKhachSan();
}

// Gọi View để hiển thị
require_once '../views/khachsan.php';
?>