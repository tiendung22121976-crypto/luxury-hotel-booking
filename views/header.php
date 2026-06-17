<?php
require_once __DIR__ . '/../config/database.php'; 
// Kiểm tra nếu hàm chưa tồn tại thì mới định nghĩa để tránh lỗi trùng lặp
if (!function_exists('fmtVND')) {
    function fmtVND($price) {
        return number_format($price, 0, ',', '.') . ' VNĐ';
    }
}
if (!function_exists('diffDays')) {
    function diffDays($checkIn, $checkOut) {
        $date1 = new DateTime($checkIn);
        $date2 = new DateTime($checkOut);
        // Tính khoảng cách giữa 2 ngày
        $interval = $date1->diff($date2);
        return $interval->days; // Trả về số ngày (dạng số nguyên)
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Các dòng require_once hay include tiếp theo của bạn...
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Hotel Chain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #F8F6F1; }
        .font-luxury { font-family: 'Playfair Display', serif; }
        .bg-navy { background-color: #0D1B2A !important; }
        .text-navy { color: #0D1B2A !important; }
        .text-gold { color: #C9A84C !important; }
        .border-navy { border-color: #0D1B2A !important; }
        .btn-gold { background-color: #C9A84C; color: #fff; border: none; }
        .btn-gold:hover { background-color: #E8C97A; color: #0D1B2A; }
        .btn-navy { background-color: #0D1B2A; color: #fff; border: 1px solid #0D1B2A; }
        .btn-navy:hover { background-color: #1B3A5C; color: #C9A84C; }
        .btn-outline-navy { background-color: transparent; color: #0D1B2A; border: 1px solid #0D1B2A; }
        .btn-outline-navy:hover { background-color: #0D1B2A; color: #fff; }
        .card-custom { border: none; border-radius: 12px; transition: transform 0.2s; }
        .card-custom:hover { transform: translateY(-5px); }
        .list-group-item-action:hover { background-color: #F8F6F1; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-navy border-bottom border-warning py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand font-luxury fw-bold fs-3 text-white" href="index.php">
            LUXURY <span class="text-gold">HOTEL</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link text-white px-3" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link text-white px-3" href="search.php">Tìm phòng</a></li>
                
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item"><a class="nav-link text-gold fw-medium px-3" href="account.php">👤 <?php echo htmlspecialchars($_SESSION['user']['HoTen']); ?></a></li>
                    <?php if ($_SESSION['user']['VaiTro'] === 'Admin'): ?>
                        <li class="nav-item"><a class="btn btn-sm btn-outline-warning mx-2" href="admin.php">🛡️ Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="btn btn-sm btn-danger px-3 mx-2" href="logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link text-white px-3" href="auth.php">Đăng ký</a></li>
                    <li class="nav-item"><a class="btn btn-gold btn-sm px-4 mx-2 rounded-pill" href="auth.php">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4">
