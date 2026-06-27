<?php
session_start();
// Giả định file kết nối CSDL sử dụng PDO biến $conn
require_once '../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ================= XỬ LÝ ĐĂNG KÝ =================
    if ($action === 'register') {
        $hoTen = trim($_POST['hoTen']);
        $email = trim($_POST['email']);
        $sdt = trim($_POST['sdt']);
        $matKhau = $_POST['matKhau'];

        // Kiểm tra mật khẩu >= 6 ký tự
        if (strlen($matKhau) < 6) {
            die("Mật khẩu phải từ 6 ký tự trở lên.");
        }

        // Kiểm tra xem Email hoặc SĐT đã tồn tại chưa
        $stmtCheck = $pdo->prepare("SELECT MaTK FROM tai_khoan WHERE Email = ? OR SDT = ?");
        $stmtCheck->execute([$email, $sdt]);
        if ($stmtCheck->rowCount() > 0) {
            die("Email hoặc Số điện thoại đã tồn tại trong hệ thống.");
        }

        // Mã hóa mật khẩu
        $matKhauHash = password_hash($matKhau, PASSWORD_BCRYPT);

        // Lưu vào CSDL
        $stmtInsert = $pdo->prepare("INSERT INTO tai_khoan (HoTen, Email, SDT, MatKhau, VaiTro) VALUES (?, ?, ?, ?, 'ThanhVien')");
        if ($stmtInsert->execute([$hoTen, $email, $sdt, $matKhauHash])) {
            echo "Đăng ký thành công! Vui lòng đăng nhập.";
        } else {
            echo "Có lỗi xảy ra trong quá trình đăng ký.";
        }
    } 
    // ================= XỬ LÝ ĐĂNG NHẬP =================
    elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $matKhau = trim($_POST['matKhau']);

        $stmt = $pdo->prepare("SELECT MaTK, HoTen, MatKhau, VaiTro FROM tai_khoan WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu đã hash
        if ($user && password_verify($matKhau, $user['MatKhau'])) {
            // Lưu thông tin vào Session
            $_SESSION['maTK'] = $user['MaTK'];
            $_SESSION['hoTen'] = $user['HoTen'];
            $_SESSION['vaiTro'] = $user['VaiTro'];
            
            echo "Đăng nhập thành công!";
        } else {
            echo "Email hoặc mật khẩu không chính xác!";
        }
    }
}
?>