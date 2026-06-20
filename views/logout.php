<?php
/**
 * logout.php
 * -----------------------------------------------------------
 * Hủy phiên làm việc (session) hiện tại và chuyển hướng về trang đăng nhập.
 * -----------------------------------------------------------
 */
session_start();
session_unset();
session_destroy();
header('Location: auth.php?msg=' . urlencode('Đã đăng xuất') . '&type=default');
exit;
