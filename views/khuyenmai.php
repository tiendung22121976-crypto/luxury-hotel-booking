<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khuyến Mãi - Luxury Hotel</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
            color: #0A2540 !important;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .table-custom {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table-custom thead th {
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #6c757d;
            letter-spacing: 1px;
            padding: 1rem;
        }

        .table-custom tbody tr {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .table-custom tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .table-custom tbody td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }

        .table-custom tbody td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .table-custom tbody td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .badge-discount {
            background: linear-gradient(45deg, #0A2540, #1A365D);
            padding: 0.5em 0.8em;
            border-radius: 20px;
            font-weight: 600;
            color: #D4AF37 !important;
        }

        .btn-gradient {
            background: linear-gradient(45deg, #D4AF37, #F3E5AB);
            color: #0A2540;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
            color: #0A2540;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #D4AF37;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: #0A2540;
        }

        /* Micro-animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fs-4" href="../index.php">LUXURY HOTEL ADMIN</a>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold text-dark" href="ctrl_khuyen_mai.php?action=list">
                            <i class="fa-solid fa-tags me-2" style="color: #D4AF37;"></i>Quản lý khuyến mãi
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5 fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark mb-0">
                <?= ($action == 'add') ? 'Thêm Khuyến Mãi Mới' : (($action == 'edit') ? 'Chỉnh Sửa Khuyến Mãi' : 'Danh Sách Khuyến Mãi') ?>
            </h2>
            <?php if ($action == 'list'): ?>
                <a href="ctrl_khuyen_mai.php?action=add" class="btn btn-gradient">
                    <i class="fa-solid fa-plus me-2"></i>Thêm Mới
                </a>
            <?php else: ?>
                <a href="ctrl_khuyen_mai.php?action=list" class="btn btn-outline-secondary rounded-3">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
            <?php endif; ?>
        </div>

        <?php if ($action == 'list'): ?>
            <div class="glass-card p-4">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Mã Code</th>
                                <th>Tên Chương Trình</th>
                                <th>% Giảm</th>
                                <th>Ngày Bắt Đầu</th>
                                <th>Ngày Kết Thúc</th>
                                <th class="text-end">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($danhSachKM)): ?>
                                <?php foreach ($danhSachKM as $km): ?>
                                    <tr>
                                        <td class="fw-bold" style="color: #0A2540;">#<?= htmlspecialchars($km['MaKM']) ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($km['TenKM']) ?></td>
                                        <td>
                                            <span class="badge badge-discount">
                                                <i class="fa-solid fa-arrow-down me-1"></i><?= htmlspecialchars($km['PhanTramGiam']) ?>%
                                            </span>
                                        </td>
                                        <td class="text-muted"><i class="fa-regular fa-calendar me-2"></i><?= date('d/m/Y', strtotime($km['NgayBatDau'])) ?></td>
                                        <td class="text-muted"><i class="fa-regular fa-calendar-check me-2"></i><?= date('d/m/Y', strtotime($km['NgayKetThuc'])) ?></td>
                                        <td class="text-end">
                                            <a href="ctrl_khuyen_mai.php?action=edit&maKM=<?= urlencode($km['MaKM']) ?>" class="btn btn-sm btn-light text-primary rounded-circle shadow-sm me-2" title="Chỉnh sửa">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="ctrl_khuyen_mai.php?action=delete&maKM=<?= urlencode($km['MaKM']) ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?');" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fs-1 mb-3 d-block text-light"></i>
                                        Chưa có chương trình khuyến mãi nào.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($action == 'add' || $action == 'edit'): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="glass-card p-5">
                        <form action="ctrl_khuyen_mai.php?action=<?= $action ?>" method="POST">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                                    <input type="text" name="maKM" class="form-control" required placeholder="Ví dụ: SUMMER24"
                                        value="<?= isset($khuyenMai['MaKM']) ? htmlspecialchars($khuyenMai['MaKM']) : '' ?>"
                                        <?= ($action == 'edit') ? 'readonly' : '' ?>>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tên Chương Trình <span class="text-danger">*</span></label>
                                    <input type="text" name="tenKM" class="form-control" required placeholder="Ví dụ: Chào Hè Sôi Động"
                                        value="<?= isset($khuyenMai['TenKM']) ? htmlspecialchars($khuyenMai['TenKM']) : '' ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">% Giảm Giá (1 - 100) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="phanTram" class="form-control" required min="1" max="100" placeholder="Ví dụ: 15"
                                            value="<?= isset($khuyenMai['PhanTramGiam']) ? htmlspecialchars($khuyenMai['PhanTramGiam']) : '' ?>">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-percent"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ngày Bắt Đầu <span class="text-danger">*</span></label>
                                    <input type="date" name="ngayBD" class="form-control" required
                                        value="<?= isset($khuyenMai['NgayBatDau']) ? htmlspecialchars($khuyenMai['NgayBatDau']) : '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ngày Kết Thúc <span class="text-danger">*</span></label>
                                    <input type="date" name="ngayKT" class="form-control" required
                                        value="<?= isset($khuyenMai['NgayKetThuc']) ? htmlspecialchars($khuyenMai['NgayKetThuc']) : '' ?>">
                                </div>
                                <div class="col-12 mt-5 text-end">
                                    <button type="submit" class="btn btn-gradient px-5 py-2 fs-5">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu Lại
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
