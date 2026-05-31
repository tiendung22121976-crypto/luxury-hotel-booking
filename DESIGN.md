---
version: alpha
name: Phân-tích-thiết-kế-Airbnb
description: Một nền tảng thị trường tiêu dùng ấm áp, hào phóng, lấy nền tảng là một giao diện trắng tinh khôi và màu Airbnb Rausch (#ff385c) - nguồn năng lượng thương hiệu duy nhất dẫn dắt mọi Lời kêu gọi hành động (CTA) chính, nút tìm kiếm hình cầu và dấu chấm đánh giá. Phông chữ sử dụng Airbnb Cereal VF với độ đậm vừa phải — chữ cỡ lớn (display) nằm ở mức 22–28px với độ đậm 500/600 thay vì mức 700+ nặng nề mà các hệ thống tài chính và doanh nghiệp hay dùng; thương hiệu này ưu tiên sử dụng nhiếp ảnh và khoảng trắng rộng rãi hơn là sức mạnh của kiểu chữ. Ba mục sản phẩm (Chỗ ở, Trải nghiệm, Dịch vụ) nằm trên thanh điều hướng trên cùng với các biểu tượng 32px vẽ tay và huy hiệu "NEW", báo hiệu sự mở rộng thị trường thay vì nhồi nhét tính năng. Các thanh tìm kiếm hình viên thuốc (`{rounded.full}`), thẻ bất động sản bo tròn mềm mại (`{rounded.lg}` ~14px) và bán kính nút 32px tạo cảm giác thân thiện và mang tính con người — không có bất kỳ góc cạnh sắc nhọn nào ngoại trừ hệ thống lưới (grid) của phần thân trang.

colors:
  primary: "#ff385c"
  primary-active: "#e00b41"
  primary-disabled: "#ffd1da"
  primary-error-text: "#c13515"
  primary-error-text-hover: "#b32505"
  luxe: "#460479"
  plus: "#92174d"
  ink: "#222222"
  body: "#3f3f3f"
  muted: "#6a6a6a"
  muted-soft: "#929292"
  hairline: "#dddddd"
  hairline-soft: "#ebebeb"
  border-strong: "#c1c1c1"
  canvas: "#ffffff"
  surface-soft: "#f7f7f7"
  surface-card: "#ffffff"
  surface-strong: "#f2f2f2"
  on-primary: "#ffffff"
  on-dark: "#ffffff"
  legal-link: "#428bff"
  star-rating: "#222222"
  scrim: "#000000"

typography:
  display-xl:
    fontFamily: "'Airbnb Cereal VF', Circular, -apple-system, system-ui, Roboto, 'Helvetica Neue', sans-serif"
    fontSize: 28px
    fontWeight: 700
    lineHeight: 1.43
    letterSpacing: 0
  display-lg:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 22px
    fontWeight: 500
    lineHeight: 1.18
    letterSpacing: -0.44px
  display-md:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 21px
    fontWeight: 700
    lineHeight: 1.43
    letterSpacing: 0
  display-sm:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 20px
    fontWeight: 600
    lineHeight: 1.20
    letterSpacing: -0.18px
  title-md:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 16px
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: 0
  title-sm:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 16px
    fontWeight: 500
    lineHeight: 1.25
    letterSpacing: 0
  rating-display:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 64px
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: -1px
  body-md:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: 0
  body-sm:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.43
    letterSpacing: 0
  caption:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1.29
    letterSpacing: 0
  caption-sm:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.23
    letterSpacing: 0
  badge:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 11px
    fontWeight: 600
    lineHeight: 1.18
    letterSpacing: 0
  micro-label:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 12px
    fontWeight: 700
    lineHeight: 1.33
    letterSpacing: 0
  uppercase-tag:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 8px
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: 0.32px
    textTransform: uppercase
  button-md:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 16px
    fontWeight: 500
    lineHeight: 1.25
    letterSpacing: 0
  button-sm:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1.29
    letterSpacing: 0
  link:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.43
    letterSpacing: 0
  nav-link:
    fontFamily: "'Airbnb Cereal VF', Circular, sans-serif"
    fontSize: 16px
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: 0

rounded:
  none: 0px
  xs: 4px
  sm: 8px
  md: 14px
  lg: 20px
  xl: 32px
  full: 9999px

spacing:
  xxs: 2px
  xs: 4px
  sm: 8px
  md: 12px
  base: 16px
  lg: 24px
  xl: 32px
  xxl: 48px
  section: 64px

components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button-md}"
    rounded: "{rounded.sm}"
    padding: 14px 24px
    height: 48px
  button-primary-active:
    backgroundColor: "{colors.primary-active}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.sm}"
  button-primary-disabled:
    backgroundColor: "{colors.primary-disabled}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.sm}"
  button-secondary:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.button-md}"
    rounded: "{rounded.sm}"
    padding: 13px 23px
    height: 48px
  button-tertiary-text:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.button-md}"
  button-pill-rausch:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button-sm}"
    rounded: "{rounded.full}"
    padding: 10px 20px
  search-orb:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.full}"
    height: 48px
  icon-button-circle:
    backgroundColor: "{colors.surface-strong}"
    textColor: "{colors.ink}"
    rounded: "{rounded.full}"
    height: 32px
  icon-button-outline:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    rounded: "{rounded.full}"
    height: 40px
  top-nav:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.nav-link}"
    height: 80px
  product-tab-active:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.nav-link}"
    rounded: "{rounded.none}"
  product-tab-inactive:
    backgroundColor: transparent
    textColor: "{colors.muted}"
    typography: "{typography.nav-link}"
  search-bar-pill:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.body-sm}"
    rounded: "{rounded.full}"
    padding: 14px 24px
    height: 64px
  search-field-segment:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.caption}"
    padding: 8px 24px
  category-strip:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.muted}"
    typography: "{typography.button-sm}"
  category-tab-active:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.button-sm}"
    rounded: "{rounded.none}"
  property-card:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.body-sm}"
    rounded: "{rounded.md}"
  property-card-photo:
    rounded: "{rounded.md}"
  experience-card:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.title-md}"
    rounded: "{rounded.md}"
  city-link-block:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.title-sm}"
  rating-display-card:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.rating-display}"
  guest-favorite-badge:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.badge}"
    rounded: "{rounded.full}"
    padding: 4px 10px
  new-tag:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.uppercase-tag}"
    rounded: "{rounded.full}"
    padding: 2px 6px
  amenity-row:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.body-md}"
    padding: 12px 0
  reviews-card:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.body-sm}"
  host-card:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.body-sm}"
    rounded: "{rounded.md}"
    padding: 24px
  reservation-card:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.body-md}"
    rounded: "{rounded.md}"
    padding: 24px
  date-picker-day:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.body-sm}"
    rounded: "{rounded.full}"
  date-picker-day-selected:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.on-dark}"
    rounded: "{rounded.full}"
  text-input:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.body-md}"
    rounded: "{rounded.sm}"
    padding: 14px 12px
    height: 56px
  footer-light:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.body-sm}"
    padding: 48px 80px
  footer-link:
    backgroundColor: transparent
    textColor: "{colors.ink}"
    typography: "{typography.body-sm}"
  legal-band:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.muted}"
    typography: "{typography.caption-sm}"
