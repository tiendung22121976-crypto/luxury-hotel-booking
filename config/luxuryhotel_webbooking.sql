-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3308
-- Thời gian đã tạo: Th6 16, 2026 lúc 05:29 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `luxuryhotel_webbooking`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_gia`
--

CREATE TABLE `danh_gia` (
  `MaDG` int(11) NOT NULL,
  `MaTK` int(11) DEFAULT NULL,
  `MaDon` int(11) DEFAULT NULL,
  `MucDo` tinyint(4) DEFAULT NULL CHECK (`MucDo` between 1 and 3),
  `BinhLuan` text DEFAULT NULL,
  `HinhAnh` varchar(255) DEFAULT NULL,
  `NgayDanhGia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_dat_phong`
--

CREATE TABLE `don_dat_phong` (
  `MaDon` int(11) NOT NULL,
  `MaTK` int(11) DEFAULT NULL,
  `MaPhong` varchar(10) DEFAULT NULL,
  `NgayNhan` date NOT NULL,
  `NgayTra` date NOT NULL,
  `TongTien` decimal(15,2) DEFAULT NULL,
  `MaKM` varchar(10) DEFAULT NULL,
  `MaXacNhan` varchar(20) NOT NULL,
  `TrangThaiDon` enum('ChoXacNhan','DaXacNhan','DaHuy','HoanTat') DEFAULT 'ChoXacNhan',
  `NgayTao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khach_san`
--

CREATE TABLE `khach_san` (
  `MaKS` varchar(10) NOT NULL,
  `TenKS` varchar(100) NOT NULL,
  `DiaChi` varchar(255) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `HinhAnhDaiDien` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khach_san`
--

