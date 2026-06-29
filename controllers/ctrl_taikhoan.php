<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ================= XỬ LÝ ĐĂNG KÝ =================
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

        // Kiểm tra trùng lặp bằng $pdo
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM tai_khoan WHERE Email = ? OR SDT = ?");
        $stmtCheck->execute([$email, $sdt]);
        if ($stmtCheck->fetchColumn() > 0) {
            header('Location: ../views/auth.php?mode=register&err=' . urlencode('Email hoặc SĐT đã tồn tại'));
            exit;
        }
    }
    // ================= XỬ LÝ ĐĂNG NHẬP =================
    elseif ($action === 'dangNhap') {
        $email   = trim($_POST['email'] ?? '');
        $matKhau = $_POST['matKhau'] ?? '';

        $stmt = $pdo->prepare("SELECT MaTK, HoTen, Email, MatKhau, VaiTro FROM tai_khoan WHERE Email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu (hỗ trợ cả mật khẩu băm Bcrypt lẫn mật khẩu thô cũ)
        $passDung = false;
        if ($user) {
            if (password_verify($matKhau, $user['MatKhau']) || $matKhau === $user['MatKhau']) {
                $passDung = true;
            }
        }

        if ($passDung) {
            $_SESSION['MaTK']   = $user['MaTK'];
            $_SESSION['HoTen']  = $user['HoTen'];
            $_SESSION['Email']  = $user['Email'];
            $_SESSION['VaiTro'] = $user['VaiTro'];

            $url = ($user['VaiTro'] === 'Admin') ? '../views/admin.php' : '../views/index.php';
            header('Location: ' . $url);
            exit;
        } else {
            header('Location: ../views/auth.php?mode=login&err=' . urlencode('Email hoặc mật khẩu sai'));
            exit;
        }
    }
    // ================= BƯỚC 1: GỬI MÃ OTP QUÊN MẬT KHẨU (LƯU VÀO SESSION) =================
    elseif ($action === 'quenMatKhau') {
        $email = trim($_POST['email'] ?? '');

        if (!$email) {
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Vui lòng nhập email'));
            exit;
        }

        $stmt = $pdo->prepare("SELECT MaTK FROM tai_khoan WHERE Email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 1. Sinh ngẫu nhiên mã OTP 6 chữ số
            $otp = random_int(100000, 999999);

            // 2. KHÔNG DÙNG SQL UPDATE: Lưu thẳng vào Session của trình duyệt khách hàng
            $_SESSION['reset_otp']        = (string)$otp;
            $_SESSION['reset_otp_expire'] = time() + (5 * 60); // Thời hạn 5 phút
            $_SESSION['otp_email']        = $email;            // Lưu email để đối chiếu

            // Gửi OTP qua URL để hiển thị trực tiếp lên giao diện Demo
            header('Location: ../views/auth.php?mode=forgot&step=otp&demo_otp=' . urlencode($otp));
            exit;
        } else {
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Email này chưa được đăng ký trong hệ thống'));
            exit;
        }
    }
    // ================= BƯỚC 2: XÁC NHẬN OTP TỪ SESSION & ĐẶT LẠI MẬT KHẨU =================
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
            header('Location: ../views/auth.php?mode=forgot&step=otp&err=' . urlencode('Mật khẩu mới tối thiểu 6 ký tự'));
            exit;
        }

        // 1. Lấy dữ liệu OTP đã lưu trong Session ra kiểm tra
        $sessionOtp    = $_SESSION['reset_otp'] ?? null;
        $sessionExpire = $_SESSION['reset_otp_expire'] ?? null;
        $sessionEmail  = $_SESSION['otp_email'] ?? null;

        if (!$sessionOtp || !$sessionExpire || $sessionEmail !== $email) {
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Yêu cầu không hợp lệ hoặc phiên làm việc đã bị hủy, vui lòng làm lại từ đầu'));
            exit;
        }

        // 2. Kiểm tra thời gian hết hạn (Quá 5 phút = hủy)
        if (time() > $sessionExpire) {
            unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expire'], $_SESSION['otp_email']);
            header('Location: ../views/auth.php?mode=forgot&err=' . urlencode('Mã OTP đã hết hạn (quá 5 phút), vui lòng yêu cầu mã mới'));
            exit;
        }

        // 3. Đối chiếu mã nhập vào
        if ($sessionOtp !== $otpNhap) {
            header('Location: ../views/auth.php?mode=forgot&step=otp&err=' . urlencode('Mã OTP nhập vào không chính xác'));
            exit;
        }

        // 4. OTP hợp lệ -> Mã hóa Bcrypt và cập nhật mật khẩu mới vào SQL
        $matKhauHash = password_hash($matKhauMoi, PASSWORD_BCRYPT);
        $stmtU = $pdo->prepare("UPDATE tai_khoan SET MatKhau = :mk WHERE Email = :em");
        $stmtU->execute([':mk' => $matKhauHash, ':em' => $email]);

        // 5. Đổi xong xóa sạch Session OTP đi để không bị dùng lại
        unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expire'], $_SESSION['otp_email']);

        header('Location: ../views/auth.php?mode=login&msg=' . urlencode('Đặt lại mật khẩu thành công! Vui lòng đăng nhập bằng mật khẩu mới.'));
        exit;
    }
}