---

## Tổng quan

Airbnb là ví dụ kinh điển về một thị trường tiêu dùng hào phóng, lấy nhiếp ảnh làm chủ đạo. Nền cơ bản là **trắng tinh** (`{colors.canvas}` — #ffffff) với màu mực đen sâu (`{colors.ink}` — #222222) cho tiêu đề và nội dung, cùng một điểm nhấn duy nhất là màu **Rausch** (`{colors.primary}` — #ff385c) đảm nhận mọi CTA chính, nút tìm kiếm hình cầu, trạng thái lưu (thả tim) và các liên kết thương hiệu nội tuyến (inline). Không có màu thương hiệu phụ nào trong các chiến dịch tiếp thị chính — các mã màu **Tím Luxe** (`{colors.luxe}` — #460479) và **Đỏ tươi Plus** (`{colors.plus}` — #92174d) chỉ là các điểm nhấn thương hiệu phụ xuất hiện trong bối cảnh của các sản phẩm Airbnb Luxe / Plus.

Kiểu chữ chạy trên nền **Airbnb Cereal VF** (một phông chữ biến đổi tùy chỉnh mà Airbnb mua bản quyền), với **Circular** là phông chữ dự phòng nội bộ mang tính lịch sử và hệ thống phông chữ mặc định lót bên dưới. Cereal nằm ở mức độ đậm vừa phải — các tiêu đề lớn hiển thị ở mức 22–28px, độ đậm 500–600, thay vì các mức 700+ nặng nề mà các hệ thống tài chính hoặc doanh nghiệp thường dùng. Tiêu đề chính h1 ("Nguồn cảm hứng cho những kỳ nghỉ tương lai") trên trang chủ chỉ ở mức 28px / 700, mức này có thể tạo cảm giác quá nhỏ trên một trang SaaS thông thường; nhưng ở đây nó hoạt động tốt vì bố cục dựa vào nhiếp ảnh (ảnh ghép thành phố, thẻ bất động sản) để tạo sức nặng thị giác thay vì kiểu chữ.

Ngôn ngữ hình khối mang tính **mềm mại**. Các nút có bán kính 8px (`{rounded.sm}`), thẻ bất động sản là ~14px (`{rounded.md}`), thanh tìm kiếm có hình viên thuốc hoàn toàn (`{rounded.full}`), trái tim yêu thích và quả cầu tìm kiếm là hình tròn (`{rounded.full}`), và các góc của dải danh mục được bo ở mức 32px (`{rounded.xl}`). Gần như không có bất kỳ góc nhọn nào ở bất cứ đâu ngoại trừ bản thân lưới nội dung — mọi yếu tố tương tác đều được bo tròn.

**Đặc điểm chính:**
*   **Màu nhấn duy nhất:** `{colors.primary}` (#ff385c — "Rausch") mang mọi CTA chính, quả cầu tìm kiếm, trạng thái thả tim và logo chữ của thương hiệu. Được sử dụng rất tiết kiệm — hầu hết các trang là 90% màu trắng + màu mực với một hoặc hai điểm nhấn Rausch.
*   **Kiểu chữ biến đổi tùy chỉnh:** `Airbnb Cereal VF`. Độ đậm của tiêu đề lớn nằm ở mức 500–700, nội dung thường là 400. Độ đậm tinh tế này là có chủ ý — hệ thống tin tưởng vào sức nặng thị giác của hình ảnh.
*   **Điều hướng 3 sản phẩm:** Chỗ ở (Homes), Trải nghiệm (Experiences), Dịch vụ (Services) — mỗi mục có một biểu tượng 32px vẽ tay và huy hiệu "NEW" (`{component.new-tag}`) trên hai sản phẩm mới hơn. Tab đang hoạt động sử dụng đường gạch chân (`{component.product-tab-active}`).
*   **Thanh tìm kiếm toàn cầu hình viên thuốc:** Bề mặt trắng, bo tròn hoàn toàn (`{rounded.full}`), được chia bằng các đường viền mảnh (hairline) 1px thành các phân đoạn Ở đâu / Khi nào / Ai, và kết thúc bằng một quả cầu tìm kiếm Rausch hình tròn (`{component.search-orb}`).
*   **Thẻ bất động sản ưu tiên hình ảnh:** Hình chữ nhật có tỷ lệ khung hình với phần cắt góc `{rounded.md}`, băng chuyền hình ảnh có thể vuốt, huy hiệu "Khách được yêu thích" nổi ở trên cùng bên trái, biểu tượng trái tim ở trên cùng bên phải, sau đó là 4–5 dòng thông tin meta bên dưới.
*   **Dropdown mang tính biên tập:** (chân trang, bộ chọn ngôn ngữ) là các cột văn bản gọn gàng trên nền trắng — không có thẻ nền, không có bóng đổ.
*   **Hệ thống bóng đổ (Elevation):** Giới hạn ở một cấp độ đổ bóng duy nhất (`box-shadow: rgba(0,0,0,0.02) 0 0 0 1px, rgba(0,0,0,0.04) 0 2px 6px, rgba(0,0,0,0.1) 0 4px 8px`) — được sử dụng cho các thẻ nổi lên khi di chuột (hover) và các menu thả xuống của tìm kiếm/tài khoản.
*   **Hệ thống khoảng cách:** Dựa trên bội số 8px, với các phần chính ở `{spacing.section}` (64px) — rộng rãi nhưng không quá thoáng đến mức giống tạp chí; mật độ của thị trường cần hiển thị nhiều thẻ hơn trong mỗi lần cuộn.

---

## Màu sắc

### Thương hiệu & Điểm nhấn
*   **Rausch** (`{colors.primary}` — #ff385c): Màu thương hiệu duy nhất. Được sử dụng cho hình nền CTA chính (Đặt phòng, Tiếp tục), quả cầu tìm kiếm, trạng thái lưu hình trái tim và các liên kết thương hiệu nội tuyến. Đây là màu dễ nhận biết nhất trong ngành du lịch tiêu dùng.
*   **Rausch Active** (`{colors.primary-active}` — #e00b41): Biến thể khi nhấn / giữ chuột — bão hòa hơn một chút. Được dùng trên `{component.button-primary-active}`.
*   **Rausch Disabled** (`{colors.primary-disabled}` — #ffd1da): Tông màu nhạt được dùng cho các CTA bị vô hiệu hóa.
*   **Tím Luxe** (`{colors.luxe}` — #460479): Điểm nhấn cho thương hiệu phụ Airbnb Luxe. Chỉ xuất hiện bên trong các giao diện mang thương hiệu Luxe — không bao giờ dùng trong tiếp thị dòng chính.
*   **Đỏ tươi Plus** (`{colors.plus}` — #92174d): Điểm nhấn cho thương hiệu phụ Airbnb Plus. Phạm vi tương tự như Luxe — chỉ dành cho sản phẩm phụ.

### Bề mặt (Surface)
*   **Canvas** (`{colors.canvas}` — #ffffff): Nền tảng mặc định cho mọi trang công khai. Airbnb không có chế độ nền tối (dark mode) trên web công khai.
*   **Surface Soft** (`{colors.surface-soft}` — #f7f7f7): Lớp phủ sáng nhất — được sử dụng trên các trường bị vô hiệu hóa, nền rê chuột của menu phụ và dải bộ lọc tìm kiếm nội tuyến.
*   **Surface Strong** (`{colors.surface-strong}` — #f2f2f2): Lớp phủ đậm hơn một chút — dùng cho bề mặt nút biểu tượng tròn (ví dụ: mũi tên quay lại breadcrumb và các nút trên thanh công cụ danh sách).

### Đường viền (Hairlines & Borders)
*   **Hairline** (`{colors.hairline}` — #dddddd): Tông màu viền 1px mặc định — dải phân cách thanh tìm kiếm, bộ phân tách bảng, cột chia chân trang, đường viền thẻ 1px.
*   **Hairline Soft** (`{colors.hairline-soft}` — #ebebeb): Một dải phân cách nhạt hơn được sử dụng trên các bộ phân tách nội dung cuộn dài.
*   **Border Strong** (`{colors.border-strong}` — #c1c1c1): Đường viền nét dày hơn được sử dụng trên các nút phác thảo bị vô hiệu hóa và viền ngoài của form nhập liệu sau khi được chọn (focus).

### Văn bản (Text)
*   **Mực (Ink)** (`{colors.ink}` — #222222): Màu văn bản chủ đạo trên nền sáng. Dành cho tiêu đề lớn, đoạn văn bản, liên kết điều hướng chính và hầu hết văn bản liên kết nội tuyến. Không bao giờ là màu đen tuyền.
*   **Nội dung (Body)** (`{colors.body}` — #3f3f3f): Màu văn bản chạy phụ trợ được sử dụng bên trong các đoạn đánh giá dài và văn bản tiện nghi nơi màu "Ink" sẽ tạo cảm giác quá nặng nề.
*   **Mờ (Muted)** (`{colors.muted}` — #6a6a6a): Tiêu đề phụ bên trong các khối liên kết thành phố ("Thuê biệt thự nghỉ dưỡng", "Thuê villa"), nhãn tab sản phẩm không hoạt động, nhãn phụ danh mục chân trang, liên kết "Xem tất cả".
*   **Mờ nhẹ (Muted Soft)** (`{colors.muted-soft}` — #929292): Chữ của liên kết bị vô hiệu hóa. Được sử dụng rất hạn chế.
*   **Đánh giá Sao (Star Rating)** (`{colors.star-rating}` — #222222): Sử dụng cùng mã màu Ink — biểu tượng ngôi sao của Airbnb và con số đánh giá "4.81" đều được hiển thị bằng màu mực đen thay vì màu vàng/vàng kim, đây là một lựa chọn thương hiệu có chủ ý (ngôi sao màu vàng mang lại cảm giác rẻ tiền trong bối cảnh du lịch).
*   **Trên nền màu chính (On Primary)** (`{colors.on-primary}` — #ffffff): Chữ trắng trên nền CTA màu Rausch.

### Ngữ nghĩa (Semantic)
*   **Lỗi (Error)** (`{colors.primary-error-text}` — #c13515): Lỗi văn bản nội tuyến khi xác thực form. Khác biệt với Rausch — đỏ đậm hơn, bão hòa hơn một chút.
*   **Lỗi khi rê chuột (Error Hover)** (`{colors.primary-error-text-hover}` — #b32505): Tối hơn khi rê chuột qua liên kết.
*   **Xanh liên kết pháp lý (Legal Link Blue)** (`{colors.legal-link}` — #428bff): Các liên kết nội tuyến bên trong bản sao pháp lý (Quyền riêng tư, Điều khoản). Chỉ được sử dụng trong tiểu vùng pháp lý.

### Màn che (Scrim)
*   **Scrim** (`{colors.scrim}` — #000000 ở độ mờ 50%): Tông màu phông nền modal (hộp thoại) toàn cục — dùng cho bộ chọn ngày, hộp thoại đăng nhập, bộ chọn ngôn ngữ. Được lưu dưới dạng mã hex cơ sở; độ trong suốt (opacity) được áp dụng tại thời điểm kết xuất (render).

---

## Kiểu chữ (Typography)

### Phông chữ
Hệ thống chạy **Airbnb Cereal VF** cho mọi thứ — tiêu đề lớn, nội dung, điều hướng, chú thích, văn bản siêu nhỏ (microcopy). Hệ thống dự phòng chạy theo thứ tự `Circular, -apple-system, system-ui, Roboto, "Helvetica Neue", sans-serif`. **Circular** là kiểu chữ nội bộ lịch sử vẫn được giữ lại làm dự phòng phi biến đổi đầu tiên; các hệ thống xếp chồng phông chữ sẽ hỗ trợ phía sau.

Không có bộ phông chữ lớn riêng biệt. Phông chữ biến đổi (variable font) này đảm nhiệm toàn bộ quy mô.

### Phân cấp

| Token (Biến) | Kích thước | Độ đậm | Chiều cao dòng | Khoảng cách chữ | Sử dụng |
|---|---|---|---|---|---|
| `{typography.rating-display}` | 64px | 700 | 1.1 | -1px | Số điểm đánh giá chi tiết ("4.81") |
| `{typography.display-xl}` | 28px | 700 | 1.43 | 0 | Thẻ h1 trang chủ ("Nguồn cảm hứng cho những kỳ nghỉ tương lai") |
| `{typography.display-lg}` | 22px | 500 | 1.18 | -0.44px | Thẻ h1 chi tiết danh sách ("Gần Bãi biển Fethiye Aliyah Bali…") |
| `{typography.display-md}` | 21px | 700 | 1.43 | 0 | Tiêu đề phần bên trong chi tiết danh sách ("Nơi này có những gì") |
| `{typography.display-sm}` | 20px | 600 | 1.20 | -0.18px | Tiêu đề phụ ("Những điều cần biết") |
| `{typography.title-md}` | 16px | 600 | 1.25 | 0 | Tiêu đề khối liên kết thành phố ("Wilmington", "Athens") |
| `{typography.title-sm}` | 16px | 500 | 1.25 | 0 | Tiêu đề cột chân trang ("Hỗ trợ", "Đón tiếp khách", "Airbnb") |
| `{typography.body-md}` | 16px | 400 | 1.5 | 0 | Văn bản chạy mặc định bên trong mô tả chỗ ở |
| `{typography.body-sm}` | 14px | 400 | 1.43 | 0 | Dòng meta của thẻ, ngày tháng, giá cả, văn bản khoảng cách |
| `{typography.caption}` | 14px | 500 | 1.29 | 0 | Nhãn phân đoạn trường tìm kiếm ("Ở đâu", "Khi nào", "Ai") |
| `{typography.caption-sm}` | 13px | 400 | 1.23 | 0 | Dòng pháp lý chân trang ("© 2026 Airbnb, Inc.") |
| `{typography.badge}` | 11px | 600 | 1.18 | 0 | Chữ huy hiệu nổi "Khách được yêu thích" |
| `{typography.micro-label}` | 12px | 700 | 1.33 | 0 | Nhãn vi mô tiện nghi của thẻ ("Động cơ I6") |
| `{typography.uppercase-tag}` | 8px | 700 | 1.25 | 0.32px (Viết hoa) | Huy hiệu "NEW" trên các tab điều hướng sản phẩm |
| `{typography.button-md}` | 16px | 500 | 1.25 | 0 | Nhãn nút CTA chính |
| `{typography.button-sm}` | 14px | 500 | 1.29 | 0 | Nhãn nút hình viên thuốc (dải danh mục) |
| `{typography.link}` | 14px | 400 | 1.43 | 0 | Liên kết thân nội tuyến |
| `{typography.nav-link}` | 16px | 600 | 1.25 | 0 | Nhãn điều hướng sản phẩm hàng đầu (Chỗ ở, Trải nghiệm, Dịch vụ) |

### Nguyên tắc
Trọng lượng chữ lớn (display) được giữ ở mức vừa phải. Thẻ h1 trên trang chủ ở mức 28px / 700 là nhỏ một cách có chủ ý — nó nép dưới thanh tìm kiếm để nhiếp ảnh và lưới liên kết thành phố gánh vác hệ thống phân cấp thị giác. Thẻ h1 chi tiết ở mức 22px / 500 thậm chí còn tĩnh lặng hơn; banner ảnh của chỗ ở sẽ làm phần việc nổi bật ở phía trên nó.

Khoảnh khắc kiểu chữ "ồn ào" duy nhất trong toàn bộ hệ thống là **hiển thị đánh giá** (`{typography.rating-display}` — 64px / 700) trên các trang thông tin chỗ ở. Đó là nơi duy nhất hệ thống tin tưởng kiểu chữ đơn thuần để mang hệ thống phân cấp — con số đánh giá là tín hiệu niềm tin đỉnh cao, vì vậy chúng nhận được sự ưu tiên lớn nhất.

### Lưu ý về Phông chữ Thay thế
Nếu Airbnb Cereal VF và Circular không khả dụng, **Inter** là phông chữ nguồn mở thay thế gần nhất. Hãy điều chỉnh tiêu đề lớn xuống khoảng ~2% chiều cao dòng (line-height) để khớp với chiều cao (cap height) hơi khít hơn của Cereal; nếu không, tỷ lệ chuyển đổi khá gọn gàng.

---

## Bố cục (Layout)

### Hệ thống Khoảng cách (Spacing System)
*   **Đơn vị cơ sở:** 4px (với bước nhảy vi mô 2px).
*   **Tokens:** `{spacing.xxs}` 2px · `{spacing.xs}` 4px · `{spacing.sm}` 8px · `{spacing.md}` 12px · `{spacing.base}` 16px · `{spacing.lg}` 24px · `{spacing.xl}` 32px · `{spacing.xxl}` 48px · `{spacing.section}` 64px.
*   **Khoảng đệm (padding) phần (dọc):** `{spacing.section}` (64px) cho các mảng trang chính; chặt chẽ hơn so với hoạt động tiếp thị SaaS thông thường (80–96px) vì các trang thị trường (marketplace) cần mật độ thẻ cao hơn trong mỗi lần cuộn.
*   **Khoảng đệm bên trong thẻ:** `{spacing.lg}` (24px) cho `{component.host-card}` và `{component.reservation-card}`; `{spacing.base}` (16px) cho khối meta thẻ bất động sản; `{spacing.sm}` (8px) cho phần chú thích / khoảng cách giữa các hàng ngày tháng.
*   **Khoảng trống giữa các phần tử (Gutters):** `{spacing.base}` (16px) giữa các thẻ trong lưới thành phố trên trang chủ; `{spacing.lg}` (24px) bên trong các rãnh cột ở chân trang; `{spacing.xs}` (4px) trên các dải phân cách danh mục dày đặc.

### Lưới & Container (Grid & Container)
*   **Chiều rộng nội dung tối đa:** ~1280px được căn giữa trên trang chủ và các trang bài viết/biên tập. Các trang chi tiết giới hạn gần 1080px để giữ cho banner ảnh và thanh đặt phòng dễ đọc.
*   **Lưới liên kết thành phố (chân trang chủ):** Lưới 6 cột trên máy tính với mỗi ô chứa tên thành phố bằng `{typography.title-md}` và một nhãn phụ danh mục bằng `{typography.body-sm}` (Mờ).
*   **Chi tiết thông tin chỗ ở:** 2 cột với ảnh / nội dung tiện nghi bên trái (rộng ~64%) và thẻ đặt phòng cố định (sticky) (`{component.reservation-card}`) bên phải (~32%).
*   **Chân trang:** Danh sách liên kết 3 cột (Hỗ trợ / Đón tiếp khách / Airbnb) trên máy tính, thu gọn thành 1 cột trên thiết bị di động.

### Triết lý Khoảng trắng (Whitespace)
Hệ thống tạo ra khoảng không gian thở theo chiều dọc là 64px cho các dải nội dung nhưng nén các lưới thẻ — thẻ bất động sản và thẻ liên kết thành phố chỉ cách nhau 16px. Sự tương phản này là có chủ ý: trang web đọc giống như "mở rộng ở phần đầu (hero), và một thị trường dày đặc bên dưới," củng cố bản chất của một trang marketplace (thị trường) mà không làm cho khách truy cập bị ngợp ở nếp gấp (lần cuộn đầu tiên).

---

## Độ nổi (Elevation)

Hệ thống này cơ bản chỉ có **một tầng đổ bóng** cộng với đường cơ sở phẳng.

*   **Phẳng (không đổ bóng):** Phần thân, phần hero đầu trang, chân trang, tất cả các dải biên tập — 95% bề mặt.
*   **Thẻ nổi lên khi rê chuột:** `box-shadow: rgba(0, 0, 0, 0.02) 0 0 0 1px, rgba(0, 0, 0, 0.04) 0 2px 6px 0, rgba(0, 0, 0, 0.1) 0 4px 8px 0` — được áp dụng cho các thẻ bất động sản khi con trỏ di chuột qua, thanh tìm kiếm ở trạng thái nghỉ, và các menu thả xuống (menu tài khoản, bộ chọn ngôn ngữ, bộ chọn ngày). Đây là định nghĩa đổ bóng duy nhất trong toàn bộ hệ thống.
*   **Màn che Modal:** `{colors.scrim}` hiển thị ở mức độ mờ 50% — phông nền hộp thoại toàn cục. Dùng cho bộ chọn ngày, hộp thoại đăng nhập, bộ chọn ngôn ngữ.

Không có các lớp nâng lũy tiến — hệ thống chỉ có lớp bóng này hoặc không có. Độ sâu đến từ nhiếp ảnh, sự phân tách bề mặt trắng-trên-trắng và việc cắt bỏ góc bo tròn thay vì dùng các bóng đổ xếp lớp.

---

## Các thành phần (Components)

### Nút bấm (Buttons)
*   **`button-primary`** — Nền Rausch, chữ trắng, bo góc 8px, padding 14×24px, chiều cao 48px, độ đậm 500. CTA phổ biến nhất trên hệ thống: "Reserve" (Đặt phòng), "Continue" (Tiếp tục), "Search" (Tìm kiếm).
*   **`button-primary-active`** — Trạng thái khi nhấn. Nền chuyển sang `{colors.primary-active}`. Không thay đổi hình khối, không thay đổi bóng đổ.
*   **`button-primary-disabled`** — Tông màu Rausch nhạt tại #ffd1da với chữ trắng. Trỏ chuột dạng không cho phép (not-allowed).
*   **`button-secondary`** — Nền trắng với chữ màu mực (ink) và đường viền mực 1px. Bo góc 8px. Dùng cho nút "Lưu", "Hủy" và các CTA nghịch đảo trên nền màu Rausch.
*   **`button-tertiary-text`** — Văn bản mực đơn thuần, không bề mặt, không viền. Gạch chân khi di chuột. Dùng cho các loại liên kết "Hiển thị thêm" và nhãn đóng hộp thoại (modal).
*   **`button-pill-rausch`** — CTA hình viên thuốc màu Rausch được sử dụng trên các ô nổi bật (ví dụ: CTA phụ "Trở thành chủ nhà") — bán kính 9999px, padding 10×20px, nhãn 14px.

### Bề mặt Tìm kiếm (Search Surface)
*   **`search-bar-pill`** — Thanh tìm kiếm toàn cầu mang tính biểu tượng. Bề mặt trắng, bán kính 9999px, chiều cao 64px, viền mảnh hairline 1px đổ bóng 1px. Bên trong được chia bằng các đường kẻ dọc mỏng thành các ô `{component.search-field-segment}` (Ở đâu / Khi nào / Ai). Mỗi phân đoạn chứa một nhãn chú thích viết hoa bên trên dòng giữ chỗ (placeholder) bằng `{typography.caption}`.
*   **`search-orb`** — Quả cầu màu Rausch nằm ở mép phải của thanh tìm kiếm. 48×48px, bo tròn hoàn toàn, biểu tượng kính lúp màu trắng ở giữa. Đây là điểm nhấn màu sắc rực rỡ nhất trên trang chủ.

### Điều hướng Trên cùng (Top Navigation)
*   **`top-nav`** — Bề mặt trắng, cao 80px, đường viền dưới mỏng 1px. Logo chữ Airbnb nằm ngang bằng bên trái, ba tab sản phẩm (Chỗ ở / Trải nghiệm / Dịch vụ) nằm ngay chính giữa, và các tiện ích tài khoản (liên kết chủ nhà, quả địa cầu ngôn ngữ, menu tài khoản) nằm ngang bằng bên phải.
*   **`product-tab-active`** — Nhãn màu mực theo `{typography.nav-link}`, biểu tượng minh họa vẽ tay 32px, đường gạch chân mực 2px nằm dưới cặp biểu tượng-nhãn.
*   **`product-tab-inactive`** — Nhãn mờ (muted), biểu tượng minh họa, không gạch chân. Sẽ hoạt động (active) khi được nhấp vào.
*   **`new-tag`** — Một huy hiệu hình viên thuốc nhỏ xíu bo tròn (`{rounded.full}`) bám sát góc trên bên phải của biểu tượng, mang nhãn viết hoa "NEW" (MỚI) bằng `{typography.uppercase-tag}` (8px / 700 với khoảng cách chữ 0.32px, viết hoa). Được dùng trên mục Trải nghiệm và Dịch vụ để báo hiệu sự mới mẻ.

### Thẻ Danh sách (Listing Cards)
*   **`property-card`** — Một thẻ ưu tiên hình ảnh. Hình ảnh tỷ lệ 1:1 với góc bo `{rounded.md}`, lớp phủ chấm tròn cho băng chuyền hình ảnh, huy hiệu nổi "Khách được yêu thích" ở trên cùng bên trái (`{component.guest-favorite-badge}`), và biểu tượng trái tim ở trên cùng bên phải (`{component.icon-button-circle}` ở trạng thái viền ngoài mặc định, nền đầy Rausch khi được lưu). Bên dưới ảnh: 4–5 dòng meta — tiêu đề (`{typography.title-md}`), khoảng cách / ngày tháng (`{typography.body-sm}` mờ), và giá ("$X đêm") căn lề phải.
*   **`property-card-photo`** — Chính là mảng hình ảnh, được tách ra thành một token riêng vì một số bề mặt (danh sách yêu thích, kết quả tìm kiếm) tái sử dụng chỉ riêng ảnh mà không có khối meta.
*   **`experience-card`** — Thẻ có tỷ lệ cao hơn (4:5) cho các mục trải nghiệm. Bo góc `{rounded.md}` tương tự, huy hiệu nổi "NEW" trên cùng bên trái, trái tim trên cùng bên phải, và tiêu đề một dòng bên dưới.
*   **`guest-favorite-badge`** — Viên thuốc bo tròn trắng (`{rounded.full}`) với chữ kích thước 11px / độ đậm 600. Nằm phía trên ảnh với độ nổi một lớp đổ bóng duy nhất của hệ thống được áp dụng.

### Chi tiết Chỗ ở (Listing Detail)
*   **`rating-display-card`** — Điểm nhấn nổi bật trong phần chi tiết. Con số đánh giá 64px / 700 ("4.81") được kẹp giữa bởi hai biểu tượng SVG vòng nguyệt quế nhỏ bên trái và phải. Bên dưới đánh giá: dòng khẩu hiệu "Khách được yêu thích" và một hàng các cột thống kê màu mực. Đây là trọng lượng chữ lớn nhất trong toàn bộ hệ thống.
*   **`amenity-row`** — Danh sách 1 cột gồm các biểu tượng tiện nghi + nhãn mực theo `{typography.body-md}`. Padding hàng 12px, không có viền giữa các hàng; phần này được đóng lại bằng một đường kẻ phân cách mảnh 1px phía trên và dưới.
*   **`reviews-card`** — Lưới 2 cột chứa các trích đoạn đánh giá. Mỗi cột chứa một hàng tác giả (ảnh đại diện, tên, ngày tháng) phía trên đoạn trích 3 dòng với liên kết cấp ba "Hiển thị thêm".
*   **`host-card`** — Thẻ nền trắng với góc bo `{rounded.md}` và padding 24px, chứa ảnh đại diện chủ nhà, tên, huy hiệu "Chủ nhà siêu cấp" (Superhost), thống kê tỷ lệ phản hồi, và một nút `{component.button-secondary}` "Liên hệ chủ nhà".
*   **`reservation-card`** — Thẻ cố định ở cột bên phải trên trang chi tiết chỗ ở. Bề mặt trắng, bo tròn `{rounded.md}`, viền kẻ mảnh 1px, đổ bóng nâng cao cấp 1, padding 24px. Chứa: giá mỗi đêm (mực `{typography.display-md}`), bộ chọn phạm vi ngày, bộ đếm số lượng khách, CTA "Đặt phòng" màu chính (primary) trải dài toàn chiều rộng, và danh sách phân tích phí bên dưới với chữ `{typography.body-sm}`.

### Bộ chọn Ngày (Date Picker)
*   **`date-picker-day`** — Ô tròn 40×40px mang số ngày theo `{typography.body-sm}`. Trạng thái mặc định là nền trong suốt, chữ mực (ink).
*   **`date-picker-day-selected`** — Đổ nền màu mực, chữ trắng, bo tròn hoàn toàn (`{rounded.full}`). Trạng thái phạm vi nằm giữa hai ngày được chọn sẽ mang một nền hình thoi dẹt (lozenge) `{colors.surface-soft}` kết nối chúng lại.

### Biểu mẫu (Forms)
*   **`text-input`** — Bề mặt trắng, viền ngoài nét mảnh 1px, bo góc `{rounded.sm}` 8px, cao 56px, padding 14×12px. Nhãn xếp chồng phía trên (theo `{typography.caption}` mờ), văn bản giữ chỗ theo `{typography.body-md}` mờ. Khi được chọn (focus), viền sẽ dày lên 2px màu mực và màu viền lật sang `{colors.ink}` — không phát sáng, không có vòng (ring) xung quanh.

### Chân trang (Footer)
*   **`footer-light`** — Bề mặt trắng (khớp với nền trang web — Airbnb không sử dụng chân trang màu tương phản), padding 48×80px. Ba cột chứa các khối liên kết (Hỗ trợ / Đón tiếp khách / Airbnb), phân cách bởi các khoảng trống (gutters) rộng 24px. Mỗi cột có nhãn tiêu đề `{typography.title-sm}` bằng màu mực và xếp chồng các hàng `{component.footer-link}` bằng `{typography.body-sm}` màu mực.
*   **`legal-band`** — Dải viền dưới cùng bên dưới các cột chân trang mang dòng bản quyền, bộ chọn ngôn ngữ (biểu tượng quả địa cầu + liên kết "Tiếng Anh (Mỹ)"), bộ chọn tiền tệ, và các biểu tượng xã hội (Facebook, X, Instagram). Tất cả văn bản ở dạng màu mờ `{colors.muted}` tại `{typography.caption-sm}`.

---

## Hoạt động trên các Thiết bị (Responsive Behavior)

| Thiết bị | Chiều rộng | Thay đổi chính |
|---|---|---|
| **Di động** | < 744px | Thanh điều hướng trên cùng thu gọn thành logo + hamburger; các tab sản phẩm ẩn sau một bảng kéo (sheet); thanh tìm kiếm thu gọn thành một viên thuốc duy nhất có thể chạm; thẻ chỗ ở xếp dọc 1 cột; lưới thành phố 1 cột; chi tiết chỗ ở thu gọn thẻ đặt phòng thành thanh cố định ở dưới cùng. |
| **Máy tính bảng** | 744–1128px | Điều hướng trên cùng giữ các tab sản phẩm nhưng thanh tìm kiếm hẹp lại; thẻ chỗ ở hiển thị 2 cột (2-up); lưới thành phố 2–3 cột; thẻ đặt phòng vẫn cố định ở bên phải với chiều rộng hẹp hơn. |
| **Máy tính bàn** | 1128–1440px | Thanh điều hướng đầy đủ với ba tab sản phẩm nằm giữa; thanh tìm kiếm viên thuốc hiển thị đủ 3 đoạn; thẻ chỗ ở hiển thị 4 cột; lưới thành phố 6 cột; trang chi tiết 2 cột với thanh đặt phòng. |
| **Màn hình rộng** | > 1440px | Chiều rộng nội dung tối đa ở mức 1440px trên trang danh sách/tìm kiếm và ~1280px trên trang bài viết; các khoảng trống (gutters) sẽ tự hấp thụ phần dư. |

### Mục tiêu Cảm ứng (Touch Targets)
*   Các CTA chính có kích thước tối thiểu 48×48px (Vượt tiêu chuẩn WCAG AAA).
*   Quả cầu tìm kiếm có hình tròn 48×48px — phần tử được chạm nhiều nhất trên trang.
*   Nút thả tim là hình tròn 32×32px — kích thước ranh giới đối với tiêu chuẩn AAA nhưng được bù đắp bởi khoảng đệm 12px rộng rãi bên trong thẻ ảnh.
*   Các ô ngày trong bộ chọn ngày là hình tròn 40×40px.

### Chiến lược Thu gọn (Collapsing Strategy)
*   Các tab sản phẩm hàng đầu thu gọn vào một bảng kéo hamburger khi dưới 744px.
*   3 phân đoạn của thanh tìm kiếm thu gọn lại thành một mục duy nhất, khi chạm sẽ mở ra thanh tìm kiếm toàn màn hình trên thiết bị di động.
*   Lưới bất động sản và lưới liên kết thành phố giảm số cột một cách dứt khoát tại mỗi điểm ngắt (breakpoint) — không bao giờ để các hàng tự động chảy (reflow) lung tung; luôn ưu tiên giảm số cột.
*   Thẻ đặt phòng trên chi tiết danh sách chuyển từ dạng cố định bên phải (right-rail) sang dạng thanh cố định dưới cùng (bottom bar) trên thiết bị di động, chỉ giữ lại CTA "Đặt phòng" + tóm tắt giá mỗi đêm.

---

## Các khoảng trống dữ liệu (Known Gaps)
*   **Màu sắc trạng thái rê chuột (Hover state):** Cố ý không ghi lại dữ liệu vì chính sách toàn cầu không có hiệu ứng hover — thiết kế `:hover` thực tế của Airbnb đối với các thẻ bất động sản là một độ nổi tinh tế nâng thẻ lên, nhưng việc trích xuất chính xác là không đáng tin cậy.
*   **Trạng thái tải (Loading states) / Khung xương màn hình (skeleton screens):** Không thể nhìn thấy trên các bề mặt được trích xuất.
*   **Kiểu hiển thị bản đồ:** Bản đồ hiển thị kết quả tìm kiếm sử dụng các lát cắt màu do Mapbox cung cấp với các điểm đánh dấu màu Rausch tùy chỉnh; phần này không được ghi nhận tại đây.
*   **Trạng thái lỗi nhập liệu:** Màu chữ báo lỗi (`{colors.primary-error-text}`) đã được lập tài liệu, nhưng sự kết hợp toàn bộ viền nhập liệu + văn bản trợ giúp khi lỗi xác thực xảy ra thì không được hiển thị trên các bề mặt thu thập.
*   **Bảng màu thương hiệu phụ:** Luxe (`{colors.luxe}`) và Plus (`{colors.plus}`) được lưu dưới dạng token, nhưng toàn bộ hệ thống phụ của chúng (ghi đè kiểu chữ, xử lý bề mặt) nằm trên các tên miền phụ riêng biệt và không được thu thập tại đây.