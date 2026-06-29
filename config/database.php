<?php
/**
 * database.php
 * -----------------------------------------------------------
 * File cấu hình kết nối Cơ sở dữ liệu cho dự án Luxury Hotel.
 * Sử dụng PDO + Prepared Statement để đảm bảo an toàn (chống SQL Injection).
 * Mọi file PHP khác trong dự án đều require_once file này để lấy biến $pdo.
 * -----------------------------------------------------------
 */

// Thông tin cấu hình Database (khớp với file luxuryhotel_webbooking.sql)
$host = '127.0.0.1';
$port = '3308';                         // Port máy chủ MySQL/MariaDB nhóm đang dùng (XAMPP có thể là 3306)
$db   = 'luxuryhotel_webbooking';       // Tên database đúng theo file SQL được cung cấp
$user = 'root';                          // Username mặc định của XAMPP/WAMP
$pass = '';                              // Mật khẩu mặc định (thường để trống)

// Cấu hình DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

// Cấu hình các tùy chọn cho PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ném ra ngoại lệ khi có lỗi
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Trả về dữ liệu dạng mảng kết hợp
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Tắt mô phỏng prepare (tăng bảo mật chống injection)
];

try {
    // Khởi tạo đối tượng PDO dùng chung cho toàn bộ ứng dụng
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Bắt lỗi nếu kết nối thất bại, dừng thực thi ngay để tránh lỗi dây chuyền
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
