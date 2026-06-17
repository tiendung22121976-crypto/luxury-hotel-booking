<?php
require_once '../models/mdl_phong.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $maPhong = trim($_POST['maPhong']);
        $soPhong = trim($_POST['soPhong']);
        $maKS = trim($_POST['maKS']);
        $maLoai = trim($_POST['maLoai']);
        $trangThai = isset($_POST['trangThai']) ? trim($_POST['trangThai']) : 'Available';

        // Validation cơ bản
        if (empty($maPhong) || empty($soPhong) || empty($maKS) || empty($maLoai)) {
            $error = 'Vui lòng nhập đầy đủ thông tin Mã phòng, Số phòng, Khách sạn và Loại phòng.';
        } else {
            if ($action == 'add') {
                if (checkPhongExists($maPhong)) {
                    $error = 'Mã phòng này đã tồn tại trong hệ thống.';
                } else {
                    if (addPhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)) {
                        echo "<script>alert('Thêm phòng thành công!'); window.location.href='ctrl_ql_phong.php?action=list';</script>";
                        exit();
                    } else {
                        $error = 'Lỗi hệ thống khi thêm dữ liệu.';
                    }
                }
            } elseif ($action == 'edit') {
                if (updatePhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)) {
                    echo "<script>alert('Cập nhật phòng thành công!'); window.location.href='ctrl_ql_phong.php?action=list';</script>";
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

// Xử lý luồng Xóa phòng
if ($action == 'delete' && isset($_GET['maPhong'])) {
    $maPhong = $_GET['maPhong'];
    
    // Kiểm tra ràng buộc nghiệp vụ: Không xóa nếu phòng đang có đơn đặt chưa kết thúc[cite: 2]
    if (kiemTraPhongDangHoatDong($maPhong)) {
        echo "<script>alert('Không thể xóa phòng này vì đang có đơn đặt phòng hoạt động!'); window.location.href='ctrl_ql_phong.php?action=list';</script>";
        exit();
    } else {
        if (deletePhong($maPhong)) {
            echo "<script>alert('Xóa phòng thành công!'); window.location.href='ctrl_ql_phong.php?action=list';</script>";
            exit();
        } else {
            echo "<script>alert('Lỗi hệ thống khi xóa phòng.'); window.location.href='ctrl_ql_phong.php?action=list';</script>";
            exit();
        }
    }
}

// Chuẩn bị dữ liệu để đẩy ra View (Giao diện)
if ($action == 'edit' && isset($_GET['maPhong'])) {
    $phong = getPhongById($_GET['maPhong']);
}

if ($action == 'list') {
    $danhSachPhong = getAllPhongAdmin();
}

// Lấy danh sách Khách sạn và Loại phòng để làm thẻ <select> cho form Add/Edit
if ($action == 'add' || $action == 'edit') {
    $danhSachKS = getDanhSachKS();
    $danhSachLoai = getDanhSachLoaiPhong();
}

// Gọi View để hiển thị
require_once '../views/phong.php';
?>