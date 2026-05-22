<?php
// Nhúng model vào để xài hàm timPhongTrong()
require_once '../models/mdl_phong.php';

// Kiểm tra xem khách có bấm nút "Tìm Kiếm" từ form không
// Giả sử form dùng phương thức GET
if (isset($_GET['btn_timkiem'])) {
    
    // 1. Nhận dữ liệu từ Form (HTML cần có các input name="diadiem", "checkin", "checkout")
    $diaDiemKS = $_GET['diadiem']; // Ví dụ: 'HN01' hoặc 'DN01'
    $ngayNhan = $_GET['checkin'];
    $ngayTra = $_GET['checkout'];

    // Lọc lỗi cơ bản: Ngày trả phải lớn hơn ngày nhận
    if (strtotime($ngayTra) <= strtotime($ngayNhan)) {
        echo "<script>alert('Lỗi: Ngày trả phòng phải sau ngày nhận phòng!'); history.back();</script>";
        exit;
    }

    // 2. Chạy thuật toán lõi
    $danhSachPhongTrong = timPhongTrong($diaDiemKS, $ngayNhan, $ngayTra);

    // 3. Đưa dữ liệu sang View (Giao diện) để in ra màn hình
    // Ở đây mình tạm dùng print_r để test thử data trước khi làm giao diện đẹp
    echo "<h3>Kết quả tìm kiếm từ $ngayNhan đến $ngayTra:</h3>";
    
    if (count($danhSachPhongTrong) > 0) {
        echo "<pre>";
        print_r($danhSachPhongTrong); // In mảng ra xem kết quả DB trả về đúng không
        echo "</pre>";
    } else {
        echo "<p>Rất tiếc, đã hết phòng trống trong khoảng thời gian này!</p>";
    }
}
?>