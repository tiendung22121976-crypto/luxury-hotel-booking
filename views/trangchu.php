<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Hotel - Đặt Phòng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-blue border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">LUXURY HOTEL</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link border border-dark rounded px-3 mx-1" href="#">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border border-dark rounded px-3 mx-1" href="#">Đăng ký</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link border border-dark rounded px-3 mx-1" href="#">Đăng nhập</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="nav-link fs-4" href="#"><i class="fa-regular fa-circle-user"></i></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <h1 class="display-5 text-primary fw-bold">Welcome Luxury hotel</h1>
            <p class="text-muted">Khám phá không gian lưu trú sang trọng và đẳng cấp tại Luxury Hotel.<br>Đặt phòng ngay để nhận ưu đãi!</p>
        </div>
    </section>

    <div class="container position-relative z-1">
        <div class="search-box">
            <form action="search.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Địa điểm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="location" class="form-control" placeholder="Bạn muốn đi đâu?">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Ngày nhận - Ngày trả</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-regular fa-calendar"></i></span>
                        <input type="date" name="checkin" class="form-control">
                        <span class="input-group-text bg-white"><i class="fa-regular fa-calendar"></i></span>
                        <input type="date" name="checkout" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Khách</label> <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-regular fa-user"></i></span>
                        <input type="text" name="guests" class="form-control" placeholder="Số lượng khách">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-dark w-100 fw-bold">Tìm Kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container mt-5">
        
        <section class="mb-5">
            <h4 class="mb-4">Có Thể Bạn Quan Tâm?</h4>
            
            <div class="card mb-4 border border-dark rounded-0">
                <div class="row g-0">
                    <div class="col-md-4 img-placeholder" style="min-height: 210px;">
                        <img src="https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aG90ZWwlMjBzdWl0ZXxlbnwwfHwwfHx8MA%3D%3D" alt="Phòng Suite" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Luxury Hotel Đà Nẵng</small>
                                    <div class="text-dark">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i>
                                    </div>
                                </div>
                                <h5 class="card-title fw-bold">Phòng tổng thống(Suite)</h5>
                                <div class="mb-2 text-muted small">
                                    <span class="me-3"><i class="fa-solid fa-user-group"></i> 2 Người lớn</span>
                                    <span class="me-3"><i class="fa-solid fa-location-dot"></i> 60m2</span>
                                </div>
                                <div class="text-muted mb-3">
                                    <i class="fa-solid fa-tv me-2"></i>
                                    <i class="fa-solid fa-bed me-2"></i>
                                    <i class="fa-solid fa-wifi me-2"></i>
                                    <i class="fa-solid fa-bath"></i>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">9,000,000 VND/Đêm</h5>
                                <a href="#" class="btn btn-outline-dark rounded-0 px-4">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border border-dark rounded-0">
                <div class="row g-0">
                    <div class="col-md-4 img-placeholder" style="min-height: 210px;">
                        <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8aG90ZWwlMjBzdWl0ZXxlbnwwfHwwfHx8MA%3D%3D" alt="Phòng Deluxe" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Luxury Hotel Hà Nội</small>
                                    <div class="text-dark">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i>
                                    </div>
                                </div>
                                <h5 class="card-title fw-bold">Phòng Deluxe</h5>
                                <div class="mb-2 text-muted small">
                                    <span class="me-3"><i class="fa-solid fa-user-group"></i> 2 Người lớn</span>
                                    <span class="me-3"><i class="fa-solid fa-location-dot"></i> 35m2</span>
                                </div>
                                <div class="text-muted mb-3">
                                    <i class="fa-solid fa-tv me-2"></i>
                                    <i class="fa-solid fa-bed me-2"></i>
                                    <i class="fa-solid fa-wifi"></i>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">5,000,000 VND/Đêm</h5>
                                <a href="#" class="btn btn-outline-dark rounded-0 px-4">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h4 class="mb-2">Ưu đãi</h4>
            <p class="text-muted">Khuyến mãi giảm giá và ưu đãi đặc biệt dành riêng cho bạn. <a href="#" class="text-dark">Tìm hiểu ngay!</a></p>
        </section>

        <section class="mb-5">
            <h4 class="mb-3">Một Số Địa Điểm Nổi Tiếng</h4>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="img-placeholder w-100" style="height: 417px;">
                        <img src="https://plus.unsplash.com/premium_photo-1664366320037-6cad9e3c6e20?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTN8fHZpJUUxJUJCJTg3dCUyMG5hbXxlbnwwfHwwfHx8MA%3D%3D" alt="Địa điểm nổi tiếng" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                </div>
                <div class="col-md-3 d-flex flex-column gap-3">
                    <div class="img-placeholder w-100" style="height: 142px;">
                        <img src="https://images.unsplash.com/photo-1555921015-5532091f6026?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fHZpJUUxJUJCJTg3dCUyMG5hbXxlbnwwfHwwfHx8MA%3D%3D" alt="Địa điểm nổi tiếng" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                    <div class="img-placeholder w-100" style="height: 142px;">
                        <img src="https://plus.unsplash.com/premium_photo-1691960159290-6f4ace6e6c4c?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8aCVDMyVBMCUyMG4lRTElQkIlOTlpfGVufDB8fDB8fHww%3D%3D" alt="Địa điểm nổi tiếng" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="img-placeholder w-100" style="height: 417px;">
                        <img src="https://images.unsplash.com/photo-1582473788468-d25f5f398cce?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fCVDNCU5MSVDMyVBMCUyMG4lRTElQkElQjVuZ3xlbnwwfHwwfHx8MA%3D%3D" alt="Địa điểm nổi tiếng" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h4 class="mb-3">Tìm Kiếm Chỗ Nghỉ Tại Hà Nội</h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="img-placeholder w-100" style="height: 200px;">
                        <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MjB8fGhvdGVsfGVufDB8fDB8fHww%3D%3D" alt="Hà Nội" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="img-placeholder w-100" style="height: 200px;">
                        <img src="https://plus.unsplash.com/premium_photo-1661964402307-02267d1423f5?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8aG90ZWx8ZW58MHx8MHx8fDA%3D%3D" alt="Hà Nội" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="img-placeholder w-100" style="height: 200px;">
                        <img src="https://plus.unsplash.com/premium_photo-1676823553207-758c7a66e9bb?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8Y2hhbWJyZXxlbnwwfHwwfHx8MA%3D%3D" alt="Hà Nội" class="img-fluid h-100 w-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </section>

    </div> <footer class="bg-white border-top py-4 mt-5">
        <div class="container">
            <ul class="list-unstyled mb-0">
                <li class="mb-2"><a href="#" class="text-primary text-decoration-none">Tìm kiếm thông tin khách sạn</a></li>
                <li><a href="#" class="text-primary text-decoration-none">Hỗ trợ</a></li>
            </ul>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>