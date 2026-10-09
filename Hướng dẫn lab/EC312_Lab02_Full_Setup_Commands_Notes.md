# EC312 – Lab 02
# Full setup, cấu hình, tùy chỉnh giao diện, email và lưu ý vận hành Shop Đồng Hồ

**Sinh viên:** Lê Hiếu Huy  
**MSSV:** 24520666  
**Học phần:** EC312 – Thiết kế hệ thống thương mại điện tử  
**Môi trường:** Windows + VS Code + Docker Desktop + Nginx + WordPress + WooCommerce + Flatsome/Flatsome Child + MySQL + WP Mail SMTP  
**Website local:** `http://localhost:8080`  
**Tài liệu kế thừa:** `EC312_Lab01_Full_Setup_Commands_Notes.md`  
**Báo cáo minh chứng đi kèm:** `EC312_Lab02_Bao_cao_FINAL_Loco_Translate.docx`

> **Vai trò tài liệu:** Đây là sổ tay **thực hiện và khôi phục thao tác** của Lab 02, viết theo phong cách file hướng dẫn Lab 01. Tài liệu ghi rõ các bước đã thực hiện, đường dẫn cài đặt, cấu hình quan sát được, lệnh kiểm tra, đoạn mã tham khảo và các lỗi đã gặp. **Không phải bản sao nguyên trạng toàn bộ `functions.php`/`style.css` của website**: muốn phục dựng chính xác giao diện đã chỉnh thì cần bảo quản hai tệp thực tế và dữ liệu WordPress.
>
> **Mức độ xác nhận:** Các kết quả có ảnh minh chứng được đối chiếu với báo cáo Lab 02 FINAL. Các đoạn **mã minh họa** bên dưới không nhất thiết trùng 100% với phiên bản cuối trong child theme và **không được dán chồng lên code đang chạy**.

---

# 1. Mục tiêu cuối cùng của Lab 02

Giảng viên yêu cầu **05 hạng mục, mỗi hạng mục 02 điểm**:

1. **Việt hóa shop (2đ).**
2. **Cài đặt thông tin cơ bản**: địa chỉ shop, giao nhận, thanh toán... (2đ).
3. **Cài đặt Mail server** để có thể gửi mail cho khách hàng (2đ).
4. **Tùy chỉnh giao diện của các Page** (2đ).
5. **Tùy chỉnh Email template** (2đ).

Phần cuối của Lab 02 cần chứng minh được:

- Menu, nhãn mua sắm và cấu hình ngôn ngữ ưu tiên tiếng Việt; có sử dụng **Loco Translate**.
- WooCommerce có địa chỉ, tiền tệ **VND**, shipping zone Việt Nam, **Giao hàng tiêu chuẩn 30.000đ**, COD.
- WordPress có thể gửi email qua WP Mail SMTP; email thực tế đến hộp thư Gmail.
- Giao diện **Homepage, Shop, Product Details, Cart, Checkout, Order Complete** đã tùy chỉnh trên Flatsome/Flatsome Child.
- Có ít nhất hai mẫu email đã tùy chỉnh: **New Order** và **Processing Order**.

**Kết quả giao dịch đã xác nhận:** Đơn **#1041**, sản phẩm **4.212.000đ**, phí ship **30.000đ**, tổng **4.242.000đ**, thanh toán **COD**; email của đơn này đã đến Gmail.

---

# 2. Phạm vi kế thừa từ Lab 01

Lab 02 **không yêu cầu dựng lại toàn bộ Docker và nhập lại dữ liệu sản phẩm** nếu Lab 01 đang chạy được. Sử dụng tiếp project:

```text
C:\Users\Admin\Desktop\ec312-lab1
```

Sơ đồ làm việc:

```text
ec312-lab1/
├── docker-compose.yml
├── php.ini
├── nginx/
│   └── default.conf
├── wordpress/
│   ├── wp-admin/
│   ├── wp-content/
│   │   ├── languages/
│   │   ├── plugins/
│   │   │   ├── woocommerce/
│   │   │   ├── loco-translate/           # nếu plugin đã cài
│   │   │   └── wp-mail-smtp/            # tên folder có thể khác theo bản cài
│   │   ├── themes/
│   │   │   ├── flatsome/
│   │   │   └── flatsome-child/
│   │   │       ├── functions.php
│   │   │       ├── style.css
│   │   │       └── screenshot.png
│   │   └── uploads/
│   └── wp-config.php
└── Hướng dẫn lab/
    ├── EC312_Lab01_Full_Setup_Commands_Notes.md
    └── EC312_Lab02_Full_Setup_Commands_Notes.md  # đặt file này vào đây
```

**Chú ý:** Sơ đồ trên mô tả các đường dẫn liên quan, không có nghĩa mọi thư mục đều được Git theo dõi. Project trước đó có `.gitignore` dành cho các thư mục WordPress; cần kiểm tra khi muốn backup hoặc nộp source.

---

# 3. Khởi động lại project trước khi làm Lab 02

Mở **PowerShell** tại project:

```powershell
cd C:\Users\Admin\Desktop\ec312-lab1

docker compose ps
```

Nếu container chưa chạy:

```powershell
docker compose up -d
```

Kiểm tra:

```powershell
docker compose ps

docker logs --tail 50 ec312_nginx

docker logs --tail 50 ec312_wordpress

docker logs --tail 50 ec312_mysql
```

Mở:

```text
Trang web:   http://localhost:8080/
Trang admin: http://localhost:8080/wp-admin/
Shop:        http://localhost:8080/shop/
Giỏ hàng:    http://localhost:8080/cart/
Thanh toán:  http://localhost:8080/checkout/
```

Một số trang có thể sử dụng slug đã chỉnh; khi đó xem URL đúng tại **Trang → Tất cả trang** thay vì mặc định rằng toàn bộ đường dẫn trên phải tồn tại.

**Không dùng:**

```powershell
docker compose down -v
```

`-v` có thể xóa database volume MySQL, kéo theo sản phẩm, đơn hàng và các thiết lập được lưu trong database.

---

# 4. Sao lưu trước khi chỉnh theme và cấu hình

Vào project:

```powershell
cd C:\Users\Admin\Desktop\ec312-lab1
```

Sao lưu **hai file code** đang hoạt động trước khi sửa:

```powershell
$ts = Get-Date -Format 'yyyyMMdd-HHmmss'
$backup = "backups\lab02-$ts"
New-Item -ItemType Directory -Force -Path $backup | Out-Null

Copy-Item "wordpress\wp-content\themes\flatsome-child\functions.php" "$backup\functions.php.bak"
Copy-Item "wordpress\wp-content\themes\flatsome-child\style.css" "$backup\style.css.bak"
```

Để sao lưu cấu hình WordPress, đơn hàng, sản phẩm và cài đặt plugin, **chỉ copy CSS/PHP là chưa đủ**. Cần backup cả database (qua giải pháp backup đang dùng hoặc xuất SQL đúng cách) và thư mục upload nếu cần phục dựng toàn bộ website.

Lưu ý:

