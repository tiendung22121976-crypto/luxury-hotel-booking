<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hoàn tất đặt phòng</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">

  <div class="container">
    <div class="card mx-auto shadow-sm" style="max-width: 560px;">
      <div class="card-body p-4">
        
        <form id="booking-form" action="#" method="POST" onsubmit="event.preventDefault(); alert('Đặt phòng thành công!');">
          
          <h2 class="h4 mb-2 fw-bold">Hoàn tất đặt phòng</h2>
          <p class="small text-secondary mb-4" id="booking-summary-text">
            Phòng Standard · Luxury Hotel Hà Nội · 8 đêm · Tổng: 22.000.000 VNĐ
          </p>

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="b-firstname" class="form-label fw-medium">Tên <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="b-firstname" name="firstname" placeholder="Nguyễn" required>
            </div>
            <div class="col-sm-6">
              <label for="b-lastname" class="form-label fw-medium">Họ <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="b-lastname" name="lastname" placeholder="Văn An" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="b-email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="b-email" name="email" placeholder="email@gmail.com" required>
            </div>
            <div class="col-sm-6">
              <label for="b-phone" class="form-label fw-medium">Số điện thoại <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" id="b-phone" name="phone" placeholder="0912-345-678" required pattern="[0-9]{10,11}">
            </div>
          </div>

          <div class="mb-4">
            <label for="b-note" class="form-label fw-medium">Yêu cầu đặc biệt (không bắt buộc)</label>
            <textarea class="form-control" id="b-note" name="note" rows="2" placeholder="Phòng không hút thuốc, view biển..."></textarea>
          </div>

          <h4 class="h6 fw-semibold text-primary mb-3">Phương thức thanh toán</h4>
          
          <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="payment_method" id="payment-card" value="card" checked>
            <label class="form-check-label fw-medium" for="payment-card">
              Thẻ tín dụng / Ghi nợ
            </label>
            
            <div class="row g-2 mt-2" id="card-details">
              <div class="col-12">
                <input type="text" class="form-control" id="b-cardnum" name="card_number" placeholder="Số thẻ" required>
              </div>
              <div class="col-6">
                <input type="text" class="form-control" id="b-expiry" name="card_expiry" placeholder="MM/YY" required>
              </div>
              <div class="col-6">
                <input type="text" class="form-control" id="b-cvc" name="card_cvc" placeholder="CVC" required>
              </div>
            </div>
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="radio" name="payment_method" id="payment-transfer" value="transfer">
            <label class="form-check-label fw-medium" for="payment-transfer">
              Chuyển khoản ngân hàng
            </label>
          </div>

          <div id="b-error" class="alert alert-danger d-none mt-3" role="alert"></div>

          <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary px-4">Hủy</button>
            <button type="submit" class="btn btn-warning px-4 fw-medium text-dark" id="confirm-btn">
              Xác nhận &amp; Thanh toán &rarr;
            </button>
          </div>

        </form>
        </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const radioCard = document.getElementById('payment-card');
    const radioTransfer = document.getElementById('payment-transfer');
    const cardDetails = document.getElementById('card-details');
    const cardInputs = cardDetails.querySelectorAll('input');

    function toggleCardDetails() {
      if (radioTransfer.checked) {
        cardDetails.classList.add('d-none');
        // Tắt bắt buộc nhập thẻ nếu chọn chuyển khoản
        cardInputs.forEach(input => input.removeAttribute('required'));
      } else {
        cardDetails.classList.remove('d-none');
        // Bật lại bắt buộc nhập thẻ nếu chọn thẻ
        cardInputs.forEach(input => input.setAttribute('required', 'true'));
      }
    }

    radioCard.addEventListener('change', toggleCardDetails);
    radioTransfer.addEventListener('change', toggleCardDetails);
  </script>
</body>
</html>