INSERT INTO `khach_san` (`MaKS`, `TenKS`, `DiaChi`, `MoTa`, `HinhAnhDaiDien`) VALUES
('DN01', 'Luxury Hotel Đà Nẵng', '456 Bạch Đằng, Đà Nẵng', 'Khách sạn view sông Hàn tuyệt đẹp', NULL),
('HN01', 'Luxury Hotel Hà Nội ', '123 Cầu Giấy, Hà Nội', 'Khách sạn trung tâm thủ đô, tiện lợi di chuyển', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `MaKM` varchar(10) NOT NULL,
  `TenKM` varchar(100) NOT NULL,
  `PhanTramGiam` int(11) DEFAULT NULL CHECK (`PhanTramGiam` >= 0 and `PhanTramGiam` <= 100),
  `NgayBatDau` date DEFAULT NULL,
  `NgayKetThuc` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khuyen_mai`
--

INSERT INTO `khuyen_mai` (`MaKM`, `TenKM`, `PhanTramGiam`, `NgayBatDau`, `NgayKetThuc`) VALUES
('OPENING', 'Khai Trương Luxury', 20, '2026-11-01', '2026-12-31'),
('SUMMER26', 'Chào Hè 2026', 10, '2026-06-01', '2026-06-15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loai_phong`
--

CREATE TABLE `loai_phong` (
  `MaLoai` varchar(10) NOT NULL,
  `TenLoai` varchar(50) NOT NULL,
  `DonGia` decimal(15,2) NOT NULL,
  `DienTich` float DEFAULT NULL,
  `TienIch` text DEFAULT NULL,
  `MoTa` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `loai_phong`
--

INSERT INTO `loai_phong` (`MaLoai`, `TenLoai`, `DonGia`, `DienTich`, `TienIch`, `MoTa`) VALUES
('STD', 'Standard', 1000000.00, 25, 'TV, Điều hòa, Wifi miễn phí', 'Phòng tiêu chuẩn dành cho 2 người'),
('DLX', 'Deluxe', 2000000.00, 40, 'TV, Mini-bar, Điều hòa, Bồn tắm', 'Phòng cao cấp với không gian rộng rãi'),
('SUT', 'Suite', 9000000.00, 60, 'TV, Mini-bar, Điều hòa, Bồn tắm, Sofa, Bàn làm việc', 'Phòng tổng thống đẳng cấp 5 sao');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phong`
--

CREATE TABLE `phong` (
  `MaPhong` varchar(10) NOT NULL,
  `SoPhong` varchar(10) NOT NULL,
  `MaKS` varchar(10) DEFAULT NULL,
  `MaLoai` varchar(10) DEFAULT NULL,
  `TrangThai` enum('Available','Reserved','Occupied','Cleaning') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phong`
--

INSERT INTO `phong` (`MaPhong`, `SoPhong`, `MaKS`, `MaLoai`, `TrangThai`) VALUES
('D101', '101', 'DN01', 'STD', 'Available'),
('D102', '102', 'DN01', 'DLX', 'Cleaning'),
('H101', '101', 'HN01', 'DLX', 'Available'),
('H102', '102', 'HN01', 'DLX', 'Reserved'),
('H201', '201', 'HN01', 'SUT', 'Occupied');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `MaTK` int(11) NOT NULL,
  `HoTen` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `SDT` varchar(15) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `VaiTro` enum('Admin','ThanhVien') DEFAULT 'ThanhVien'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`MaDG`),
  ADD UNIQUE KEY `MaDon` (`MaDon`),
  ADD KEY `FK_DanhGia_TaiKhoan` (`MaTK`);

--
-- Chỉ mục cho bảng `don_dat_phong`
--
ALTER TABLE `don_dat_phong`
  ADD PRIMARY KEY (`MaDon`),
  ADD UNIQUE KEY `MaXacNhan` (`MaXacNhan`),
  ADD KEY `FK_DonDat_TaiKhoan` (`MaTK`),
  ADD KEY `FK_DonDat_Phong` (`MaPhong`),
  ADD KEY `FK_DonDat_KhuyenMai` (`MaKM`);

--
-- Chỉ mục cho bảng `khach_san`
--
ALTER TABLE `khach_san`
  ADD PRIMARY KEY (`MaKS`);

--
-- Chỉ mục cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  ADD PRIMARY KEY (`MaKM`);

--
-- Chỉ mục cho bảng `loai_phong`
--
ALTER TABLE `loai_phong`
  ADD PRIMARY KEY (`MaLoai`);

--
-- Chỉ mục cho bảng `phong`
--
ALTER TABLE `phong`
  ADD PRIMARY KEY (`MaPhong`),
  ADD KEY `FK_Phong_KhachSan` (`MaKS`),
  ADD KEY `FK_Phong_LoaiPhong` (`MaLoai`);

--
-- Chỉ mục cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`MaTK`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `SDT` (`SDT`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `MaDG` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `don_dat_phong`
--
ALTER TABLE `don_dat_phong`
  MODIFY `MaDon` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `MaTK` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD CONSTRAINT `FK_DanhGia_DonDat` FOREIGN KEY (`MaDon`) REFERENCES `don_dat_phong` (`MaDon`),
  ADD CONSTRAINT `FK_DanhGia_TaiKhoan` FOREIGN KEY (`MaTK`) REFERENCES `tai_khoan` (`MaTK`);

--
-- Các ràng buộc cho bảng `don_dat_phong`
--
ALTER TABLE `don_dat_phong`
  ADD CONSTRAINT `FK_DonDat_KhuyenMai` FOREIGN KEY (`MaKM`) REFERENCES `khuyen_mai` (`MaKM`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_DonDat_Phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`),
  ADD CONSTRAINT `FK_DonDat_TaiKhoan` FOREIGN KEY (`MaTK`) REFERENCES `tai_khoan` (`MaTK`);

--
-- Các ràng buộc cho bảng `phong`
--
ALTER TABLE `phong`
  ADD CONSTRAINT `FK_Phong_KhachSan` FOREIGN KEY (`MaKS`) REFERENCES `khach_san` (`MaKS`),
  ADD CONSTRAINT `FK_Phong_LoaiPhong` FOREIGN KEY (`MaLoai`) REFERENCES `loai_phong` (`MaLoai`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