- Không sửa theme cha `flatsome/`.
- Không sửa `wp-includes`, WooCommerce Core hoặc plugin gốc chỉ để đổi giao diện.
- Không tự thay `functions.php` hiện tại bằng mã ví dụ ngắn trong tài liệu này.

---

# 5. Việt hóa WordPress – chọn tiếng Việt làm ngôn ngữ website

Truy cập:

```text
WordPress Admin
→ Cài đặt (Settings)
→ Tổng quan (General)
→ Ngôn ngữ của trang web (Site Language)
```

Chọn:

```text
Tiếng Việt
```

Bấm **Lưu thay đổi**.

Kết quả cần nhìn thấy:

- Các nhãn quản trị WordPress cơ bản hiện bằng tiếng Việt.
- Những plugin đã có bản dịch tiếng Việt có thể tự hiển thị chuỗi tiếng Việt.
- Việc đổi Site Language **không tự dịch tên các sản phẩm demo, nội dung nhập tay hoặc mọi chuỗi theme**.

**Minh chứng:** Hình 01 trong báo cáo FINAL.

---

# 6. Cài đặt và kích hoạt Loco Translate

Truy cập:

```text
Plugin
→ Cài plugin mới
→ tìm "Loco Translate"
→ Cài đặt
→ Kích hoạt
```

Trong quá trình làm Lab 02, hình minh chứng ghi nhận **Loco Translate 2.8.9** đang bật.

Để kiểm tra:

```text
Plugin → Plugin đã cài
```

Nếu dưới plugin thấy nút **Vô hiệu hóa** (Deactivate) thì plugin đang được kích hoạt.

Loco Translate dùng để quản lý bản dịch `.po/.mo` trong WordPress, **không thay thế tính năng sửa dữ liệu sản phẩm WooCommerce**.

**Minh chứng:** Hình 04.

---

# 7. Kiểm tra và quản lý bộ dịch WooCommerce

Truy cập:

```text
Loco Translate
→ Plugin
→ WooCommerce
→ Tiếng Việt (Vietnamese)
```

Ở thời điểm chụp ảnh Lab 02, màn hình cho thấy:

```text
File: woocommerce-vi.po
Ngôn ngữ: Vietnamese
Tổng chuỗi: 10.122
Tỷ lệ có bản dịch: 99,9%
Còn chờ xử lý: 01 chuỗi
```

**Diễn giải đúng:** `99,9%` là tỷ lệ chuỗi trong **file dịch WooCommerce được quan sát tại thời điểm chụp**, không phải cam kết 99,9% toàn bộ chữ trên website đã được Việt hóa.

Quy trình khi cần dịch một chuỗi còn thiếu:

1. Chọn gói `woocommerce-vi.po`.
2. Tìm chuỗi tiếng Anh (Source text).
3. Nhập bản dịch tiếng Việt ở vùng Translation.
4. Nhấn **Save**.
5. Refresh trang frontend, kiểm tra chuỗi đã được áp dụng chưa.

Không xóa hoặc ghi đè file dịch hệ thống tùy tiện. Nếu WordPress/WooCommerce cập nhật gói ngôn ngữ, cần kiểm tra phạm vi ảnh hưởng tới bản dịch tùy chỉnh.

**Minh chứng:** Hình 05.

---

# 8. Khởi tạo bản dịch riêng cho Flatsome bằng Loco Translate

Vào:

```text
Loco Translate
→ Giao diện / Themes
→ Flatsome
→ Thêm ngôn ngữ / New language
```

Chọn:

```text
Language: Vietnamese
Location: Custom / Vị trí tùy chỉnh
Tệp: flatsome-vi.po
```

Đường dẫn được ghi nhận:

```text
wordpress/wp-content/languages/loco/themes/flatsome-vi.po
```

Sau đó:

1. Tạo file dịch.
2. Mở trình biên tập Loco Translate.
3. Tìm chuỗi cần dịch, ví dụ `Quick View`.
4. Nếu muốn Việt hóa: nhập `Xem nhanh` vào Translation, rồi lưu.
5. Truy cập Shop để kiểm tra sự thay đổi (nếu phần tử đó thật sự đang hiển thị).

**Trạng thái thực tế đã có ảnh:** file Flatsome tùy chỉnh được khởi tạo, màn hình biên tập hiển thị **115 chuỗi, 0% đã dịch** tại thời điểm ghi nhận. **Không ghi trong báo cáo rằng toàn bộ Flatsome đã được dịch xong.**

**Minh chứng:** Hình 06 và Hình 07.

---

# 9. Việt hóa Main Menu

Truy cập:

```text
Giao diện (Appearance)
→ Menu / Thiết lập Menu
→ Chọn menu đang hiển thị ở Main Menu
```

Các nhãn menu đã triển khai:

| Thứ tự | Văn bản hiển thị | Chức năng |
|---|---|---|
| 1 | Trang chủ | Điều hướng về homepage |
| 2 | Cửa hàng | Điều hướng đến trang Shop |
| 3 | Liên hệ | Điều hướng đến trang liên hệ / liên kết được thiết lập |

Thao tác:

1. Chọn đúng menu đang dùng ở header.
2. Mở từng mục menu để đổi **Nhãn điều hướng** thành tiếng Việt.
3. Kéo mục menu để sắp xếp cùng cấp nếu muốn ba mục nằm ngang.
4. Gán vị trí **Main Menu** (theo giao diện quản trị Flatsome đang dùng).
5. Nhấn **Lưu menu**.
6. Kiểm tra ba nhãn trên header thực tế.

**Lưu ý:** Không nhất thiết đổi tên menu nội bộ thành tiếng Việt; điều quan trọng là nhãn hiển thị và đường dẫn hoạt động. Ảnh thao tác có thể ghi nhận trạng thái menu đang thụt cấp, còn ảnh frontend cuối cùng cho thấy ba mục nằm ngang.

**Minh chứng:** Hình 02, Hình 03.

---

# 10. Rà soát các nhãn tiếng Việt trong quy trình mua hàng

Các nhãn đã có minh chứng trên storefront:

```text
TRANG CHỦ
CỬA HÀNG
LIÊN HỆ
DANH MỤC ĐỒNG HỒ
SẢN PHẨM MỚI NHẤT
THÔNG TIN THANH TOÁN
ĐƠN HÀNG CỦA BẠN
ĐẶT HÀNG
Thanh toán khi nhận hàng (COD)
Chi tiết đơn hàng
```

Đồng thời vẫn còn một số tên sản phẩm / danh mục demo tiếng Anh. Đây là **dữ liệu nhập khẩu**, không nên nhầm với lỗi gói ngôn ngữ.

**Minh chứng:** Hình 08 và các ảnh Homepage/Checkout.

---

# 11. Cài đặt thông tin địa chỉ Shop Đồng Hồ

Truy cập:

```text
WooCommerce
→ Cài đặt (Settings)
→ Cài đặt chung (General)
```

Địa chỉ ghi nhận trong Lab:

```text
Địa chỉ dòng 1: Khu phố 6, Phường Linh Trung
Thành phố: Ho Chi Minh City / TP. Hồ Chí Minh
Quốc gia: Việt Nam
Mã bưu điện: 700000
```

