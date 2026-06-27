// ── LUXURY HOTEL – Shared client-side JS ──
// File này chỉ chứa các helper UI thuần (toast, đếm sao...).
// Toàn bộ logic dữ liệu (đặt phòng, đăng  , CRUD) đã được xử lý
// ở phía Server bằng PHP + PDO, JS chỉ hỗ trợ trải nghiệm người dùng.

// Hiển thị thông báo dạng Toast (dùng Bootstrap Toast)
function showToast(message, type = 'default') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
  }
  const bgClass = { success: 'bg-success', error: 'bg-danger', default: 'bg-dark' }[type] || 'bg-dark';
  const toastEl = document.createElement('div');
  toastEl.className = `toast align-items-center text-white ${bgClass} border-0`;
  toastEl.setAttribute('role', 'alert');
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>`;
  container.appendChild(toastEl);
  const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
  toast.show();
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// Hiển thị thông báo lấy từ query string (?msg=...&type=...) sau khi redirect (PRG pattern)
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(location.search);
  const msg = params.get('msg');
  const type = params.get('type') || 'default';
  if (msg) showToast(decodeURIComponent(msg), type);
});

// Đếm sao cho form đánh giá (room-detail.php)
function setRatingStars(containerId, hiddenInputId, n) {
  document.getElementById(hiddenInputId).value = n;
  document.querySelectorAll(`#${containerId} span`).forEach((s, i) => {
    s.style.color = i < n ? 'var(--gold)' : '#E0DDD5';
  });
}

// Tính tổng tiền tạm tính ở trang chi tiết phòng (chỉ hiển thị UI,
// số liệu chính thức luôn được tính lại và xác thực ở phía Server)
function diffDays(d1, d2) {
  const t1 = new Date(d1), t2 = new Date(d2);
  if (isNaN(t1) || isNaN(t2)) return 0;
  return Math.max(0, Math.ceil((t2 - t1) / 86400000));
}
