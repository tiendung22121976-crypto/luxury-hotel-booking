-- ============================================================
-- SEED DATA BỔ SUNG CHO DỰ ÁN LUXURY HOTEL
-- File này CHẠY SAU file luxuryhotel_webbooking.sql gốc.
-- Mục đích: cung cấp tài khoản Admin/Thành viên mẫu, đơn đặt phòng
-- mẫu và đánh giá mẫu để các trang PHP có dữ liệu hiển thị ngay
-- sau khi import vào XAMPP/phpMyAdmin.
-- ============================================================

-- Thêm chi nhánh TP.HCM để đủ 3 thành phố như giao diện gốc
INSERT INTO `khach_san` (`MaKS`, `TenKS`, `DiaChi`, `MoTa`, `HinhAnhDaiDien`) VALUES
('HCM01', 'Luxury Hotel Hồ Chí Minh', '789 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh', 'Khách sạn sang trọng giữa lòng phố thị, gần phố đi bộ Nguyễn Huệ', NULL);

-- Thêm vài phòng cho chi nhánh TP.HCM và bổ sung trạng thái đa dạng
INSERT INTO `phong` (`MaPhong`, `SoPhong`, `MaKS`, `MaLoai`, `TrangThai`) VALUES
('S101', '101', 'HCM01', 'STD', 'Available'),
('S201', '201', 'HCM01', 'DLX', 'Available'),
('S301', '301', 'HCM01', 'SUT', 'Available'),
('D201', '201', 'DN01', 'SUT', 'Available'),
('H301', '301', 'HN01', 'STD', 'Available');

-- Tài khoản Admin và 2 tài khoản thành viên mẫu
-- Mật khẩu Admin: Admin@123  | Mật khẩu thành viên: KhachHang123
-- (Mật khẩu lưu dạng plain-text, KHÔNG mã hóa)
INSERT INTO `tai_khoan` (`MaTK`, `HoTen`, `Email`, `SDT`, `MatKhau`, `VaiTro`) VALUES
(1, 'Quản Trị Viên', 'admin@luxuryhotel.vn', '0900000001', 'Admin@123', 'Admin'),
(2, 'Trần Văn An', 'tranvanan@gmail.com', '0912345678', 'KhachHang123', 'ThanhVien'),
(3, 'Nguyễn Thị Lan', 'nguyenthilan@gmail.com', '0987654321', 'KhachHang123', 'ThanhVien');

-- Đơn đặt phòng mẫu (1 đơn đã hoàn tất để có thể viết đánh giá,
-- 1 đơn đã xác nhận sắp tới, 1 đơn đã hủy)
INSERT INTO `don_dat_phong` (`MaDon`, `MaTK`, `MaPhong`, `NgayNhan`, `NgayTra`, `TongTien`, `MaKM`, `MaXacNhan`, `TrangThaiDon`, `NgayTao`) VALUES
(1, 2, 'H101', '2026-04-10', '2026-04-13', 3300000.00, NULL, 'LH-1001-HN', 'HoanTat', '2026-04-01 09:00:00'),
(2, 2, 'D201', '2026-07-20', '2026-07-23', 24300000.00, 'SUMMER26', 'AX-2002-DA', 'DaXacNhan', '2026-06-10 14:30:00'),
(3, 3, 'S201', '2026-05-05', '2026-05-07', 2200000.00, NULL, 'RX-3003-SG', 'DaHuy', '2026-04-20 11:15:00');

-- Đánh giá mẫu gắn với đơn đã hoàn tất (MaDon = 1)
INSERT INTO `danh_gia` (`MaDG`, `MaTK`, `MaDon`, `MucDo`, `BinhLuan`, `HinhAnh`, `NgayDanhGia`) VALUES
(1, 2, 1, 3, 'Phong rat sach se, nhan vien phuc vu nhiet tinh, se quay lai lan sau.', NULL, '2026-04-14 10:00:00');

-- Cập nhật trạng thái phòng tương ứng với các đơn đặt còn hiệu lực
UPDATE `phong` SET `TrangThai` = 'Reserved' WHERE `MaPhong` = 'D201';
