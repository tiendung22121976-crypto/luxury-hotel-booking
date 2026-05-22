<?php
// Gọi file kết nối Database vào đây
require_once '../config/database.php';

function timPhongTrong($maKS, $ngayNhan, $ngayTra) {
    global $pdo; // Sử dụng biến kết nối PDO từ database.php
    
    // Câu lệnh SQL "Lõi"
    $sql = "
        SELECT p.MaPhong, p.SoPhong, lp.TenLoai, lp.DonGia, lp.HinhAnhDaiDien
        FROM phong p
        JOIN loai_phong lp ON p.MaLoai = lp.MaLoai
        WHERE p.MaKS = :maKS 
        AND p.TrangThai = 'Available' -- Chỉ tìm những phòng đang hoạt động bình thường
        AND p.MaPhong NOT IN (
            -- Truy vấn con: Tìm các phòng đã bị đặt trong khoảng thời gian này
            SELECT MaPhong 
            FROM don_dat_phong 
            WHERE TrangThaiDon != 'DaHuy' 
            AND (NgayNhan < :ngayTra AND NgayTra > :ngayNhan)
        )
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        // Gắn dữ liệu an toàn để chống SQL Injection
        $stmt->bindParam(':maKS', $maKS);
        $stmt->bindParam(':ngayNhan', $ngayNhan);
        $stmt->bindParam(':ngayTra', $ngayTra);
        
        $stmt->execute();
        
        // Trả về mảng chứa danh sách các phòng trống tìm được
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        echo "Lỗi thuật toán tìm phòng: " . $e->getMessage();
        return [];
    }
}
?>