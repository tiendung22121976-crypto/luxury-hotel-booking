<?php
/**
 * views/admin.php
 * Trang quản trị (Admin Dashboard) - Luxury Hotel
 *
 * Toàn bộ dữ liệu hiển thị (thống kê, danh sách khách sạn/phòng/đặt
 * phòng/khuyến mãi/tài khoản) được lấy trực tiếp từ cơ sở dữ liệu qua
 * các model trong thư mục models/. Không còn dữ liệu mẫu (sample/fake)
 * như bản trước đây.
 *
 * CSS được tách riêng ra assets/admin-style.css (xem yêu cầu tách style).
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/mdl_khach_san.php';
require_once __DIR__ . '/../models/mdl_phong.php';
require_once __DIR__ . '/../models/mdl_khuyen_mai.php';
require_once __DIR__ . '/../models/mdl_tai_khoan.php';
require_once __DIR__ . '/../models/mdl_don_dat_phong.php';

yeuCauAdmin(); // Chỉ Admin mới được vào trang này

$admin_name = $_SESSION['HoTen'] ?? 'Admin';
$active_tab = $_GET['tab'] ?? 'dashboard';
$action     = $_GET['action'] ?? '';
$thongBaoLoi = '';
$thongBaoOk  = '';

// ============================================================
// XỬ LÝ CÁC HÀNH ĐỘNG (THÊM / SỬA / XÓA / XÁC NHẬN / HỦY...)
// Toàn bộ thao tác CRUD tác động trực tiếp lên CSDL qua các model.
// Sau khi xử lý xong sẽ redirect lại tab tương ứng (PRG pattern)
// để tránh resend form khi người dùng bấm F5.
// ============================================================

// ---------- KHÁCH SẠN (CHI NHÁNH) ----------
// >>> BỔ SUNG: XỬ LÝ XÓA ĐƠN ĐẶT PHÒNG DÀNH CHO ADMIN <<<
if ($action === 'xoa_booking' && isset($_GET['id'])) {
    $maDon = $_GET['id'];
    $redirectTab = $_GET['tab'] ?? 'booking'; // Nhận tab chuyển hướng để giữ nguyên giao diện hiện tại

    // 1. Kiểm tra trạng thái phòng hiện tại của đơn đặt phòng này
    $stmtCheck = $pdo->prepare("
        SELECT d.MaDon, p.TrangThai AS TrangThaiPhong 
        FROM don_dat_phong d
        INNER JOIN phong p ON d.MaPhong = p.MaPhong
        WHERE d.MaDon = :maDon
    ");
    $stmtCheck->execute([':maDon' => $maDon]);
    $booking = $stmtCheck->fetch();

    if ($booking) {
        // Nếu phòng có trạng thái hoạt động là 'Reserved' hoặc 'Occupied' -> Chặn không cho xóa
        if (in_array($booking['TrangThaiPhong'], ['Reserved', 'Occupied'])) {
            header("Location: admin.php?tab={$redirectTab}&msg=del_bk_fail");
            exit;
        } else {
            // Phòng trống hoặc trạng thái khác -> Cho phép xóa bình thường
            $stmtDelete = $pdo->prepare("DELETE FROM don_dat_phong WHERE MaDon = :maDon");
            $stmtDelete->execute([':maDon' => $maDon]);
            header("Location: admin.php?tab={$redirectTab}&msg=del_bk_ok");
            exit;
        }
    } else {
        header("Location: admin.php?tab={$redirectTab}&msg=sys_err");
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['them_chinhanh', 'sua_chinhanh'])) {
    $maKS   = trim($_POST['maKS'] ?? '');
    $tenKS  = trim($_POST['tenKS'] ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $moTa   = trim($_POST['moTa'] ?? '');

    if ($maKS === '' || $tenKS === '' || $diaChi === '') {
        $thongBaoLoi = 'Vui lòng nhập đầy đủ Mã, Tên và Địa chỉ chi nhánh.';
    } elseif ($action === 'them_chinhanh') {
        if (checkKhachSanExists($maKS)) {
            $thongBaoLoi = 'Mã chi nhánh này đã tồn tại.';
        } elseif (addKhachSan($maKS, $tenKS, $diaChi, $moTa)) {
            header('Location: admin.php?tab=khachsan&msg=add_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi thêm chi nhánh.';
        }
    } else { // sua_chinhanh
        if (updateKhachSan($maKS, $tenKS, $diaChi, $moTa)) {
            header('Location: admin.php?tab=khachsan&msg=edit_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi cập nhật chi nhánh.';
        }
    }
}
if ($action === 'xoa_chinhanh' && isset($_GET['maKS'])) {
    $maKS = $_GET['maKS'];
    if (kiemTraKhachSanCoPhong($maKS)) {
        header('Location: admin.php?tab=khachsan&msg=del_fail');
    } elseif (deleteKhachSan($maKS)) {
        header('Location: admin.php?tab=khachsan&msg=del_ok');
    } else {
        header('Location: admin.php?tab=khachsan&msg=sys_err');
    }
    exit;
}

// ---------- PHÒNG ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['them_phong', 'sua_phong'])) {
    $maPhong   = trim($_POST['maPhong']   ?? '');
    $soPhong   = trim($_POST['soPhong']   ?? '');
    $maKS      = trim($_POST['maKS']      ?? '');
    $maLoai    = trim($_POST['maLoai']    ?? '');
    $trangThai = trim($_POST['trangThai'] ?? 'Available');

    if ($maPhong === '' || $soPhong === '' || $maKS === '' || $maLoai === '') {
        $thongBaoLoi = 'Vui lòng nhập đầy đủ Mã phòng, Số phòng, Chi nhánh và Loại phòng.';
    } elseif ($action === 'them_phong') {
        if (checkPhongExists($maPhong)) {
            $thongBaoLoi = 'Mã phòng này đã tồn tại trong hệ thống.';
        } elseif (addPhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)) {
            header('Location: admin.php?tab=phong&msg=add_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi thêm phòng.';
        }
    } else { // sua_phong
        if (updatePhong($maPhong, $soPhong, $maKS, $maLoai, $trangThai)) {
            header('Location: admin.php?tab=phong&msg=edit_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi cập nhật phòng.';
        }
    }
}
if ($action === 'xoa_phong' && isset($_GET['maPhong'])) {
    $maPhong = $_GET['maPhong'];
    if (kiemTraPhongDangHoatDong($maPhong)) {
        header('Location: admin.php?tab=phong&msg=del_fail');
    } elseif (deletePhong($maPhong)) {
        header('Location: admin.php?tab=phong&msg=del_ok');
    } else {
        header('Location: admin.php?tab=phong&msg=sys_err');
    }
    exit;
}

// ---------- ĐẶT PHÒNG (BOOKING) ----------
if ($action === 'xac_nhan_booking' && isset($_GET['id'])) {
    if (xacNhanDonDatPhong($_GET['id'])) {
        header('Location: admin.php?tab=booking&msg=confirm_ok');
    } else {
        header('Location: admin.php?tab=booking&msg=sys_err');
    }
    exit;
}
if ($action === 'huy_booking' && isset($_GET['id'])) {
    if (huyDonDatPhongAdmin($_GET['id'])) {
        header('Location: admin.php?tab=booking&msg=cancel_ok');
    } else {
        header('Location: admin.php?tab=booking&msg=sys_err');
    }
    exit;
}
if ($action === 'xoa_booking' && isset($_GET['id'])) {
    $result = xoaDonDatPhongAdmin($_GET['id']);
    if ($result === true) {
        header('Location: admin.php?tab=booking&msg=del_bk_ok');
    } elseif ($result === 'ACTIVE_ROOM') {
        header('Location: admin.php?tab=booking&msg=del_bk_fail');
    } else {
        header('Location: admin.php?tab=booking&msg=sys_err');
    }
    exit;
}
// ---------- KHUYẾN MÃI ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['them_khuyenmai', 'sua_khuyenmai'])) {
    $maKM     = trim($_POST['maKM']     ?? '');
    $tenKM    = trim($_POST['tenKM']    ?? '');
    $phanTram = intval($_POST['phanTram'] ?? 0);
    $ngayBD   = $_POST['ngayBD'] ?? '';
    $ngayKT   = $_POST['ngayKT'] ?? '';

    if ($maKM === '' || $tenKM === '' || $phanTram < 1 || $phanTram > 100) {
        $thongBaoLoi = 'Thiếu thông tin bắt buộc hoặc mức giảm giá không hợp lệ (1-100%).';
    } elseif (strtotime($ngayKT) < strtotime($ngayBD)) {
        $thongBaoLoi = 'Ngày kết thúc phải sau ngày bắt đầu.';
    } elseif ($action === 'them_khuyenmai') {
        if (checkKhuyenMaiExists($maKM)) {
            $thongBaoLoi = 'Mã khuyến mãi này đã tồn tại.';
        } elseif (addKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT)) {
            header('Location: admin.php?tab=khuyenmai&msg=add_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi thêm khuyến mãi.';
        }
    } else { // sua_khuyenmai
        if (updateKhuyenMai($maKM, $tenKM, $phanTram, $ngayBD, $ngayKT)) {
            header('Location: admin.php?tab=khuyenmai&msg=edit_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi cập nhật khuyến mãi.';
        }
    }
}
if ($action === 'xoa_khuyenmai' && isset($_GET['maKM'])) {
    if (deleteKhuyenMai($_GET['maKM'])) {
        header('Location: admin.php?tab=khuyenmai&msg=del_ok');
    } else {
        header('Location: admin.php?tab=khuyenmai&msg=sys_err');
    }
    exit;
}

// ---------- TÀI KHOẢN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['them_taikhoan', 'sua_taikhoan'])) {
    $maTK      = trim($_POST['maTK']      ?? '');
    $hoTen     = trim($_POST['hoTen']     ?? '');
    $email     = trim($_POST['email']     ?? '');
    $sdt       = trim($_POST['sdt']       ?? '');
    $vaiTro    = trim($_POST['vaiTro']    ?? 'ThanhVien');
    $trangThai = trim($_POST['trangThai'] ?? 'HoatDong');
    $matKhau   = $_POST['matKhau'] ?? '';

    if ($hoTen === '' || $email === '' || $sdt === '') {
        $thongBaoLoi = 'Vui lòng nhập đầy đủ Họ tên, Email và Số điện thoại.';
    } elseif ($action === 'them_taikhoan') {
        if ($matKhau === '') {
            $thongBaoLoi = 'Vui lòng nhập mật khẩu cho tài khoản mới.';
        } elseif (checkTaiKhoanTrungEmailSdt($email, $sdt)) {
            $thongBaoLoi = 'Email hoặc số điện thoại đã được sử dụng.';
        } elseif (addTaiKhoan($hoTen, $email, $sdt, $matKhau, $vaiTro, $trangThai)) {
            header('Location: admin.php?tab=taikhoan&msg=add_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi thêm tài khoản.';
        }
    } else { // sua_taikhoan
        if (checkTaiKhoanTrungEmailSdt($email, $sdt, $maTK)) {
            $thongBaoLoi = 'Email hoặc số điện thoại đã được sử dụng bởi tài khoản khác.';
        } elseif (updateTaiKhoan($maTK, $hoTen, $email, $sdt, $vaiTro, $trangThai, $matKhau)) {
            header('Location: admin.php?tab=taikhoan&msg=edit_ok');
            exit;
        } else {
            $thongBaoLoi = 'Lỗi hệ thống khi cập nhật tài khoản.';
        }
    }
}
if ($action === 'khoa_taikhoan' && isset($_GET['maTK'])) {
    if (toggleKhoaTaiKhoan($_GET['maTK'])) {
        header('Location: admin.php?tab=taikhoan&msg=toggle_ok');
    } else {
        header('Location: admin.php?tab=taikhoan&msg=sys_err');
    }
    exit;
}
if ($action === 'xoa_taikhoan' && isset($_GET['maTK'])) {
    $maTK = $_GET['maTK'];
    if (kiemTraTaiKhoanCoDuLieu($maTK)) {
        header('Location: admin.php?tab=taikhoan&msg=del_fail');
    } elseif (deleteTaiKhoan($maTK)) {
        header('Location: admin.php?tab=taikhoan&msg=del_ok');
    } else {
        header('Location: admin.php?tab=taikhoan&msg=sys_err');
    }
    exit;
}

// ============================================================
// CHUẨN BỊ DỮ LIỆU THỰC TỪ CSDL CHO TỪNG TAB (GET / hiển thị)
// ============================================================

// Thông báo từ query string (sau redirect PRG)
$msgMap = [
    'add_ok'     => ['ok', 'Thêm dữ liệu thành công!'],
    'edit_ok'    => ['ok', 'Cập nhật dữ liệu thành công!'],
    'del_ok'     => ['ok', 'Xóa dữ liệu thành công!'],
    'del_fail'   => ['err', 'Không thể xóa: dữ liệu này đang được sử dụng ở nơi khác.'],
    'sys_err'    => ['err', 'Lỗi hệ thống, vui lòng thử lại.'],
    'confirm_ok' => ['ok', 'Xác nhận đơn đặt phòng thành công!'],
    'cancel_ok'  => ['ok', 'Hủy đơn đặt phòng thành công!'],
    'toggle_ok'  => ['ok', 'Cập nhật trạng thái tài khoản thành công!'],
    'del_bk_ok'   => ['ok', 'Xóa đơn đặt phòng thành công khỏi hệ thống!'],
    'del_bk_fail' => ['err', 'Không thể xóa đơn: Phòng này đang hoạt động hoặc đơn đặt phòng chưa được Hủy/Hoàn tất.'],
];
if (isset($_GET['msg']) && isset($msgMap[$_GET['msg']])) {
    $msgInfo = $msgMap[$_GET['msg']];
    if ($msgInfo[0] === 'ok') { $thongBaoOk = $msgInfo[1]; } else { $thongBaoLoi = $msgInfo[1]; }
}

// ---------- DASHBOARD ----------
$tk_dashboard   = getThongKeDonDatPhong();
$tk_phong_ds    = getThongKePhongTheoTrangThai();
$danhSachKS_all = getAllKhachSan();
$stats = [
    'tong_khach_san' => count($danhSachKS_all),
    'tong_phong'     => $tk_phong_ds['tong_phong'] ?? 0,
    'tong_dat_phong' => $tk_dashboard['tong_dat_phong'] ?? 0,
    'doanh_thu'      => $tk_dashboard['doanh_thu_tong'] ?? 0,
    'phong_trong'    => $tk_phong_ds['phong_trong'] ?? 0,
    'phong_co_khach' => $tk_phong_ds['phong_co_khach'] ?? 0,
    'phong_da_dat'   => $tk_phong_ds['phong_da_dat'] ?? 0,
];
$bookings = getDonDatPhongAdmin(8); // 8 đơn mới nhất cho Dashboard

// Map trạng thái CSDL (enum) -> nhãn + class badge hiển thị (dùng chung cho Dashboard & tab Booking)
$badges = [
    'ChoXacNhan' => ['badge-pending',   'Chờ xác nhận'],
    'DaXacNhan'  => ['badge-confirmed', 'Đã xác nhận'],
    'HoanTat'    => ['badge-checkout',  'Hoàn tất'],
    'DaHuy'      => ['badge-cancelled', 'Đã hủy'],
];

// ---------- TAB: KHÁCH SẠN (CHI NHÁNH) ----------
$chi_nhanh = getAllKhachSan();
$ks_info = [
    'so_phong' => $tk_phong_ds['tong_phong'] ?? 0,
    'dia_chi'  => count($chi_nhanh) . ' chi nhánh',
    'hotline'  => '1900-0000', // Hotline tổng đài chung, hiện chưa có cột riêng trong CSDL
];
$edit_chinhanh = null;
if ($action === 'sua_chinhanh' && isset($_GET['maKS'])) {
    $edit_chinhanh = getKhachSanById($_GET['maKS']);
}

// ---------- TAB: PHÒNG ----------
$phong        = getAllPhongAdmin();
$danhSachKS   = getDanhSachKS();
$danhSachLoai = getDanhSachLoaiPhong();
$phong_stats = [
    'tong'     => $tk_phong_ds['tong_phong'] ?? 0,
    'loai'     => count($danhSachLoai),
    'tien_ich' => 5, // số nhóm tiện ích cơ bản hệ thống đang hỗ trợ
];
$edit_phong = null;
if ($action === 'sua_phong' && isset($_GET['maPhong'])) {
    $edit_phong = getPhongById($_GET['maPhong']);
}

// Map trạng thái phòng (CSDL) -> badge hiển thị riêng cho tab Phòng
$p_badges = [
    'Available' => ['badge-empty',      'Trống'],
    'Reserved'  => ['badge-booked',     'Đã đặt'],
    'Occupied'  => ['badge-processing', 'Đang sử dụng'],
    'Cleaning'  => ['badge-maintain',   'Đang dọn dẹp'],
];

// ---------- TAB: ĐẶT PHÒNG (BOOKING) ----------
$don_dat_phong = getDonDatPhongAdmin();
$bk_stats = [
    'cho_duyet'       => $tk_dashboard['cho_duyet'] ?? 0,
    'da_xac_nhan'     => $tk_dashboard['da_xac_nhan'] ?? 0,
    'da_huy'          => $tk_dashboard['da_huy'] ?? 0,
    'doanh_thu_thang' => fmtVND($tk_dashboard['doanh_thu_thang'] ?? 0),
];

// ---------- TAB: KHUYẾN MÃI ----------
$khuyen_mai = getAllKhuyenMai();
$today_ymd  = date('Y-m-d');
$km_con_han = 0;
$km_het_han = 0;
foreach ($khuyen_mai as $km) {
    if ($km['NgayKetThuc'] >= $today_ymd) { $km_con_han++; } else { $km_het_han++; }
}
$km_stats = [
    'con_han'    => $km_con_han,
    'het_han'    => $km_het_han,
    'tong_phieu' => count($khuyen_mai),
    'luot_dung'  => (int)$pdo->query("SELECT COUNT(*) FROM don_dat_phong WHERE MaKM IS NOT NULL")->fetchColumn(),
];
$edit_khuyenmai = null;
if ($action === 'sua_khuyenmai' && isset($_GET['maKM'])) {
    $edit_khuyenmai = getKhuyenMaiById($_GET['maKM']);
}

// ---------- TAB: TÀI KHOẢN ----------
$tai_khoan   = getAllTaiKhoanAdmin();
$tk_tk_stats = getThongKeTaiKhoan();
$tk_stats = [
    'admin'      => $tk_tk_stats['admin'] ?? 0,
    'khach_hang' => $tk_tk_stats['thanh_vien'] ?? 0,
    'le_tan'     => 0, // Hệ thống hiện chỉ phân quyền Admin/ThanhVien, chưa có vai trò Lễ tân riêng
];
$edit_tk = null;
if ($action === 'sua_taikhoan' && isset($_GET['maTK'])) {
    $edit_tk = getTaiKhoanById($_GET['maTK']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Luxury Hotel</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS riêng cho khu vực Admin (đã tách ra khỏi file admin.php) -->
    <link href="../assets/admin-style.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ======================== SIDEBAR ======================== -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name"><i class="fas fa-hotel me-2" style="color:var(--accent)"></i>Luxury Hotel</div>
        <div class="brand-sub">Hệ thống quản trị</div>
    </div>

    <div class="sidebar-user">
        <div class="avatar"><?= h(layChuCaiDauTen($admin_name)) ?></div>
        <div class="user-info">
            <div class="name"><?= h($admin_name) ?></div>
            <div class="role">Quản trị viên</div>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-section-label">Tổng quan</li>
        <li class="nav-item">
            <a href="#" class="nav-link-tab <?= $active_tab === 'dashboard' ? 'active' : '' ?>" data-tab="dashboard">
                <span class="nav-icon"><i class="fas fa-house"></i></span>
                Trang Chủ
            </a>
        </li>

        <li class="nav-section-label">Quản lý</li>
        <li class="nav-item">
            <a href="#" class="nav-link-tab <?= $active_tab === 'khachsan' ? 'active' : '' ?>" data-tab="khachsan">
                <span class="nav-icon"><i class="fas fa-building"></i></span>
                Thông Tin Khách Sạn
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link-tab <?= $active_tab === 'phong' ? 'active' : '' ?>" data-tab="phong">
                <span class="nav-icon"><i class="fas fa-door-open"></i></span>
                Quản Lý Phòng
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link-tab <?= $active_tab === 'booking' ? 'active' : '' ?>" data-tab="booking">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                Quản Lý Đặt Phòng
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link-tab <?= $active_tab === 'khuyenmai' ? 'active' : '' ?>" data-tab="khuyenmai">
                <span class="nav-icon"><i class="fas fa-tag"></i></span>
                Quản Lý Khuyến Mãi
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link-tab <?= $active_tab === 'taikhoan' ? 'active' : '' ?>" data-tab="taikhoan">
                <span class="nav-icon"><i class="fas fa-users-gear"></i></span>
                Quản Lý Tài Khoản
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="logout.php">
            <i class="fas fa-right-from-bracket"></i>
            Đăng Xuất
        </a>
    </div>
</nav>

<!-- ======================== MAIN WRAPPER ======================== -->
<div id="main-wrapper">

    <!-- Top Bar -->
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn-sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <span class="page-title" id="topbar-title">Trang Chủ</span>
        </div>
        <div class="topbar-right">
            <span class="text-secondary" style="font-size:0.82rem">
                <i class="fas fa-clock me-1"></i>
                <span id="clock"></span>
            </span>
        </div>
    </header>

    <main id="main-content">

        <?php if ($thongBaoOk): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-circle-check me-2"></i><?= h($thongBaoOk) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if ($thongBaoLoi): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-circle-exclamation me-2"></i><?= h($thongBaoLoi) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- ==================== TAB: TRANG CHỦ (DASHBOARD) ==================== -->
        <div class="tab-panel <?= $active_tab === 'dashboard' ? 'active' : '' ?>" id="tab-dashboard">
            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fas fa-hotel"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)$stats['tong_khach_san'] ?></div>
                            <div class="stat-label">Chi nhánh khách sạn</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fas fa-door-open"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)$stats['tong_phong'] ?></div>
                            <div class="stat-label">Tổng số phòng</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-amber"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)$stats['tong_dat_phong'] ?></div>
                            <div class="stat-label">Lượt đặt phòng</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mini stats row -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#f0fdf4;color:#16a34a"><i class="fas fa-bed"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)$stats['phong_trong'] ?></div>
                            <div class="stat-label">Phòng trống</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fef2f2;color:#dc2626"><i class="fas fa-user-check"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)$stats['phong_co_khach'] ?></div>
                            <div class="stat-label">Phòng có khách</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fffbeb;color:#d97706"><i class="fas fa-bookmark"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)$stats['phong_da_dat'] ?></div>
                            <div class="stat-label">Phòng đã đặt</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Requests Table -->
            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="fas fa-bell me-2 text-warning"></i>Yêu cầu đặt phòng mới nhất</h5>
                    <div class="search-bar">
                        <i class="fas fa-magnifying-glass search-icon"></i>
                        <input type="text" placeholder="Tìm kiếm..." onkeyup="filterTable(this, 'tbl-dashboard')">
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-table" id="tbl-dashboard">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã đặt phòng</th>
                                <th>Mã xác nhận</th>
                                <th>Tên khách</th>
                                <th>Trạng thái</th>
                                <th>Loại phòng</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                            <tr><td colspan="7" class="text-center text-secondary py-4">Chưa có đơn đặt phòng nào.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($bookings as $i => $b):
                                $cls = $badges[$b['TrangThaiDon']] ?? ['badge-pending', $b['TrangThaiDon']];
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong>DP<?= str_pad($b['MaDon'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= h($b['MaXacNhan']) ?></td>
                                <td><?= h($b['TenKhach']) ?></td>
                                <td><span class="badge-status <?= $cls[0] ?>"><?= $cls[1] ?></span></td>
                                <td><?= h($b['TenLoai']) ?></td>
                                <td>
                                    <a href="admin.php?tab=booking" class="btn-action btn-action-view" title="Xem chi tiết tại tab Đặt phòng"><i class="fas fa-eye"></i></a>
                                    <?php if ($b['TrangThaiDon'] === 'ChoXacNhan'): ?>
                                    <a href="admin.php?action=xac_nhan_booking&id=<?= $b['MaDon'] ?>" class="btn-action btn-action-confirm" title="Xác nhận"><i class="fas fa-check"></i></a>
                                    <?php endif; ?>
                                    <?php if ($b['TrangThaiDon'] !== 'DaHuy'): ?>
                                    <?php endif; ?>
                                    
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-custom">
                    <span class="page-info">Hiển thị <?= count($bookings) ?> đơn mới nhất</span>
                </div>
            </div>
        </div><!-- /tab-dashboard -->


        <!-- ==================== TAB: THÔNG TIN KHÁCH SẠN ==================== -->
        <div class="tab-panel <?= $active_tab === 'khachsan' ? 'active' : '' ?>" id="tab-khachsan">
            <!-- Header stats -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fas fa-signature"></i></div>
                        <div>
                            <div class="stat-value" style="font-size:1rem;font-weight:700">LUXURY HOTEL</div>
                            <div class="stat-label">Tên hệ thống</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fas fa-door-open"></i></div>
                        <div>
                            <div class="stat-value"><?= (int)$ks_info['so_phong'] ?></div>
                            <div class="stat-label">Tổng số phòng</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-amber"><i class="fas fa-location-dot"></i></div>
                        <div>
                            <div class="stat-value" style="font-size:0.95rem"><?= h($ks_info['dia_chi']) ?></div>
                            <div class="stat-label">Phạm vi hoạt động</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-purple"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="stat-value" style="font-size:1rem"><?= h($ks_info['hotline']) ?></div>
                            <div class="stat-label">Hotline tổng đài</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chi nhánh -->
            <div class="section-card mb-4">
                <div class="section-card-header">
                    <h5><i class="fas fa-code-branch me-2 text-info"></i>Chi Nhánh</h5>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalThemChiNhanh">
                            <i class="fas fa-plus"></i> Thêm
                        </button>
                        <div class="search-bar">
                            <i class="fas fa-magnifying-glass search-icon"></i>
                            <input type="text" placeholder="Tìm kiếm..." onkeyup="filterTable(this, 'tbl-chinhanh')">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-table" id="tbl-chinhanh">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã</th>
                                <th>Tên chi nhánh</th>
                                <th>Địa chỉ</th>
                                <th>Mô tả</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($chi_nhanh)): ?>
                            <tr><td colspan="6" class="text-center text-secondary py-4">Chưa có chi nhánh nào.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($chi_nhanh as $i => $cn): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><code><?= h($cn['MaKS']) ?></code></td>
                                <td><strong><?= h($cn['TenKS']) ?></strong></td>
                                <td><?= h($cn['DiaChi']) ?></td>
                                <td class="text-secondary"><?= h($cn['MoTa']) ?></td>
                                <td>
                                    <a href="admin.php?tab=khachsan&action=sua_chinhanh&maKS=<?= urlencode($cn['MaKS']) ?>" class="btn-action btn-action-edit" data-bs-toggle="modal" data-bs-target="#modalSuaChiNhanh-<?= h($cn['MaKS']) ?>"><i class="fas fa-pen"></i></a>
                                    <a href="admin.php?tab=khachsan&action=xoa_chinhanh&maKS=<?= urlencode($cn['MaKS']) ?>" class="btn-action btn-action-delete" onclick="return confirm('Xóa chi nhánh này?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>

                            <!-- Modal sửa riêng cho từng chi nhánh (vì dữ liệu lấy trực tiếp từ DB theo dòng) -->
                            <div class="modal fade" id="modalSuaChiNhanh-<?= h($cn['MaKS']) ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header modal-header-custom">
                                            <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Sửa Chi Nhánh: <?= h($cn['TenKS']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <form method="POST" action="admin.php?tab=khachsan&action=sua_chinhanh">
                                                <input type="hidden" name="maKS" value="<?= h($cn['MaKS']) ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Tên chi nhánh <span class="text-danger">*</span></label>
                                                        <input type="text" name="tenKS" class="form-control" value="<?= h($cn['TenKS']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Địa chỉ <span class="text-danger">*</span></label>
                                                        <input type="text" name="diaChi" class="form-control" value="<?= h($cn['DiaChi']) ?>" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Mô tả</label>
                                                        <textarea name="moTa" class="form-control" rows="3"><?= h($cn['MoTa']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="mt-4 d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Cập nhật</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-custom">
                    <span class="page-info">Hiển thị <?= count($chi_nhanh) ?> / <?= count($chi_nhanh) ?> kết quả</span>
                </div>
            </div>
        </div><!-- /tab-khachsan -->


        <!-- ==================== TAB: QUẢN LÝ PHÒNG ==================== -->
        <div class="tab-panel <?= $active_tab === 'phong' ? 'active' : '' ?>" id="tab-phong">
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fas fa-door-open"></i></div>
                        <div><div class="stat-value"><?= (int)$phong_stats['tong'] ?></div><div class="stat-label">Số lượng phòng</div></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fas fa-layer-group"></i></div>
                        <div><div class="stat-value"><?= (int)$phong_stats['loai'] ?></div><div class="stat-label">Loại phòng</div></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-amber"><i class="fas fa-wifi"></i></div>
                        <div><div class="stat-value"><?= (int)$phong_stats['tien_ich'] ?></div><div class="stat-label">Nhóm tiện ích</div></div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="fas fa-door-open me-2 text-info"></i>Danh sách phòng</h5>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalThemPhong">
                            <i class="fas fa-plus"></i> Thêm phòng
                        </button>
                        <div class="search-bar">
                            <i class="fas fa-magnifying-glass search-icon"></i>
                            <input type="text" placeholder="Tìm kiếm..." onkeyup="filterTable(this, 'tbl-phong')">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-table" id="tbl-phong">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã phòng</th>
                                <th>Số phòng</th>
                                <th>Chi nhánh</th>
                                <th>Loại phòng</th>
                                <th>Giá/đêm</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($phong)): ?>
                            <tr><td colspan="8" class="text-center text-secondary py-4">Chưa có phòng nào.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($phong as $i => $p):
                                $pcls = $p_badges[$p['TrangThai']] ?? ['badge-empty', $p['TrangThai']];
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><code><?= h($p['MaPhong']) ?></code></td>
                                <td><strong><?= h($p['SoPhong']) ?></strong></td>
                                <td><?= h($p['TenKS']) ?></td>
                                <td><?= h($p['TenLoai']) ?></td>
                                <td class="text-secondary"><?= fmtVND($p['DonGia']) ?></td>
                                <td><span class="badge-status <?= $pcls[0] ?>"><?= $pcls[1] ?></span></td>
                                <td>
                                    <button class="btn-action btn-action-edit" data-bs-toggle="modal" data-bs-target="#modalSuaPhong-<?= h($p['MaPhong']) ?>"><i class="fas fa-pen"></i></button>
                                    <a href="admin.php?tab=phong&action=xoa_phong&maPhong=<?= urlencode($p['MaPhong']) ?>" class="btn-action btn-action-delete" onclick="return confirm('Xóa phòng này?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>

                            <!-- Modal sửa phòng (mỗi dòng 1 modal vì dữ liệu lấy trực tiếp từ DB) -->
                            <div class="modal fade" id="modalSuaPhong-<?= h($p['MaPhong']) ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header modal-header-custom">
                                            <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Sửa Phòng <?= h($p['SoPhong']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <form method="POST" action="admin.php?tab=phong&action=sua_phong">
                                                <input type="hidden" name="maPhong" value="<?= h($p['MaPhong']) ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Số phòng</label>
                                                        <input type="text" name="soPhong" class="form-control" value="<?= h($p['SoPhong']) ?>" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Chi nhánh</label>
                                                        <select name="maKS" class="form-select">
                                                            <?php foreach ($danhSachKS as $ks): ?>
                                                            <option value="<?= h($ks['MaKS']) ?>" <?= $ks['MaKS'] === $p['MaKS'] ? 'selected' : '' ?>><?= h($ks['TenKS']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Loại phòng</label>
                                                        <select name="maLoai" class="form-select">
                                                            <?php foreach ($danhSachLoai as $lp): ?>
                                                            <option value="<?= h($lp['MaLoai']) ?>" <?= $lp['MaLoai'] === $p['MaLoai'] ? 'selected' : '' ?>><?= h($lp['TenLoai']) ?> (<?= fmtVND($lp['DonGia']) ?>)</option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Trạng thái</label>
                                                        <select name="trangThai" class="form-select">
                                                            <?php foreach (['Available'=>'Trống','Reserved'=>'Đã đặt','Occupied'=>'Đang sử dụng','Cleaning'=>'Đang dọn dẹp'] as $val => $label): ?>
                                                            <option value="<?= $val ?>" <?= $p['TrangThai'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mt-4 d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Cập nhật</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-custom">
                    <span class="page-info">Hiển thị <?= count($phong) ?> / <?= count($phong) ?> kết quả</span>
                </div>
            </div>
        </div><!-- /tab-phong -->


        <!-- ==================== TAB: QUẢN LÝ BOOKING ==================== -->
         
        <div class="tab-panel <?= $active_tab === 'booking' ? 'active' : '' ?>" id="tab-booking">
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fffbeb;color:#d97706"><i class="fas fa-clock"></i></div>
                        <div><div class="stat-value"><?= (int)$bk_stats['cho_duyet'] ?></div><div class="stat-label">Chờ duyệt</div></div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fas fa-circle-check"></i></div>
                        <div><div class="stat-value"><?= (int)$bk_stats['da_xac_nhan'] ?></div><div class="stat-label">Đã xác nhận</div></div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fef2f2;color:#dc2626"><i class="fas fa-circle-xmark"></i></div>
                        <div><div class="stat-value"><?= (int)$bk_stats['da_huy'] ?></div><div class="stat-label">Đã hủy</div></div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fas fa-money-bill-wave"></i></div>
                        <div><div class="stat-value" style="font-size:1.1rem"><?= h($bk_stats['doanh_thu_thang']) ?></div><div class="stat-label">Doanh thu tháng này</div></div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="fas fa-calendar-check me-2 text-warning"></i>Danh sách đặt phòng</h5>
                    <div class="search-bar">
                        <i class="fas fa-magnifying-glass search-icon"></i>
                        <input type="text" placeholder="Tìm kiếm..." onkeyup="filterTable(this, 'tbl-booking')">
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-table" id="tbl-booking">
                        <thead>
                            <tr>
                                <th>Mã xác nhận</th>
                                <th>Tên khách</th>
                                <th>Phòng</th>
                                <th>Ngày nhận</th>
                                <th>Ngày trả</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($don_dat_phong)): ?>
                            <tr><td colspan="8" class="text-center text-secondary py-4">Chưa có đơn đặt phòng nào.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($don_dat_phong as $bk):
                                $bcls = $badges[$bk['TrangThaiDon']] ?? ['badge-pending', $bk['TrangThaiDon']];
                            ?>
                            <tr>
                                <td><strong><?= h($bk['MaXacNhan']) ?></strong></td>
                                <td><?= h($bk['TenKhach']) ?></td>
                                <td><?= h($bk['SoPhong']) ?> (<?= h($bk['TenLoai']) ?>)</td>
                                <td><?= fmtNgay($bk['NgayNhan']) ?></td>
                                <td><?= fmtNgay($bk['NgayTra']) ?></td>
                                <td><strong><?= fmtVND($bk['TongTien']) ?></strong></td>
                                <td><span class="badge-status <?= $bcls[0] ?>"><?= $bcls[1] ?></span></td>
                                <td>
                                    <?php if ($bk['TrangThaiDon'] === 'ChoXacNhan'): ?>
                                    <a href="admin.php?tab=booking&action=xac_nhan_booking&id=<?= $bk['MaDon'] ?>" class="btn-action btn-action-confirm" title="Xác nhận"><i class="fas fa-check"></i></a>
                                    <?php endif; ?>
                                    <?php if ($bk['TrangThaiDon'] !== 'DaHuy'): ?>
                                    <a href="admin.php?tab=booking&action=huy_booking&id=<?= $bk['MaDon'] ?>" class="btn-action btn-action-delete" title="Hủy" onclick="return confirm('Hủy đơn đặt phòng này?')"><i class="fas fa-xmark"></i></a>
                                    <?php endif; ?>
                                    <button type="button" class="btn-action btn-action-view" title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#modalXemBooking-<?= $bk['MaDon'] ?>"><i class="fas fa-eye"></i></button>
                                    <a href="admin.php?action=xoa_booking&id=<?= $bk['MaDon'] ?>" 
       class="btn-action text-danger ms-1" 
       title="Xóa vĩnh viễn đơn" 
       onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đơn đặt phòng này không?')">
        <i class="fas fa-trash"></i>
    </a>
                                </td>
                            </tr>

                            <!-- Modal xem chi tiết đơn -->
                            <div class="modal fade" id="modalXemBooking-<?= $bk['MaDon'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header modal-header-custom">
                                            <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Chi Tiết Đơn <?= h($bk['MaXacNhan']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p><strong>Khách hàng:</strong> <?= h($bk['TenKhach']) ?></p>
                                            <p><strong>Khách sạn:</strong> <?= h($bk['TenKS']) ?></p>
                                            <p><strong>Phòng:</strong> <?= h($bk['SoPhong']) ?> – <?= h($bk['TenLoai']) ?></p>
                                            <p><strong>Ngày nhận:</strong> <?= fmtNgay($bk['NgayNhan']) ?> &nbsp; <strong>Ngày trả:</strong> <?= fmtNgay($bk['NgayTra']) ?></p>
                                            <p><strong>Mã khuyến mãi:</strong> <?= $bk['MaKM'] ? h($bk['MaKM']) : '—' ?></p>
                                            <p><strong>Tổng tiền:</strong> <?= fmtVND($bk['TongTien']) ?></p>
                                            <p class="mb-0"><strong>Ngày tạo đơn:</strong> <?= fmtNgay($bk['NgayTao']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-custom">
                    <span class="page-info">Hiển thị <?= count($don_dat_phong) ?> / <?= count($don_dat_phong) ?> kết quả</span>
                </div>
            </div>
        </div><!-- /tab-booking -->


        <!-- ==================== TAB: KHUYẾN MÃI ==================== -->
        <div class="tab-panel <?= $active_tab === 'khuyenmai' ? 'active' : '' ?>" id="tab-khuyenmai">
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fas fa-ticket"></i></div>
                        <div><div class="stat-value"><?= (int)$km_stats['con_han'] ?></div><div class="stat-label">Mã còn hạn</div></div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fef2f2;color:#dc2626"><i class="fas fa-clock-rotate-left"></i></div>
                        <div><div class="stat-value"><?= (int)$km_stats['het_han'] ?></div><div class="stat-label">Mã hết hạn</div></div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-amber"><i class="fas fa-tags"></i></div>
                        <div><div class="stat-value"><?= (int)$km_stats['tong_phieu'] ?></div><div class="stat-label">Tổng số mã</div></div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fas fa-users"></i></div>
                        <div><div class="stat-value"><?= (int)$km_stats['luot_dung'] ?></div><div class="stat-label">Lượt sử dụng</div></div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="fas fa-tag me-2 text-success"></i>Danh sách mã khuyến mãi</h5>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalThemKhuyenMai">
                            <i class="fas fa-plus"></i> Thêm
                        </button>
                        <div class="search-bar">
                            <i class="fas fa-magnifying-glass search-icon"></i>
                            <input type="text" placeholder="Tìm kiếm..." onkeyup="filterTable(this, 'tbl-khuyenmai')">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-table" id="tbl-khuyenmai">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã</th>
                                <th>Tên khuyến mãi</th>
                                <th>Mức giảm</th>
                                <th>Bắt đầu</th>
                                <th>Kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($khuyen_mai)): ?>
                            <tr><td colspan="8" class="text-center text-secondary py-4">Chưa có mã khuyến mãi nào.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($khuyen_mai as $i => $km):
                                $conHan = $km['NgayKetThuc'] >= $today_ymd;
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= h($km['MaKM']) ?></strong></td>
                                <td><?= h($km['TenKM']) ?></td>
                                <td><span class="badge-status badge-active">Giảm <?= (int)$km['PhanTramGiam'] ?>%</span></td>
                                <td><?= fmtNgay($km['NgayBatDau']) ?></td>
                                <td><?= fmtNgay($km['NgayKetThuc']) ?></td>
                                <td>
                                    <span class="badge-status <?= $conHan ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $conHan ? 'Còn hạn' : 'Hết hạn' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action btn-action-edit" data-bs-toggle="modal" data-bs-target="#modalSuaKhuyenMai-<?= h($km['MaKM']) ?>"><i class="fas fa-pen"></i></button>
                                    <a href="admin.php?tab=khuyenmai&action=xoa_khuyenmai&maKM=<?= urlencode($km['MaKM']) ?>" class="btn-action btn-action-delete" onclick="return confirm('Xóa mã này?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>

                            <!-- Modal sửa khuyến mãi -->
                            <div class="modal fade" id="modalSuaKhuyenMai-<?= h($km['MaKM']) ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header modal-header-custom">
                                            <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Sửa Mã <?= h($km['MaKM']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <form method="POST" action="admin.php?tab=khuyenmai&action=sua_khuyenmai">
                                                <input type="hidden" name="maKM" value="<?= h($km['MaKM']) ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Tên khuyến mãi</label>
                                                        <input type="text" name="tenKM" class="form-control" value="<?= h($km['TenKM']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Mức giảm (%)</label>
                                                        <input type="number" name="phanTram" class="form-control" min="1" max="100" value="<?= (int)$km['PhanTramGiam'] ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Ngày bắt đầu</label>
                                                        <input type="date" name="ngayBD" class="form-control" value="<?= h($km['NgayBatDau']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Ngày kết thúc</label>
                                                        <input type="date" name="ngayKT" class="form-control" value="<?= h($km['NgayKetThuc']) ?>">
                                                    </div>
                                                </div>
                                                <div class="mt-4 d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Cập nhật</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-custom">
                    <span class="page-info">Hiển thị <?= count($khuyen_mai) ?> / <?= count($khuyen_mai) ?> kết quả</span>
                </div>
            </div>
        </div><!-- /tab-khuyenmai -->


        <!-- ==================== TAB: TÀI KHOẢN ==================== -->
        <div class="tab-panel <?= $active_tab === 'taikhoan' ? 'active' : '' ?>" id="tab-taikhoan">
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-user-shield"></i></div>
                        <div><div class="stat-value"><?= (int)$tk_stats['admin'] ?></div><div class="stat-label">Tài khoản Admin</div></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fas fa-users"></i></div>
                        <div><div class="stat-value"><?= (int)$tk_stats['khach_hang'] ?></div><div class="stat-label">Tài khoản khách hàng</div></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fas fa-user-tie"></i></div>
                        <div><div class="stat-value"><?= (int)$tk_stats['le_tan'] ?></div><div class="stat-label">Tài khoản lễ tân</div></div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-card-header">
                    <h5><i class="fas fa-users-gear me-2 text-purple" style="color:#7c3aed"></i>Danh sách tài khoản</h5>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalThemTaiKhoan">
                            <i class="fas fa-plus"></i> Thêm
                        </button>
                        <div class="search-bar">
                            <i class="fas fa-magnifying-glass search-icon"></i>
                            <input type="text" placeholder="Tìm kiếm..." onkeyup="filterTable(this, 'tbl-taikhoan')">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="admin-table" id="tbl-taikhoan">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Họ tên</th>
                                <th>Vai trò</th>
                                <th>SĐT</th>
                                <th>Email</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tai_khoan)): ?>
                            <tr><td colspan="7" class="text-center text-secondary py-4">Chưa có tài khoản nào.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($tai_khoan as $i => $tk):
                                $tstatus = $tk['TrangThai'] ?? 'HoatDong';
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= h($tk['HoTen']) ?></td>
                                <td><?= $tk['VaiTro'] === 'Admin' ? 'Admin' : 'Khách hàng' ?></td>
                                <td><?= h($tk['SDT']) ?></td>
                                <td><?= h($tk['Email']) ?></td>
                                <td>
                                    <span class="badge-status <?= $tstatus === 'HoatDong' ? 'badge-active' : 'badge-locked' ?>">
                                        <?= $tstatus === 'HoatDong' ? 'Hoạt động' : 'Bị khóa' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action btn-action-edit" data-bs-toggle="modal" data-bs-target="#modalSuaTaiKhoan-<?= (int)$tk['MaTK'] ?>"><i class="fas fa-pen"></i></button>
                                    <a href="admin.php?tab=taikhoan&action=khoa_taikhoan&maTK=<?= (int)$tk['MaTK'] ?>" class="btn-action btn-action-edit" title="<?= $tstatus === 'HoatDong' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' ?>" onclick="return confirm('Đổi trạng thái tài khoản này?')">
                                        <i class="fas <?= $tstatus === 'HoatDong' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                    </a>
                                    <a href="admin.php?tab=taikhoan&action=xoa_taikhoan&maTK=<?= (int)$tk['MaTK'] ?>" class="btn-action btn-action-delete" onclick="return confirm('Xóa tài khoản này?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>

                            <!-- Modal sửa tài khoản -->
                            <div class="modal fade" id="modalSuaTaiKhoan-<?= (int)$tk['MaTK'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header modal-header-custom">
                                            <h5 class="modal-title"><i class="fas fa-user-pen me-2"></i>Sửa Tài Khoản: <?= h($tk['HoTen']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <form method="POST" action="admin.php?tab=taikhoan&action=sua_taikhoan">
                                                <input type="hidden" name="maTK" value="<?= (int)$tk['MaTK'] ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Họ tên</label>
                                                        <input type="text" name="hoTen" class="form-control" value="<?= h($tk['HoTen']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Email</label>
                                                        <input type="email" name="email" class="form-control" value="<?= h($tk['Email']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Số điện thoại</label>
                                                        <input type="text" name="sdt" class="form-control" value="<?= h($tk['SDT']) ?>" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold">Vai trò</label>
                                                        <select name="vaiTro" class="form-select">
                                                            <option value="ThanhVien" <?= $tk['VaiTro'] === 'ThanhVien' ? 'selected' : '' ?>>Khách hàng</option>
                                                            <option value="Admin" <?= $tk['VaiTro'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold">Trạng thái</label>
                                                        <select name="trangThai" class="form-select">
                                                            <option value="HoatDong" <?= $tstatus === 'HoatDong' ? 'selected' : '' ?>>Hoạt động</option>
                                                            <option value="BiKhoa" <?= $tstatus === 'BiKhoa' ? 'selected' : '' ?>>Bị khóa</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Mật khẩu mới (bỏ trống nếu không đổi)</label>
                                                        <input type="password" name="matKhau" class="form-control" placeholder="••••••••">
                                                    </div>
                                                </div>
                                                <div class="mt-4 d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Cập nhật</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-custom">
                    <span class="page-info">Hiển thị <?= count($tai_khoan) ?> / <?= count($tai_khoan) ?> kết quả</span>
                </div>
            </div>
        </div><!-- /tab-taikhoan -->

    </main>
</div><!-- /main-wrapper -->


<!-- ======================== MODALS "THÊM MỚI" ======================== -->

<!-- Modal: Thêm chi nhánh -->
<div class="modal fade" id="modalThemChiNhanh" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm Chi Nhánh Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="admin.php?tab=khachsan&action=them_chinhanh">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mã chi nhánh <span class="text-danger">*</span></label>
                            <input type="text" name="maKS" class="form-control" placeholder="VD: HP01" maxlength="10" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Tên chi nhánh <span class="text-danger">*</span></label>
                            <input type="text" name="tenKS" class="form-control" placeholder="VD: Luxury Hotel Hải Phòng" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ <span class="text-danger">*</span></label>
                            <input type="text" name="diaChi" class="form-control" placeholder="VD: 12 Lạch Tray, Hải Phòng" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea name="moTa" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Thêm phòng -->
<div class="modal fade" id="modalThemPhong" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm Phòng Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="admin.php?tab=phong&action=them_phong">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mã phòng <span class="text-danger">*</span></label>
                            <input type="text" name="maPhong" class="form-control" placeholder="VD: H401" maxlength="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Số phòng <span class="text-danger">*</span></label>
                            <input type="text" name="soPhong" class="form-control" placeholder="VD: 401" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Chi nhánh <span class="text-danger">*</span></label>
                            <select name="maKS" class="form-select" required>
                                <option value="">-- Chọn chi nhánh --</option>
                                <?php foreach ($danhSachKS as $ks): ?>
                                <option value="<?= h($ks['MaKS']) ?>"><?= h($ks['TenKS']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Loại phòng <span class="text-danger">*</span></label>
                            <select name="maLoai" class="form-select" required>
                                <option value="">-- Chọn loại phòng --</option>
                                <?php foreach ($danhSachLoai as $lp): ?>
                                <option value="<?= h($lp['MaLoai']) ?>"><?= h($lp['TenLoai']) ?> (<?= fmtVND($lp['DonGia']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="trangThai" class="form-select">
                                <option value="Available">Trống</option>
                                <option value="Reserved">Đã đặt</option>
                                <option value="Occupied">Đang sử dụng</option>
                                <option value="Cleaning">Đang dọn dẹp</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Lưu phòng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Thêm khuyến mãi -->
<div class="modal fade" id="modalThemKhuyenMai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title"><i class="fas fa-tag me-2"></i>Thêm Mã Khuyến Mãi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="admin.php?tab=khuyenmai&action=them_khuyenmai">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mã khuyến mãi <span class="text-danger">*</span></label>
                            <input type="text" name="maKM" class="form-control" placeholder="VD: AUTUMN26" maxlength="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tên khuyến mãi <span class="text-danger">*</span></label>
                            <input type="text" name="tenKM" class="form-control" placeholder="VD: Ưu Đãi Mùa Thu" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mức giảm (%) <span class="text-danger">*</span></label>
                            <input type="number" name="phanTram" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngày bắt đầu</label>
                            <input type="date" name="ngayBD" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngày kết thúc</label>
                            <input type="date" name="ngayKT" class="form-control">
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Thêm tài khoản -->
<div class="modal fade" id="modalThemTaiKhoan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Thêm Tài Khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="admin.php?tab=taikhoan&action=them_taikhoan">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" name="hoTen" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="sdt" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="matKhau" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vai trò</label>
                            <select name="vaiTro" class="form-select">
                                <option value="ThanhVien">Khách hàng</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="trangThai" class="form-select">
                                <option value="HoatDong">Hoạt động</option>
                                <option value="BiKhoa">Bị khóa</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-primary-custom"><i class="fas fa-floppy-disk"></i> Tạo tài khoản</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ===================== TAB SWITCHING =====================
const tabLinks  = document.querySelectorAll('.nav-link-tab');
const tabPanels = document.querySelectorAll('.tab-panel');
const topbarTitle = document.getElementById('topbar-title');

const tabTitles = {
    dashboard: 'Trang Chủ',
    khachsan:  'Thông Tin Khách Sạn',
    phong:     'Quản Lý Phòng',
    booking:   'Quản Lý Đặt Phòng',
    khuyenmai: 'Quản Lý Khuyến Mãi',
    taikhoan:  'Quản Lý Tài Khoản',
};

function switchTab(tabId, updateUrl = true) {
    tabLinks.forEach(a => a.classList.remove('active'));
    tabPanels.forEach(p => p.classList.remove('active'));

    const link = document.querySelector(`.nav-link-tab[data-tab="${tabId}"]`);
    const panel = document.getElementById(`tab-${tabId}`);

    if (link)  link.classList.add('active');
    if (panel) panel.classList.add('active');
    if (topbarTitle) topbarTitle.textContent = tabTitles[tabId] || '';

    if (updateUrl) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        url.searchParams.delete('action');
        url.searchParams.delete('msg');
        history.replaceState(null, '', url);
    }
}

tabLinks.forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        switchTab(this.dataset.tab);
        // On mobile: close sidebar after selecting
        closeSidebar();
    });
});

// ===================== SIDEBAR (MOBILE) =====================
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

// ===================== TABLE FILTER =====================
function filterTable(input, tableId) {
    const filter = input.value.toLowerCase();
    const rows = document.getElementById(tableId).querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

// ===================== CLOCK =====================
function updateClock() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const dateStr = now.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    const el = document.getElementById('clock');
    if (el) el.textContent = `${dateStr} ${timeStr}`;
}
updateClock();
setInterval(updateClock, 1000);
</script>
</body>
</html>