Địa chỉ và khu vực ở đây là **dữ liệu cấu hình cửa hàng**, không phải địa chỉ giao hàng của đơn #1041.

Sau khi nhập, nhấn **Lưu thay đổi**.

**Minh chứng:** Hình 09.

---

# 12. Thiết lập khu vực bán hàng và vị trí khách hàng

Cũng ở:

```text
WooCommerce → Cài đặt → Cài đặt chung
```

Thiết lập nền tảng được ghi trong hướng dẫn Lab 01:

```text
Selling location(s): Sell to all countries
Shipping location(s): Ship to all countries you sell to
Default customer location: Shop country/region
```

Ở Lab 02, cần **đối chiếu màn hình cấu hình thực tế** trước khi thay đổi. Không bắt buộc chỉnh lại nếu website đã tính phí ship Việt Nam và COD đúng.

---

# 13. Cài tiền tệ VND và định dạng giá

Truy cập:

```text
WooCommerce → Cài đặt → Cài đặt chung
→ Tùy chọn tiền tệ / Currency options
```

Giá trị đã có minh chứng:

```text
Tiền tệ: Việt Nam đồng (VND)
Số chữ số thập phân: 0
```

Các dấu ngăn cách hàng nghìn / vị trí ký hiệu `₫` tùy theo cấu hình hiển thị.

Kiểm tra bằng giá thật trên cửa hàng:

```text
Giá một sản phẩm dùng để test: 4.212.000đ
```

**Minh chứng:** Hình 10 và giao diện đơn #1041.

---

# 14. Cài phương thức giao hàng (Shipping)

Truy cập:

```text
WooCommerce
→ Cài đặt
→ Vận chuyển (Shipping)
→ Khu vực giao hàng (Shipping zones)
```

Tạo hoặc kiểm tra khu vực:

```text
Tên khu vực: Vietnam
Khu vực: Việt Nam
```

Phương thức:

```text
Kiểu: Flat rate (Phí cố định)
Tên hiển thị: Giao hàng tiêu chuẩn
Trạng thái thuế: None (theo thiết lập đã dùng ở Lab 01)
Chi phí: 30000
```

Bấm **Lưu**.

Ở Checkout hoặc giỏ hàng của đơn thử, cần có:

```text
Giao hàng tiêu chuẩn: 30.000đ
```

**Minh chứng:** Hình 11; Hình 13–14 xác nhận phí ship thật trong giao dịch.

---

# 15. Cài phương thức thanh toán COD

Truy cập:

```text
WooCommerce
→ Cài đặt
→ Thanh toán (Payments)
```

Tìm **Thanh toán khi nhận hàng (Cash on delivery – COD)**, bật và cấu hình:

```text
Title: Thanh toán khi nhận hàng (COD)
Description: Thanh toán bằng tiền mặt khi nhận hàng.
Instructions: Vui lòng chuẩn bị tiền mặt khi nhận hàng.
```

Các phương thức ngoại tuyến khác (chuyển khoản ngân hàng, séc) có thể xuất hiện trong danh sách, nhưng **ca kiểm thử xác nhận sử dụng COD**.

**Đừng chỉ nhìn ảnh danh sách phương thức** để kết luận COD đang bật; xác nhận ở Checkout và Order Complete là bằng chứng vận hành đáng tin cậy hơn.

**Minh chứng:** Hình 12–14.

---

# 16. Kiểm thử kết hợp cấu hình bán hàng

Sử dụng sản phẩm có sẵn từ dữ liệu WooCommerce đã nhập ở Lab 01:

1. Mở một trang Product Details.
2. Nhấn **THÊM VÀO GIỎ HÀNG**.
3. Vào trang Cart, kiểm tra tên sản phẩm, số lượng, giá.
4. Nhấn **TIẾN HÀNH THANH TOÁN**.
5. Điền các trường bắt buộc trong Checkout.
6. Chọn COD và phí Giao hàng tiêu chuẩn.
7. Nhấn **ĐẶT HÀNG** một lần.
8. Kiểm tra Order Complete và WooCommerce → Đơn hàng.

Ca kiểm thử đã ghi nhận:

| Trường | Giá trị |
|---|---|
| Đơn hàng | `#1041` |
| Sản phẩm | Đồng hồ Aikon #Tide mẫu hồng |
| Giá sản phẩm | `4.212.000đ` |
| Phí giao hàng | `30.000đ` |
| Tổng cộng | `4.242.000đ` |
| Thanh toán | `COD` |
| Kết quả | Tạo đơn, tới Order Complete, email xác nhận đến Gmail |

> **Không cần tạo lại đơn #1041.** Con số này là kết quả của lần thử đã hoàn thành, không phải yêu cầu mỗi lần mở lại lab đều phải đặt hàng mới.

---

# 17. Cài plugin WP Mail SMTP

Truy cập:

```text
Plugin → Cài mới → WP Mail SMTP → Cài đặt → Kích hoạt
```

Sau đó:

```text
WP Mail SMTP → Settings
```

Hệ thống Lab 02 dùng kết nối **Gmail/Google** để chuyển email do WooCommerce/WordPress tạo ra.

**Phân biệt:**

- **WP Mail SMTP:** công cụ chuyển email ra ngoài.
- **WooCommerce → Settings → Emails:** loại email, đối tượng nhận, tiêu đề, nội dung và giao diện.

Hai cấu hình liên quan nhưng không phải cùng một thứ.

---

# 18. Kết nối tài khoản Gmail/Google cho SMTP

Quy trình từng thực hiện:

1. Vào màn hình cài đặt WP Mail SMTP.
2. Chọn mailer Gmail/Google theo lựa chọn plugin đang hỗ trợ.
3. Làm theo luồng cấp quyền hoặc cấu hình OAuth phù hợp tài khoản Google.
4. Xác nhận quyền truy cập để plugin được phép gửi email.
5. Kiểm tra **From Name** và **From Email** của WordPress.
6. Lưu cấu hình.

Trong ảnh báo cáo, giao diện đã đi qua **bước xác nhận cấp quyền Google**.

**Lưu ý kỹ thuật:** Tài khoản Gmail và các biến OAuth/Client Secret là thông tin riêng của môi trường. Không ghi password, token, secret vào hướng dẫn `.md`, không đẩy lên Git và không chia sẻ trong ảnh chụp cấu hình.

**Minh chứng:** Hình 15.

---

# 19. Thử gửi email bằng WP Mail SMTP

Vào:

```text
WP Mail SMTP
→ Tools (Công cụ)
→ Email Test
```

Thao tác:

1. Nhập email nhận thử mà bạn có quyền truy cập.
2. Chọn gửi HTML (nếu giao diện plugin có tùy chọn này).
3. Nhấn **Send Email**.
4. Đọc thông báo gửi thành công ở WordPress.
5. Mở Gmail để kiểm tra **Inbox**, **Spam** và thư đã đến.

Kết quả thực tế: **WP Mail SMTP thông báo thành công và Gmail đã nhận email thử**.

