<?php
/**
 * includes/functions.php
 * -----------------------------------------------------------
 * Tập hợp các hàm tiện ích (helper functions) dùng chung cho toàn bộ
 * dự án Luxury Hotel: định dạng tiền tệ, định dạng ngày, sinh mã đặt
 * phòng, kiểm tra trạng thái đăng nhập, hiển thị badge trạng thái...
 * -----------------------------------------------------------
 */

/**
 * Định dạng số tiền sang chuỗi VNĐ (VD: 1500000 -> "1.500.000 VNĐ")
 */
function fmtVND($soTien)
{
    return number_format((float)$soTien, 0, ',', '.') . ' VNĐ';
}

/**
 * Định dạng ngày từ yyyy-mm-dd sang dd/mm/yyyy để hiển thị
 */
function fmtNgay($ngay)
{
    if (empty($ngay)) return '';
    $timestamp = strtotime($ngay);
    return $timestamp ? date('d/m/Y', $timestamp) : htmlspecialchars($ngay);
}

/**
 * Tính số đêm lưu trú giữa ngày nhận và ngày trả
 */
function tinhSoDem($ngayNhan, $ngayTra)
{
    $tsNhan = strtotime($ngayNhan);
    $tsTra  = strtotime($ngayTra);
    if (!$tsNhan || !$tsTra || $tsTra <= $tsNhan) return 0;
    return (int)ceil(($tsTra - $tsNhan) / 86400);
}

/**
 * Sinh mã xác nhận đặt phòng duy nhất (định dạng giống bản gốc: LH-1234-HN)
 * Việc bảo đảm tính DUY NHẤT tuyệt đối trên CSDL được thực hiện ở nơi gọi hàm
 * bằng cách kiểm tra UNIQUE KEY `MaXacNhan` trong bảng don_dat_phong.
 */
function sinhMaXacNhan($maTinh = '')
{
    $tienTo = ['LH', 'AX', 'RX'][array_rand(['LH', 'AX', 'RX'])];
    $soNgauNhien = random_int(1000, 9999);
    $hauTo = $maTinh !== '' ? $maTinh : ['HN', 'DN', 'SG'][array_rand(['HN', 'DN', 'SG'])];
    return "{$tienTo}-{$soNgauNhien}-{$hauTo}";
}

/**
 * Trả về đoạn HTML badge Bootstrap tương ứng với trạng thái đơn đặt phòng
 */
function getStatusBadgeDonHang($trangThai)
{
    $map = [
        'ChoXacNhan' => ['label' => 'Chờ xác nhận', 'class' => 'bg-warning text-dark'],
        'DaXacNhan'  => ['label' => 'Đã xác nhận',  'class' => 'bg-info text-dark'],
        'HoanTat'    => ['label' => 'Hoàn tất',     'class' => 'bg-success'],
        'DaHuy'      => ['label' => 'Đã hủy',       'class' => 'bg-danger'],
    ];
    $info = $map[$trangThai] ?? ['label' => htmlspecialchars($trangThai), 'class' => 'bg-secondary'];
    return '<span class="badge ' . $info['class'] . ' rounded-pill">' . $info['label'] . '</span>';
}

/**
 * Trả về đoạn HTML badge Bootstrap tương ứng với trạng thái phòng
 */
function getStatusBadgePhong($trangThai)
{
    $map = [
        'Available' => ['label' => 'Trống',        'class' => 'bg-success'],
        'Reserved'  => ['label' => 'Đã đặt',        'class' => 'bg-warning text-dark'],
        'Occupied'  => ['label' => 'Đang sử dụng',  'class' => 'bg-danger'],
        'Cleaning'  => ['label' => 'Đang dọn dẹp',  'class' => 'bg-secondary'],
    ];
    $info = $map[$trangThai] ?? ['label' => htmlspecialchars($trangThai), 'class' => 'bg-secondary'];
    return '<span class="badge ' . $info['class'] . ' rounded-pill">' . $info['label'] . '</span>';
}

/**
 * Sinh chuỗi sao (★/☆) theo số nguyên (mặc định coi tất cả loại phòng có 5 cấp hiển thị)
 */
function renderSao($soSao)
{
    $soSao = max(0, min(5, (int)$soSao));
    return str_repeat('★', $soSao) . str_repeat('☆', 5 - $soSao);
}

/**
 * Map tên loại phòng sang số sao hiển thị mang tính chất trang trí UI
 * (Standard: 3, Deluxe: 4, Suite: 5)
 */
function maLoaiSangSoSao($tenLoai)
{
    $map = ['Standard' => 3, 'Deluxe' => 4, 'Suite' => 5];
    foreach ($map as $key => $val) {
        if (stripos($tenLoai, $key) !== false) return $val;
    }
    return 3;
}

/**
 * Tách chuỗi tiện ích (lưu dạng "TV, Mini-bar, Điều hòa") thành mảng
 */
function tachTienIch($chuoiTienIch)
{
    if (empty($chuoiTienIch)) return [];
    return array_map('trim', explode(',', $chuoiTienIch));
}

/**
 * Trả về icon emoji tương ứng với từng tiện ích để hiển thị giao diện
 */
function iconTienIch($tenTienIch)
{
    $map = [
        'TV' => '📺', 'Wifi miễn phí' => '📶', 'Wifi' => '📶', 'Wi-Fi' => '📶',
        'Mini-bar' => '🍸', 'Bàn làm việc' => '💼', 'Bồn tắm' => '🛁',
        'Điều hòa' => '❄️', 'Sofa' => '🛋️',
    ];
    foreach ($map as $key => $icon) {
        if (stripos($tenTienIch, $key) !== false) return $icon;
    }
    return '✓';
}

/**
 * Lấy chữ cái đầu của Tên (từ cuối cùng trong Họ Tên) để hiển thị avatar.
 * Xử lý an toàn với tiếng Việt có dấu (UTF-8 multibyte).
 * VD: "Trần Văn An" -> "A"
 */
function layChuCaiDauTen($hoTen)
{
    $hoTen = trim($hoTen ?? '');
    if ($hoTen === '') return '?';
    $cacTu = preg_split('/\s+/u', $hoTen);
    $tuCuoi = end($cacTu);
    if (function_exists('mb_substr')) {
        return mb_strtoupper(mb_substr($tuCuoi, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return strtoupper(substr($tuCuoi, 0, 1));
}

/**
 * Kiểm tra người dùng đã đăng nhập hay chưa (dựa trên session)
 */
function daDangNhap()
{
    return isset($_SESSION['MaTK']);
}

/**
 * Kiểm tra người dùng đang đăng nhập có phải Admin hay không
 */
function laAdmin()
{
    return daDangNhap() && ($_SESSION['VaiTro'] ?? '') === 'Admin';
}

/**
 * Yêu cầu đăng nhập trước khi cho phép truy cập trang hiện tại.
 * Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập.
 */
function yeuCauDangNhap()
{
    if (!daDangNhap()) {
        header('Location: views/auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Yêu cầu quyền Admin trước khi cho phép truy cập trang quản trị.
 */
function yeuCauAdmin()
{
    if (!laAdmin()) {
        header('Location: views/auth.php');
        exit;
    }
}

/**
 * Hàm rút gọn để vừa chống XSS vừa rút gọn cú pháp khi in dữ liệu ra view
 */
function h($chuoi)
{
    return htmlspecialchars($chuoi ?? '', ENT_QUOTES, 'UTF-8');
}
