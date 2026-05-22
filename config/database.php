<?php
// Thông tin cấu hình Database
$host = '127.0.0.1';
$port = '3308'; // Port máy chủ MySQL nhóm đang dùng
$db   = 'luxuryhotel_webbooking'; // Tên database chuẩn của nhóm
$user = 'root'; // Username mặc định của XAMPP/WAMP
$pass = ''; // Mật khẩu mặc định (thường để trống)

// Cấu hình DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

// Cấu hình các tùy chọn cho PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ném ra ngoại lệ khi có lỗi
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Trả về dữ liệu dạng mảng kết hợp (dễ thao tác)
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Tắt mô phỏng prepare statement (Tăng cường bảo mật)
];

try {
    // Khởi tạo đối tượng PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Dòng dưới dùng để test kết nối Database
    echo "Kết nối Database luxuryhotel_webbooking thành công!";
} catch (\PDOException $e) {
    // Bắt lỗi nếu kết nối thất bại
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
