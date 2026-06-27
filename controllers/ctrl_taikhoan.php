<?php
session_start();
require_once '../config/database.php';

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

        // Mã hóa mật khẩu chuẩn Bcrypt
        $matKhauHash = password_hash($matKhau, PASSWORD_BCRYPT);

        $stmtInsert = $pdo->prepare("INSERT INTO tai_khoan (HoTen, Email, SDT, MatKhau, VaiTro) VALUES (?, ?, ?, ?, 'ThanhVien')");
        if ($stmtInsert->execute([$hoTen, $email, $sdt, $matKhauHash])) {
            $maMoi = $pdo->lastInsertId();
            $_SESSION['MaTK']   = $maMoi;
            $_SESSION['HoTen']  = $hoTen;
            $_SESSION['Email']  = $email;
            $_SESSION['VaiTro'] = 'ThanhVien';
            header('Location: ../views/index.php?msg=' . urlencode('Đăng ký thành công!'));
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
}
