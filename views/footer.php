</div> <footer class="bg-navy text-white-50 py-5 mt-5 border-top border-warning">
    <div class="container text-center text-md-start">
        <div class="row py-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <h5 class="font-luxury text-gold fw-bold">LUXURY HOTEL CHAIN</h5>
                <p class="small text-muted mb-0">Hệ thống chuỗi nghỉ dưỡng và đặt phòng cao cấp chuẩn thượng lưu.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1">Hotline CSKH: <span class="text-white fw-bold">123-456-7899</span></p>
                <p class="small mb-0">Hỗ trợ kỹ thuật hệ thống 24/7</p>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="text-center small text-muted">© 2026 Luxury Hotel Network. Đồ án xây dựng Website Đặt Phòng Khách Sạn.</div>
    </div>
</footer>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="liveToast" class="toast align-items-center text-white border-0 bg-dark" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body id-toast-msg fw-medium">Thông báo hệ thống.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const msgEl = toastEl.querySelector('.id-toast-msg');
    toastEl.className = 'toast align-items-center text-white border-0 bg-' + (type === 'success' ? 'success' : (type === 'error' ? 'danger' : 'dark'));
    msgEl.textContent = message;
    new bootstrap.Toast(toastEl).show();
}
</script>
</body>
</html>