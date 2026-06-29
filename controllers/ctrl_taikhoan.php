<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ================= 1. XỬ LÝ ĐĂNG KÝ  =================
    if ($action === 'dangKy') {
        $hoTen    = trim($_POST['hoTen'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $sdt      = trim($_POST['sdt'] ?? '');
        $matKhau  = $_POST['matKhau'] ?? '';
        $matKhau2 = $_POST['matKhau2'] ?? '';

        if (!$hoTen || !$email || !$sdt || !$matKhau) {
            header('Location: ../views/auth.php?mode=register&err=' . urlencode('Vui lòng nhập đầy đủ thông tin'));
            exit;
        }
        if ($matKhau !== $matKhau2) {
            header('Location: ../views/auth.php?mode=register&err=' . urlencode('Mật khẩu xác nhận không khớp'));
            exit;
        }

        // Kiểm tra trùng lặp Email hoặc SĐT
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM tai_khoan WHERE Email = ? OR SDT = ?");
        $stmtCheck->execute([$email, $sdt]);
        if ($stmtCheck->fetchColumn() > 0) {
            header('Location: ../views/auth.php?mode=register&err=' . urlencode('Email hoặc SĐT đã tồn tại trong hệ thống'));
            exit;
        }

        // VÁ LỖI TRANG TRẮNG: Chèn trực tiếp mật khẩu thô vào CSDL
        $stmtInsert = $pdo->prepare("INSERT INTO tai_khoan (HoTen, Email, SDT, MatKhau, VaiTro) VALUES (?, ?, ?, ?, 'ThanhVien')");
        
        if ($stmtInsert->execute([$hoTen, $email, $sdt, $matKhau])) {
            $maMoi = $pdo->lastInsertId();
            $_SESSION['MaTK']   = $maMoi;
            $_SESSION['HoTen']  = $hoTen;
            $_SESSION['Email']  = $email;
            $_SESSION['VaiTro'] = 'ThanhVien';

            header('Location: ../views/index.php?msg=' . urlencode('Đăng ký tài khoản thành công!'));
            exit;
        } else {
            header('Location: ../views/auth.php?mode=register&err=' . urlencode('Lỗi hệ thống, không thể tạo tài khoản lúc này'));
            exit;
        }
    }
    // ================= 2. XỬ LÝ ĐĂNG NHẬP (ĐỐI CHIẾU MẬT KHẨU THÔ) =================
    elseif ($action === 'dangNhap') {
        $email   = trim($_POST['email'] ?? '');
        $matKhau = $_POST['matKhau'] ?? '';

        $stmt = $pdo->prepare("SELECT MaTK, HoTen, Email, MatKhau, VaiTro FROM tai_khoan WHERE Email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra trực tiếp chuỗi mật khẩu thô
        if ($user && $matKhau === $user['MatKhau']) {
            $_SESSION['MaTK']   = $user['MaTK'];
            $_SESSION['HoTen']  = $user['HoTen'];
            $_SESSION['Email']  = $user['Email'];
            $_SESSION['VaiTro'] = $user['VaiTro'];

            $url = ($user['VaiTro'] === 'Admin') ? '../views/admin.php' : '../views/index.php';
            header('Location: ' . $url);
            exit;
        } else {
            header('Location: ../views/auth.php?mode=login&err=' . urlencode('Email hoặc mật khẩu không chính xác'));
            exit;
        }
    }
    // ================= 3. GỬI MÃ OTP QUÊN MẬT KHẨU (LƯU VÀO SESSION RAM) =================
    elseif ($action === 'quenMatKhau') {
        $email = trim($_POST['email'] ?? '');

        if (!$email) {
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Vui lòng nhập địa chỉ email'));
            exit;
        }

        $stmt = $pdo->prepare("SELECT MaTK FROM tai_khoan WHERE Email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $otp = random_int(100000, 999999);

            // Ghi nhớ tạm vào RAM máy chủ
            $_SESSION['reset_otp']        = (string)$otp;
            $_SESSION['reset_otp_expire'] = time() + (5 * 60); // Hạn 5 phút
            $_SESSION['otp_email']        = $email;

            header('Location: ../views/auth.php?mode=forgot&step=otp&demo_otp=' . urlencode($otp));
            exit;
        } else {
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Email này chưa được đăng ký trong hệ thống'));
            exit;
        }
    }
    // ================= 4. XÁC NHẬN OTP & ĐẶT LẠI MẬT KHẨU =================
    elseif ($action === 'xacNhanOTP') {
        $email       = trim($_POST['email'] ?? '');
        $otpNhap     = trim($_POST['otp'] ?? '');
        $matKhauMoi  = $_POST['matKhauMoi'] ?? '';
        $matKhauMoi2 = $_POST['matKhauMoi2'] ?? '';

        if (!$email || !$otpNhap || !$matKhauMoi) {
            header('Location: ../views/auth.php?mode=forgot&step=otp&err=' . urlencode('Vui lòng nhập đầy đủ thông tin'));
            exit;
        }
        if ($matKhauMoi !== $matKhauMoi2) {
            header('Location: ../views/auth.php?mode=forgot&step=otp&err=' . urlencode('Mật khẩu xác nhận không khớp'));
            exit;
        }
        if (strlen($matKhauMoi) < 6) {
            header('Location: ../views/auth.php?mode=forgot&step=otp&err=' . urlencode('Mật khẩu mới phải từ 6 ký tự trở lên'));
            exit;
        }

        $sessionOtp    = $_SESSION['reset_otp'] ?? null;
        $sessionExpire = $_SESSION['reset_otp_expire'] ?? null;
        $sessionEmail  = $_SESSION['otp_email'] ?? null;

        if (!$sessionOtp || !$sessionExpire || $sessionEmail !== $email) {
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Phiên giao dịch hết hạn hoặc không hợp lệ, vui lòng thao tác lại'));
            exit;
        }

        if (time() > $sessionExpire) {
            unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expire'], $_SESSION['otp_email']);
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Mã OTP đã hết hạn, vui lòng yêu cầu mã mới'));
            exit;
        }

        if ($sessionOtp !== $otpNhap) {
            header('Location: ../views/auth.php?mode=forgot&step=otp&err=' . urlencode('Mã OTP nhập vào không chính xác'));
            exit;
        }

        // Cập nhật thẳng mật khẩu mới dạng thô xuống CSDL
        $stmtU = $pdo->prepare("UPDATE tai_khoan SET MatKhau = :mk WHERE Email = :em");
        $stmtU->execute([':mk' => $matKhauMoi, ':em' => $email]);

        unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expire'], $_SESSION['otp_email']);

        header('Location: ../views/auth.php?mode=login&msg=' . urlencode('Đặt lại mật khẩu thành công! Vui lòng đăng nhập.'));
        exit;
    }
}