**Minh chứng:** Hình 16–17.

**Quan trọng:** Gửi SMTP thành công **chưa đủ chứng minh** template WooCommerce sẽ tự động phát sinh. Phải thử giao dịch WooCommerce riêng (đã có đơn #1041).

---

# 20. Môi trường chỉnh giao diện: Flatsome Child Theme

Sửa code tại:

```text
wordpress/wp-content/themes/flatsome-child/
├── functions.php
└── style.css
```

Flatsome parent tại:

```text
wordpress/wp-content/themes/flatsome/
```

Trong Lab 02:

- Flatsome cung cấp Header Builder, UX Builder, WooCommerce templates và CSS nền.
- Flatsome Child chứa các đoạn PHP/CSS tùy chỉnh; bản code này phải được bảo quản nếu muốn có giao diện giống hiện tại.
- Header giữ logo **Patek Philippe** theo lựa chọn thiết kế trong quá trình thực hành.
- Dữ liệu sản phẩm, đơn hàng và COD vẫn lấy từ WooCommerce.

**Không thay thế trực tiếp thư mục `flatsome/`** hoặc bỏ child theme khi update.

---

# 21. Khác nhau giữa UX Builder và trình sửa code trang WordPress

Đây là một lỗi đã gặp lúc làm Homepage.

### UX Builder

Truy cập ví dụ:

```text
Trang → Tất cả trang → Trang chủ → Edit with UX Builder
```

Dùng để bố trí section, row, banner, text, products, slider, v.v.

### Gutenberg / WordPress Code Editor

Vào trang → **Sửa trang** → menu ba chấm → **Chế độ sửa code**.

Ở đây nội dung có thể chứa các **Flatsome shortcodes** như:

```text
[section ...]
[row]
[col span="12"]
[ux_banner ...]
[products ...]
[/col]
[/row]
[/section]
```

Nếu dán shortcode vào trình không xử lý shortcode như mong đợi, trang có thể in nguyên `[ux_text]` hoặc `[ux_banner]` ra frontend.

**Quy tắc:** Chỉ thay nội dung trang bằng code khi biết chính xác editor nào đang lưu shortcode và đã sao lưu nội dung cũ. Không dán một bản HTML/shortcode mới trùm lên toàn bộ page mà chưa thử hiển thị.

---

# 22. Cấu hình Header Builder (Flatsome)

Truy cập:

```text
Giao diện → Tùy biến → Header
```

Trong Header Builder, cấu trúc đã thao tác:

```text
[LOGO] [Main Menu]                 [Search Icon] [Account] [Cart]
```

Các khối **Top / Main / Bottom** là các hàng khác nhau của Header Builder, không phải trang riêng.

Thao tác cơ bản:

1. Chuyển sang **Desktop**.
2. Kéo `Logo`, `Main Menu`, `Search Icon`, `Account`, `Cart` vào đúng hàng.
3. Cấu hình logo trong **Logo & Site Identity**.
4. Gán menu tiếng Việt vào **Main Menu**.
5. Kiểm tra Mobile / Tablet.
6. Nhấn **Publish / Đăng**.

**Theo lựa chọn thực tế:** giữ logo Patek Philippe; không cần đổi logo trong hướng dẫn này.

---

# 23. Homepage – các khối chức năng đã triển khai

Homepage cuối cùng có:

1. Hero giới thiệu **SHOP ĐỒNG HỒ** và thông điệp **TINH HOA THỜI GIAN**.
2. Ba danh mục chính: **ĐỒNG HỒ NAM**, **ĐỒNG HỒ NỮ**, **ĐỒNG HỒ UNISEX**.
3. Khu vực **SẢN PHẨM MỚI NHẤT** lấy sản phẩm WooCommerce.
4. **THƯƠNG HIỆU NỔI BẬT**: Casio, Seiko, Orient, Tissot.
5. **TRẢI NGHIỆM MUA SẮM**: giao hàng, thanh toán, hỗ trợ.
6. CTA **KHÁM PHÁ BỘ SƯU TẬP**.

Hệ màu:

```css
/* Bảng màu Lab 02 (tham khảo để kiểm tra) */
:root {
  --ec312-navy: #101b2d;
  --ec312-gold: #d6b47a;
  --ec312-light: #f8f9fb;
  --ec312-muted: #64748b;
}
```

### Phần content trong database

Nội dung page có thể lưu shortcode Flatsome trong cơ sở dữ liệu. Nó **không tự tái tạo chỉ từ việc khôi phục `style.css`**.

### Phần CSS trong child theme

Các class và style của trang được chỉnh để thống nhất màu sắc, khoảng cách, tiêu đề, thẻ danh mục và product cards. Không dán lại toàn bộ CSS nếu site đã đúng.

**Minh chứng:** Hình 18–19.

---

# 24. Product Catalog – tùy chỉnh trang Shop

URL thường dùng:

```text
http://localhost:8080/shop/
```

Kết quả đã triển khai:

- Banner **BỘ SƯU TẬP ĐỒNG HỒ** với nền navy.
- Sidebar danh mục / phân loại; bộ lọc giá.
- Lưới **4 cột** sản phẩm (màn hình đủ rộng), **12 sản phẩm/trang**.
- Badge ưu đãi thay cho badge mặc định.
- Giá tiền và tên sản phẩm từ WooCommerce.
- Phân trang hoạt động.

Một số hook/filter được dùng trong giai đoạn Lab 02:

```php
// Trích ý tưởng đã triển khai, KHÔNG dán lại nếu functions.php hiện có hook tương đương.
add_filter('loop_shop_columns', function () {
    return 4;
}, 20);

add_filter('loop_shop_per_page', function () {
    return 12;
}, 20);

add_filter('woocommerce_sale_flash', function ($html) {
    return '<span class="onsale">Ưu đãi</span>';
});
```

**Lưu ý:** Hàm đặt số cột có thể bị Flatsome hoặc tuỳ chọn theme ghi đè tại một số viewport. CSS lưới cần đi cùng cấu hình thực tế để giao diện vẫn responsive.

**Minh chứng:** Hình 20.

---

# 25. Ví dụ hook tạo banner Shop

Giai đoạn triển khai đã dùng action WooCommerce để thêm banner, theo ý tưởng:

```php
// Ví dụ tham khảo. Không thêm nếu banner đã được đăng ký trong child theme.
add_action('woocommerce_before_main_content', function () {
    if (!is_shop()) {
        return;
    }
    ?>
    <div class="ec312-shop-banner">
        <span class="ec312-shop-eyebrow">THE WATCH COLLECTION</span>
        <h1>BỘ SƯU TẬP ĐỒNG HỒ</h1>
        <p>Khám phá những thiết kế đồng hồ dành cho phong cách của bạn.</p>
    </div>
    <?php
}, 5);
```

CSS liên quan (minh họa class và màu):

```css
.ec312-shop-banner {
  background: #101b2d;
  padding: 65px 20px;
  text-align: center;
  margin-bottom: 35px;
}
.ec312-shop-eyebrow { color: #d6b47a; letter-spacing: 3px; }
.ec312-shop-banner h1 { color: #fff; }
```

**Quan trọng:** Nếu banner được in hai lần, tìm trùng `woocommerce_before_main_content` hoặc đoạn banner HTML/shortcode trên trang Shop trước khi thêm CSS.

---

# 26. Product Details – tùy chỉnh trang chi tiết sản phẩm

Mở một sản phẩm trực tiếp ở Shop rồi chọn **Xem sản phẩm**.

Các nhóm đã có trong trang chi tiết:

```text
Ảnh sản phẩm lớn + ảnh nhỏ
Tên sản phẩm / SKU / danh mục
Giá bán
Chọn số lượng
Thêm vào giỏ hàng
Cam kết chất lượng
Giao hàng toàn quốc
Hỗ trợ khách hàng
Mô tả / đánh giá
Sản phẩm tương tự
```

Phần mô tả sản phẩm nhập từ dữ liệu có thể còn tiếng Anh; Lab 02 không tự dịch giá trị từ CSV của Lab 01.

Nguyên tắc:

- Chỉnh kiểu hiển thị bằng CSS / hooks.
- Giữ logic add-to-cart chuẩn WooCommerce.
- Không can thiệp giá thực của sản phẩm bằng CSS.

**Minh chứng:** Hình 21.

---

# 27. Custom Footer trong Flatsome Child

Footer tùy chỉnh cuối cùng có bốn cột:

```text
SHOP ĐỒNG HỒ
DANH MỤC SẢN PHẨM
HỖ TRỢ KHÁCH HÀNG
THÔNG TIN LIÊN HỆ
```

Theme dùng nền **navy**, heading **champagne**, chữ trắng/xám. Copyright: Shop Đồng Hồ.

Trong giai đoạn triển khai đã dùng hook:

```php
add_action('flatsome_footer', 'ec312_render_custom_footer', 20);
```

Trong đó `ec312_render_custom_footer()` là **hàm render đã có trong `functions.php` hiện tại**, không tự nhiên có sẵn trong WordPress. Không sao chép riêng dòng `add_action` vào một child theme mới nếu chưa định nghĩa hàm tương ứng.

Bản triển khai còn tắt widget cũ và ẩn footer bản demo. Cần cẩn thận vì Flatsome có thể có nhiều vùng footer và copyright bar.

**Chú ý:** Nếu custom footer đã đẹp, không thêm một footer thứ hai; phải chỉnh bản đang hoạt động.

---

# 28. Cart – trang giỏ hàng

Giữ trang Cart mặc định của WooCommerce, tùy chỉnh trình bày:

```text
Bảng sản phẩm, ảnh, số lượng, đơn giá
Nút cập nhật giỏ hàng
Tổng cộng giỏ hàng
Phí giao hàng
Mã ưu đãi
Nút tiến hành thanh toán
```

Kiểm thử được ghi nhận khi thêm đồng hồ giá **4.212.000đ** và hiển thị shipping **30.000đ**.

Không can thiệp trực tiếp session/cart logic bằng JavaScript chỉ để đổi màu hoặc cách căn lề.

**Minh chứng:** Hình 22.

---

# 29. Checkout V2 – trang thanh toán

Bố cục:

```text
CHECKOUT DETAILS
├── Tiêu đề "THANH TOÁN ĐƠN HÀNG"
├── Bên trái: thông tin người mua và địa chỉ
└── Bên phải: đơn hàng của bạn
    ├── Tổng tiền hàng
    ├── Phí vận chuyển
    ├── Phương thức COD
    ├── Nút ĐẶT HÀNG (navy)
    └── Khối thông tin/hỗ trợ
```

Màu nút cuối cùng:

```text
Navy: #101b2d
Hover champagne: #d6b47a
```

Đây chỉ là **CSS trình bày**. Nút phải tiếp tục gửi form WooCommerce với ID `place_order`, không thay bằng nút HTML vô chức năng.

Đoạn CSS tham khảo về selector:

```css
/* Không thêm lần nữa nếu file đã có rule tương đương. */
body.woocommerce-checkout #place_order,
body.woocommerce-checkout #payment button#place_order {
  background-color: #101b2d !important;
  border-color: #101b2d !important;
  color: #ffffff !important;
}
body.woocommerce-checkout #place_order:hover {
  background-color: #d6b47a !important;
  color: #101b2d !important;
}
```

Tránh cứ thêm CSS mới khi màu không đổi; kiểm tra **Inspect → Computed** và stylesheet thật sự được tải trước.

**Minh chứng:** Hình 23.

---

# 30. Lỗi Checkout loading vô hạn – triệu chứng

Trong quá trình kiểm thử, Checkout từng gặp:

```text
Loading overlay / spinner xoay mãi
Giá và phương thức COD hiển thị nhưng overlay không biến mất
```

Kiểm tra Chrome DevTools:

```text
F12 → Network → Fetch/XHR
```

Request quan trọng:

```text
?wc-ajax=update_order_review
```

Trong lần phân tích debug, request nhận HTTP 200 và nội dung JSON nhưng jQuery/WooCommerce vẫn không xử lý dứt điểm trạng thái loading.

Chỉ nhìn mã HTTP `200` là **chưa đủ**, cần kiểm tra Response Headers và data type.

---

# 31. Nguyên nhân đã xác định và cách sửa loading

Trong báo cáo chẩn đoán từ lần làm lab, lỗi gốc được truy về file:

```text
wordpress/wp-content/themes/flatsome-child/functions.php
```

**Có một dòng trắng trước thẻ `<?php` ở đầu file**. Khi PHP xuất dữ liệu sớm, header có thể bị gửi sai (ví dụ `Content-Type: text/html` thay vì `application/json`), làm jQuery xử lý phản hồi AJAX không đúng kiểu.

Bản sửa khi đó:

1. Sao lưu `functions.php`.
2. Xóa **duy nhất** ký tự trắng/newline/BOM trước `<?php`.
3. Để byte đầu tiên của file là `<` trong chuỗi `<?php`.
4. Không sửa WooCommerce Core.
5. Refresh Checkout và thử lại.

Kiểm tra cú pháp PHP trong container:

```powershell
docker exec ec312_wordpress php -l /var/www/html/wp-content/themes/flatsome-child/functions.php
```

Kết quả kỳ vọng:

```text
No syntax errors detected
```

Không tự thêm dấu `?>` vào cuối file PHP thuần chỉ để đóng tag. Không chèn HTML/echo ở đầu file.

**Ghi chú:** Đây là kết luận của lần debug đã làm; nếu sau này gặp spinner lại, phải kiểm tra request mới chứ không tự mặc định nguyên nhân vẫn là dòng trắng.

---

# 32. Thao tác xác minh Checkout trong trình duyệt

1. Mở `/checkout/` khi giỏ hàng có sản phẩm.
2. Bấm `F12` → tab **Network**.
3. Chọn **Fetch/XHR**.
4. Nhấn `Ctrl+F5` để tải lại.
5. Chọn request `update_order_review`.
6. Kiểm tra response, Content-Type và trạng thái hoàn tất.
7. Bảo đảm overlay tự biến mất, phí ship và COD vẫn hiện.

Nếu Console có lỗi:

```text
Failed to execute 'querySelector' ... invalid selector
```

Trong lần kiểm tra đã có nhắc tới bundle `index.js` của **YITH WooCommerce Wishlist**, nhưng **không có bằng chứng đủ để kết luận YITH là nguyên nhân loading Checkout**. Không gỡ plugin chỉ dựa trên vị trí dòng exception hoặc lỗi extension trình duyệt.

**Tuyệt đối không dùng giải pháp vĩnh viễn** kiểu `jQuery('.blockOverlay').remove()` chỉ để giấu lỗi. Nó che overlay nhưng không bảo đảm WooCommerce cập nhật đơn chính xác.

---

# 33. Order Complete V2 – trang sau khi đặt hàng

Sau một lần Checkout thành công, WooCommerce chuyển sang trang **Order Complete / order-received**.

Phần tùy chỉnh đã có:

```text
Biểu tượng xác nhận
"ĐẶT HÀNG THÀNH CÔNG!"
Mã đơn hàng động
Tổng thanh toán động
Phương thức thanh toán
Trạng thái đơn hàng
Nút TIẾP TỤC MUA SẮM
Chi tiết đơn hàng WooCommerce
Địa chỉ thanh toán và giao hàng
```

Dữ liệu hiển thị trong lần thử:

```text
#1041
4.242.000đ
COD
Đang xử lý
```

Phần tùy chỉnh sử dụng hook WooCommerce với ý tưởng:

```php
add_action('woocommerce_thankyou', 'ec312_order_complete_v2_hero', 1);
```

`ec312_order_complete_v2_hero()` là hàm custom trong child theme, **không phải hàm lõi**. Dữ liệu phải lấy bằng `wc_get_order($order_id)`, không hardcode `#1041` cho mọi khách hàng.

Có thể nhìn thấy thông tin WooCommerce mặc định và thông tin custom hơi trùng nhau; đây là điểm giao diện đã ghi nhận nhưng **không ảnh hưởng ca kiểm thử đã tạo đơn thành công**.

**Minh chứng:** Hình 24.

---

# 34. Cấu hình giao diện email dùng chung của WooCommerce

Truy cập:

```text
WooCommerce
→ Cài đặt
→ Email
→ Mẫu email / Email template
```

Bảng màu được lựa chọn:

| Trường | Giá trị |
|---|---|
| From name | `Shop Đồng Hồ` |
| Logo | Để trống trong ca kiểm thử; có thể bổ sung logo URL public sau |
| Logo width | `120px` |
| Header alignment | `Giữa` |
| Font | `Helvetica` |
| Footer text | `Shop Đồng Hồ | Tinh hoa thời gian` |
| Accent | `#D6B47A` |
| Email background | `#F4F6F9` |
| Content background | `#FFFFFF` |
| Heading/text | `#101B2D` |
| Secondary text | `#64748B` |

Bấm **Lưu thay đổi** ở cuối form.

Kiểm tra lựa chọn trong menu Preview; đổi loại email để xem giao diện tương ứng.

**Minh chứng:** Hình 25–26.

---

# 35. Email Template 1 – New Order (thông báo cho quản trị viên)

Đi đến:

```text
WooCommerce → Cài đặt → Email
→ Đơn hàng mới (New Order) → Cài đặt
```

Các trường:

| Trường | Nội dung |
|---|---|
| Enabled | Bật |
| Recipient | Email quản trị viên đã cấu hình |
| Subject | `[{site_title}] Bạn có một đơn hàng mới: #{order_number}` |
| Email heading | `Đơn hàng mới: #{order_number}` |
| Additional content | `Shop Đồng Hồ vừa nhận được một đơn hàng mới. Vui lòng kiểm tra thông tin sản phẩm, địa chỉ giao hàng và tiến hành xử lý đơn hàng.` |
| Email type | HTML |
| Cc/Bcc | Để trống nếu không có nhu cầu |

Tên các biến giữ nguyên dạng `{site_title}` / `{order_number}` để WooCommerce thay giá trị ở thời điểm gửi.

Sau khi nhập, nhấn **Lưu thay đổi**.

Xem Preview và nhấn **Gửi email thử nghiệm**.

**Minh chứng:** Hình 27–28.

---

# 36. Email Template 2 – Processing Order (xác nhận cho khách)

Đi đến:

```text
WooCommerce → Cài đặt → Email
→ Đang xử lý đơn hàng (Processing Order) → Cài đặt
```

Các trường:

| Trường | Nội dung |
|---|---|
| Enabled | Bật |
| Subject | `[{site_title}] Xác nhận đơn hàng #{order_number}` (đề xuất; trong một ảnh chụp trường Subject vẫn giữ mặc định) |
| Email heading | `Cảm ơn bạn đã đặt hàng tại Shop Đồng Hồ!` |
| Additional content | `Đơn hàng của bạn đã được ghi nhận và đang được xử lý. Shop Đồng Hồ sẽ chuẩn bị sản phẩm để giao đến bạn trong thời gian sớm nhất. Cảm ơn bạn đã tin tưởng và lựa chọn chúng tôi!` |
| Email type | HTML |
| Cc/Bcc | Để trống nếu không cần |

Chọn **Lưu thay đổi**. Sau đó kiểm tra Preview và gửi email thử nghiệm.

**Phân biệt với New Order:** Processing Order dành cho khách; New Order dành cho nhân viên/quản trị viên.

**Minh chứng:** Hình 29–30.

---

# 37. Kiểm thử hai mẫu email ở Gmail

Quy trình:

1. Mở email **New Order** trong danh sách.
2. Chọn **Gửi email thử nghiệm**.
3. Mở Gmail kiểm tra thư **Đơn hàng mới**.
4. Quay về phần **Processing Order**.
5. Gửi email thử nghiệm một lần nữa.
6. Mở Gmail kiểm tra thư **Cảm ơn bạn đã đặt hàng**.

Các email thử nghiệm từ trình Preview chứa dữ liệu **giả lập**:

```text
Mã đơn: #12345
Khách hàng: John Doe
Tổng tiền: 80 USD
Sản phẩm mẫu: một số sản phẩm tải xuống / biến thể giả
```

Điều này **không có nghĩa website tự đổi tiền VND thành USD**; đó là dữ liệu thử của WooCommerce.

Các ảnh chụp Gmail cho thấy hai thư đến Inbox và có giao diện HTML đồng bộ bảng màu.

---

# 38. Kiểm tra email thực tế của đơn hàng #1041

Khác với hai thư demo `#12345`, đơn #1041 đã phát sinh từ một luồng COD thực tế ở localhost.

Email đã nhận trong Gmail có:

```text
Thông tin khách hàng
Thông báo nhận đơn
Mã đơn: #1041
Tên sản phẩm đồng hồ Aikon #Tide
Giá: 4.212.000đ
Ship: 30.000đ
Tổng: 4.242.000đ
Thanh toán COD
Địa chỉ người mua / người nhận
```

Điều này chứng minh **ít nhất một email giao dịch WooCommerce đã được gửi và nhận thành công**.

Nếu muốn gửi lại email từ dashboard:

```text
WooCommerce → Đơn hàng → #1041
→ Hành động đơn hàng (Order actions)
→ Chọn thao tác gửi email phù hợp
```

Thao tác resend chỉ nên làm nếu thật sự cần thêm minh chứng; **không cần tạo đơn hàng mới**.

**Minh chứng:** Hình 31.

---

# 39. Vì sao ảnh sản phẩm trong email có thể bị vỡ?

WordPress chạy tại:

```text
http://localhost:8080
```

Trong khi Gmail được mở qua dịch vụ bên ngoài. URL `localhost` chỉ trỏ tới **máy đang mở liên kết**, nên Gmail không thể truy cập ảnh nội bộ của máy dựng WordPress như một website được public.

Ngoài ra, dữ liệu Preview còn có sản phẩm mẫu không có ảnh hợp lệ.

Cách kiểm tra nguyên nhân:

1. Mở thư Gmail.
2. Kiểm tra URL ảnh bị lỗi nếu cần (không công khai token của email).
3. Nếu URL là `localhost` hoặc ảnh mẫu giả, lỗi tải hình là điều dễ hiểu.
4. Khi triển khai production với tên miền và HTTPS công khai, sử dụng URL ảnh mà dịch vụ email có thể truy cập.

**Kết luận:** Ảnh hỏng **không phủ nhận việc email đã đến**. Tuy nhiên, chất lượng hiển thị ảnh phải kiểm thử lại nếu shop được đưa lên Internet.

---

# 40. Phân biệt cấu hình email với sửa template bằng code

Lab 02 đã tùy chỉnh hai email bằng công cụ cấu hình WooCommerce:

```text
WooCommerce → Settings → Emails
```

Các phần đã thay đổi: bật email, subject/heading, additional content, font, màu và footer dùng chung.

Trong ca thực hành này **không có bằng chứng cần override file HTML template cụ thể** như:

```text
flatsome-child/woocommerce/emails/admin-new-order.php
flatsome-child/woocommerce/emails/customer-processing-order.php
```

Do đó **không tạo/đè** các file template này chỉ để ghi rằng đã làm đúng yêu cầu. Nếu giảng viên bắt buộc yêu cầu tùy chỉnh markup HTML riêng, cần làm và kiểm thử bổ sung theo rubric gốc.

---

# 41. Kiểm tra `functions.php` / `style.css` an toàn

Hai file chính:

```text
wordpress/wp-content/themes/flatsome-child/functions.php
wordpress/wp-content/themes/flatsome-child/style.css
```

**Cấu trúc `functions.php`:**

```php
<?php
// Add custom theme functions here.
```

Dòng đầu phải bắt đầu từ `<?php`; không có ký tự trắng trước đó. Đối với file PHP chỉ chứa PHP, nên bỏ closing tag `?>` ở cuối để tránh output ngoài ý muốn.

**Cấu trúc `style.css`:**

```css
/*
Theme Name: Flatsome Child
Template: flatsome
*/

/* Custom CSS below */
```

Trường `Template: flatsome` giúp WordPress nhận dạng quan hệ child–parent. Không xóa phần khai báo theme header.

Kiểm tra PHP lint:

```powershell
docker exec ec312_wordpress php -l /var/www/html/wp-content/themes/flatsome-child/functions.php
```

Kiểm tra file đã được WordPress phục vụ đúng:

```text
F12 → Network → CSS
```

Khi nghi ngờ CSS chưa áp dụng, thử refresh mạnh `Ctrl+F5`, Inspect phần tử và kiểm tra stylesheet/Computed. **Không giải quyết bằng cách dán hàng chục rule trùng nhau.**

---

# 42. Những lỗi khác từng gặp trong Lab 02

## Lỗi A – `[ux_text]` xuất hiện thành chữ trên website

Nguyên nhân thường gặp: shortcode Flatsome chưa được render đúng trong editor, cấu trúc thẻ thiếu/mất dấu đóng, hoặc dán shortcode sai nơi.

Cách xử lý: mở trang bằng editor tương thích, xem code gốc, sửa đúng shortcode, preview và kiểm tra ngoài frontend.

## Lỗi B – Nút ĐẶT HÀNG không đổi màu

Có thể do CSS selector không trúng hoặc Flatsome đang ghi đè. Dùng DevTools → Elements → Computed để tìm rule cuối cùng trước khi sửa CSS.

## Lỗi C – Checkout cứ hiện spinner

Xem các mục 30–32. Trong lần đã debug, ký tự trắng đầu `functions.php` làm AJAX trả header JSON sai.

## Lỗi D – Email demo hiện USD / John Doe

Đây là dữ liệu mẫu của trình xem trước, không phải đơn hàng thực. Xác nhận bằng email #1041.

## Lỗi E – Email có ảnh hỏng

Thường gặp khi URL trỏ `localhost` hoặc ảnh demo không công khai. Xem mục 39.

## Lỗi F – CSS thay thế làm mất giao diện cũ

Không `Ctrl+A` rồi thay toàn bộ `style.css` nếu không có backup bản cuối. Child theme chứa CSS của nhiều trang, không riêng Checkout.

---

# 43. Lệnh Docker thường dùng ở Lab 02

Chạy project:

```powershell
cd C:\Users\Admin\Desktop\ec312-lab1
docker compose up -d
```

Xem trạng thái:

```powershell
docker compose ps
```

Khởi động lại một service khi thật sự cần:

```powershell
docker compose restart wordpress
```

Xem log:

```powershell
docker logs --tail 100 ec312_wordpress
docker logs --tail 100 ec312_nginx
docker logs --tail 100 ec312_mysql
```

Kiểm tra syntax PHP:

```powershell
docker exec ec312_wordpress php -l /var/www/html/wp-content/themes/flatsome-child/functions.php
```

Kiểm tra cấu hình Nginx:

```powershell
docker exec ec312_nginx nginx -t
```

Dừng container (không xóa volume):

```powershell
docker compose down
```

**Ghi nhớ:** với mã PHP bind mount, thay đổi file trên Windows thường được PHP đọc ở request mới; không mặc định bắt buộc restart Docker sau mỗi lần sửa CSS/PHP.

---

# 44. Những dữ liệu phải backup nếu muốn chuyển máy

Phải bảo quản đồng thời:

| Thành phần | Vị trí | Vai trò |
|---|---|---|
| WordPress core và `wp-content` | `wordpress/` | Theme, plugin, ảnh upload |
| Child theme PHP/CSS | `wp-content/themes/flatsome-child/` | Giao diện và hooks tùy chỉnh |
| Tệp Loco Translate | `wp-content/languages/loco/` | Bản dịch custom |
| Database MySQL | Docker named volume / SQL backup | Trang, sản phẩm, đơn, menu, thiết lập |
| Docker/Nginx config | Root project + `nginx/` | Khởi động hệ thống |
| OAuth/SMTP connection | Thiết lập riêng, bảo mật | Gửi email (có thể cần xác thực lại khi chuyển môi trường) |

**Không đủ:** chỉ copy hai file `functions.php` và `style.css` sẽ **không** phục dựng toàn bộ pages, menus, ảnh và WooCommerce products từ database.

---

# 45. Ảnh minh chứng theo đúng thứ tự file Word FINAL

Nếu cần nộp kèm báo cáo, xem `EC312_Lab02_Bao_cao_FINAL_Loco_Translate.docx`:

| Hạng mục | Hình trong báo cáo |
|---|---|
| 1. Việt hóa | 01 Site Language; 02 Menu; 03 Header; 04 Loco kích hoạt; 05 WooCommerce vi.po; 06–07 Flatsome po; 08 Checkout |
| 2. Cài đặt cơ bản | 09 Địa chỉ; 10 VND; 11 Shipping; 12 Payment; 13 Checkout COD; 14 Order Complete |
| 3. Mail server | 15 Gmail/Google cấp quyền; 16 WP Mail SMTP gửi thử; 17 Gmail nhận thư |
| 4. Tùy chỉnh Page | 18–19 Homepage; 20 Shop; 21 Product; 22 Cart; 23 Checkout; 24 Order Complete |
| 5. Email Template | 25 Danh sách Email; 26 Bảng màu; 27–28 New Order; 29–30 Processing Order; 31 Email thật #1041 |

Đây là **chỉ mục dẫn chiếu hình ảnh**, không phải hình nhúng trong Markdown. File `.md` có thể dùng độc lập để thao tác; Word FINAL là bộ ảnh minh chứng đi kèm.

---

# 46. Checklist cuối Lab 02

## 1. Việt hóa shop – 2 điểm

- [x] Site Language được đặt thành Tiếng Việt.
- [x] Main Menu hiển thị Trang chủ, Cửa hàng, Liên hệ.
- [x] Đã cài/activate Loco Translate.
- [x] Đã kiểm tra bộ dịch WooCommerce `woocommerce-vi.po`.
- [x] Đã tạo bộ dịch tùy chỉnh Flatsome `flatsome-vi.po`.
- [ ] Bản dịch Flatsome 115 chuỗi chưa hoàn tất theo ảnh chụp (0% tại thời điểm ghi nhận).
- [ ] Một số tên/nhãn demo vẫn ở tiếng Anh.

## 2. Cài đặt thông tin cơ bản – 2 điểm

- [x] Cửa hàng có địa chỉ TP.HCM, Việt Nam.
- [x] Cửa hàng dùng VND, 0 số lẻ.
- [x] Có shipping zone Vietnam.
- [x] Giao hàng tiêu chuẩn 30.000đ hoạt động trong ca kiểm thử.
- [x] COD hoạt động trong ca kiểm thử.

## 3. Mail server – 2 điểm

- [x] WP Mail SMTP kết nối Gmail/Google.
- [x] Gửi email thử nghiệm thành công.
- [x] Gmail nhận email thử.
- [x] Gmail nhận email WooCommerce thực tế từ đơn #1041.

## 4. Tùy chỉnh Page – 2 điểm

- [x] Homepage.
- [x] Shop/Product Catalog.
- [x] Product Details.
- [x] Cart.
- [x] Checkout.
- [x] Order Complete.
- [x] Đơn COD #1041 được tạo thành công.

## 5. Email Template – 2 điểm

- [x] New Order được chỉnh tiêu đề/nội dung và kiểm tra Gmail.
- [x] Processing Order được chỉnh tiêu đề/nội dung và kiểm tra Gmail.
- [x] Email chung sử dụng navy/champagne.
- [x] Đã phân biệt email preview `#12345` và đơn thật `#1041`.
- [ ] Chưa chứng minh HTML template override riêng nếu rubric thầy bắt buộc yêu cầu điều đó.

**Kết luận học tập:** Năm hạng mục đã có nội dung triển khai và ảnh tương ứng; một số phần Việt hóa chưa tuyệt đối hoàn tất. Việc tự chấm đủ 10/10 cần căn cứ thêm thang chấm của giảng viên.

---

# 47. Flow Lab 02 rút gọn để làm lại nhanh

```text
BƯỚC 1: Mở project ec312-lab1, docker compose up -d
    ↓
BƯỚC 2: Chọn tiếng Việt trong WordPress
    ↓
BƯỚC 3: Cài Loco Translate, kiểm tra Woo vi.po,
         tạo tùy chỉnh Flatsome vi.po
    ↓
BƯỚC 4: Đổi Main Menu thành Trang chủ / Cửa hàng / Liên hệ
    ↓
BƯỚC 5: WooCommerce General: địa chỉ TP.HCM, VND
    ↓
BƯỚC 6: Shipping zone Vietnam, Flat rate 30.000đ
    ↓
BƯỚC 7: Payments: COD
    ↓
BƯỚC 8: Kết nối WP Mail SMTP với Gmail và gửi email test
    ↓
BƯỚC 9: Tùy chỉnh Flatsome Child: Homepage, Shop,
         Product, Cart, Checkout, Order Complete, Footer
    ↓
BƯỚC 10: Test đặt hàng COD → Order Complete
    ↓
BƯỚC 11: WooCommerce Emails: chỉnh New Order,
          Processing Order, màu email, gửi thư test
    ↓
BƯỚC 12: Kiểm tra Gmail, đối chiếu đơn #1041,
          lưu ảnh minh chứng và backup project
```

---

# 48. Ghi nhớ quan trọng nhất khi làm tiếp Lab 03

1. **Lab 02 kế thừa Lab 01**, không reset cơ sở dữ liệu và không nhập lại sản phẩm nếu không có yêu cầu.
2. **Flatsome Child** là nơi giữ code trang trí và các hooks tùy chỉnh; WordPress database giữ nội dung trang và cài đặt.
3. **Loco Translate** mới chỉ được xác nhận cài đặt, theo dõi gói WooCommerce vi.po và khởi tạo bản dịch Flatsome; không nên báo cáo Flatsome 100% dịch.
4. **Checkout spinner** từng do output sớm trước `<?php` trong `functions.php`; phải đảm bảo PHP và JSON response sạch.
5. **Không ẩn spinner bằng JS/CSS** thay cho xử lý lỗi server.
6. **Email template** và **SMTP mailer** là hai cấu hình khác nhau; cả hai đều đã được kiểm thử theo phạm vi Lab.
7. **Email `#12345` là giả lập**; email đơn **#1041 là minh chứng thực tế**.
8. **Ảnh email bị lỗi** có thể do WordPress chỉ chạy localhost; kiểm tra lại khi deploy public.
9. **Không dán trùng các code snippets** từ tài liệu này vào theme đang chạy.
10. **Backup database + uploads + child theme**, rồi mới thực hiện thay đổi lớn cho Lab kế tiếp.

---

**Kết thúc hướng dẫn Lab 02.**  
*Tài liệu này dùng để đọc lại, làm lại thao tác, giải trình khi chấm Lab và bàn giao tiến độ sang Lab tiếp theo.*
