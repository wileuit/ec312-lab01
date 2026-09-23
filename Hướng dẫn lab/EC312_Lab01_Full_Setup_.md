# EC312 – Lab 01
# Full setup, câu lệnh, cấu hình và lưu ý để dựng WordPress + WooCommerce + Flatsome bằng Docker

**Sinh viên:** Lê Hiếu Huy  
**MSSV:** 24520666  
**Lab:** Lab01  
**Môi trường:** Windows + Docker Desktop + WordPress + WooCommerce + Flatsome + MySQL 8.0 + Nginx + PHP-FPM

---

# 1. Mục tiêu cuối cùng của Lab 01

Hoàn thành website thương mại điện tử local với các yêu cầu:

1. Cài WordPress + WooCommerce
2. Cài Flatsome Theme
3. Cấu hình:
   - Store name
   - Store address
   - Shipping service
   - Payment method
4. Thêm 1 sản phẩm thủ công
5. Import danh sách sản phẩm bằng file
6. Cập nhật giá + số lượng bằng giao diện
7. Import file để cập nhật giá + số lượng hàng loạt

Stack thực tế dùng:

```text
Docker Desktop
├── Nginx
├── WordPress PHP-FPM
└── MySQL 8.0
```

---

# 2. Cấu trúc thư mục project

Project đặt tại:

```text
C:\Users\Admin\Desktop\ec312-lab1
```

Cấu trúc:

```text
ec312-lab1/
├── docker-compose.yml
├── php.ini
├── nginx/
│   └── default.conf
└── wordpress/
    ├── wp-admin/
    ├── wp-content/
    │   ├── plugins/
    │   └── themes/
    └── wp-includes/
```

---

# 3. Tạo project

Mở PowerShell:

```powershell
cd C:\Users\Admin\Desktop
mkdir ec312-lab1
cd ec312-lab1
```

---

# 4. File `docker-compose.yml`

Tạo file:

```text
C:\Users\Admin\Desktop\ec312-lab1\docker-compose.yml
```

Nội dung:

```yaml
services:

  db:
    image: mysql:8.0
    container_name: ec312_mysql
    restart: unless-stopped

    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: root

    volumes:
      - db_data:/var/lib/mysql

    networks:
      - wordpress_network


  wordpress:
    image: wordpress:php8.2-fpm
    container_name: ec312_wordpress
    restart: unless-stopped

    depends_on:
      - db

    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress

    volumes:
      - ./wordpress:/var/www/html
      - ./php.ini:/usr/local/etc/php/conf.d/uploads.ini

    networks:
      - wordpress_network


  nginx:
    image: nginx:alpine
    container_name: ec312_nginx
    restart: unless-stopped

    depends_on:
      - wordpress

    ports:
      - "8080:80"

    volumes:
      - ./wordpress:/var/www/html
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf

    networks:
      - wordpress_network


volumes:
  db_data:


networks:
  wordpress_network:
```

---

# 5. Database đang dùng

Database server:

```text
MySQL 8.0
```

Thông tin:

```text
Database name: wordpress
Database user: wordpress
Database password: wordpress
Root password: root
Container name: ec312_mysql
Internal port: 3306
```

WordPress kết nối bằng:

```text
WORDPRESS_DB_HOST=db:3306
WORDPRESS_DB_USER=wordpress
WORDPRESS_DB_PASSWORD=wordpress
WORDPRESS_DB_NAME=wordpress
```

## Lưu ý

- Không cần cài MySQL trực tiếp trên Windows.
- Không bắt buộc cài phpMyAdmin.
- MySQL chạy trong container Docker.
- phpMyAdmin chỉ là giao diện quản lý database, không phải database itself.

---

# 6. File `php.ini`

Tạo file:

```text
C:\Users\Admin\Desktop\ec312-lab1\php.ini
```

Nội dung:

```ini
upload_max_filesize = 128M
post_max_size = 128M
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
```

Mục đích:

- Cho phép upload Flatsome ZIP dung lượng lớn
- Tránh lỗi upload file quá size
- Tăng thời gian xử lý plugin/theme
- Tăng memory khi WordPress giải nén hoặc import dữ liệu

---

# 7. File cấu hình Nginx

Tạo:

```text
C:\Users\Admin\Desktop\ec312-lab1\nginx\default.conf
```

Nội dung:

```nginx
server {
    listen 80;
    server_name localhost;

    root /var/www/html;
    index index.php index.html index.htm;

    client_max_body_size 128M;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;

        fastcgi_pass wordpress:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Vì sao phải tăng timeout?

Khi cài WooCommerce bằng upload trong WordPress từng gặp:

```text
upstream timed out
```

hoặc:

```text
cURL error 28
Connection timed out
```

Nên tăng timeout Nginx để tránh request PHP bị cắt quá sớm.

---

# 8. Khởi động Docker

Chạy:

```powershell
docker compose up -d
```

Kiểm tra:

```powershell
docker ps
```

Hoặc:

```powershell
docker ps -a
```

Phải thấy:

```text
ec312_nginx
ec312_wordpress
ec312_mysql
```

Ví dụ:

```text
nginx:alpine
wordpress:php8.2-fpm
mysql:8.0
```

---

# 9. Test Nginx config

```powershell
docker exec ec312_nginx nginx -t
```

Kết quả đúng:

```text
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

---

# 10. Restart toàn bộ hệ thống sau khi sửa config

```powershell
docker compose down
docker compose up -d
```

## Lưu ý

`docker compose down` không xóa volume database nếu không thêm `-v`.

Không dùng:

```powershell
docker compose down -v
```

nếu không muốn mất database.

---

# 11. Cài WordPress

Truy cập:

```text
http://localhost:8080
```

Thiết lập ban đầu:

```text
Language: English
Site Title: Shop Đồng Hồ
Username: admin
Password: tự đặt
Email: email cá nhân
```

Đăng nhập admin:

```text
http://localhost:8080/wp-admin
```

---

# 12. Kiểm tra PHP upload limit

Chạy:

```powershell
docker exec -it ec312_wordpress php -i | findstr "upload_max_filesize post_max_size"
```

Kết quả mong đợi:

```text
post_max_size => 128M => 128M
upload_max_filesize => 128M => 128M
```

Nếu chưa đúng:

1. kiểm tra `php.ini`
2. kiểm tra mount trong `docker-compose.yml`
3. restart container

```powershell
docker compose down
docker compose up -d
```

---

# 13. Cài Flatsome Theme

Vào:

```text
Appearance
→ Themes
→ Add New
→ Upload Theme
```

Chọn:

```text
flatsome.zip
```

Sau đó:

```text
Install Now
→ Activate
```

Nếu gặp lỗi size:

- kiểm tra `php.ini`
- kiểm tra `client_max_body_size 128M`
- restart Docker

---

# 14. Cài WooCommerce – cách đã thành công

## Quan trọng

Không dùng:

```text
Plugins → Add Plugin → Upload Plugin
```

trong case này.

Lý do: upload trực tiếp WooCommerce qua WordPress đã gặp timeout / giải nén dở dang.

## Cách ổn định nhất

### Bước 1: tải file WooCommerce ZIP

Tải:

```text
woocommerce.zip
```

### Bước 2: giải nén trên Windows

Giải nén thành:

```text
woocommerce/
```

Bên trong phải có:

```text
woocommerce.php
includes/
assets/
src/
vendor/
...
```

### Bước 3: copy thư mục vào plugin directory

Copy vào:

```text
C:\Users\Admin\Desktop\ec312-lab1\wordpress\wp-content\plugins\woocommerce
```

Cấu trúc đúng:

```text
wordpress/
└── wp-content/
    └── plugins/
        └── woocommerce/
            ├── woocommerce.php
            ├── includes/
            ├── assets/
            ├── src/
            └── vendor/
```

### Bước 4: activate

Vào:

```text
Plugins
→ Installed Plugins
→ WooCommerce
→ Activate
```

Nếu dòng WooCommerce hiện:

```text
Deactivate
```

thì plugin đang Active.

---

# 15. Trường hợp WooCommerce từng bị giải nén lỗi

Đã từng gặp folder:

```text
wp-content/plugins/woocommerce
```

nhưng bên trong chỉ có:

```text
assets/
```

=> nghĩa là cài dở dang.

Cách xóa:

```powershell
docker exec -it ec312_wordpress bash
```

Trong container:

```bash
rm -rf /var/www/html/wp-content/plugins/woocommerce
exit
```

Sau đó giải nén thủ công trên Windows và copy lại folder đầy đủ.

---

# 16. Kiểm tra permission của `wp-content`

Nếu WordPress không thể ghi file:

```powershell
docker exec -it ec312_wordpress bash
```

Trong container:

```bash
chown -R www-data:www-data /var/www/html/wp-content
chmod -R 775 /var/www/html/wp-content
```

Thoát:

```bash
exit
```

Có thể restart:

```powershell
docker compose restart
```

---

# 17. Kiểm tra internet trong container WordPress

Vào container:

```powershell
docker exec -it ec312_wordpress bash
```

Test:

```bash
curl -I https://wordpress.org
```

Test thêm:

```bash
curl -I https://downloads.wordpress.org
```

Nếu có HTTP response thì container có internet.

Ví dụ:

```text
HTTP/2 200
```

hoặc:

```text
HTTP/2 302
```

---

# 18. Xem log khi có lỗi

WordPress/PHP:

```powershell
docker logs --tail 100 ec312_wordpress
```

Nginx:

```powershell
docker logs --tail 100 ec312_nginx
```

Dùng khi gặp:

```text
Installation failed
White screen
Timeout
502 Bad Gateway
upstream timed out
```

---

# 19. Setup WooCommerce cơ bản

Sau khi Activate WooCommerce:

```text
Welcome to Woo!
```

Chọn:

```text
Skip guided setup
```

để tự cấu hình theo yêu cầu lab.

---

# 20. Cấu hình Store Name

Vào:

```text
Settings
→ General
```

Điền:

```text
Site Title: Shop Đồng Hồ
```

---

# 21. Cấu hình Store Address

Vào:

```text
WooCommerce
→ Settings
→ General
```

Ví dụ:

```text
Address line 1: Khu phố 6, Phường Linh Trung
Address line 2: để trống
City: Ho Chi Minh City
Country / State: Vietnam
Postcode / ZIP: 700000
```

General options:

```text
Selling location(s): Sell to all countries
Shipping location(s): Ship to all countries you sell to
Default customer location: Shop country/region
```

---

# 22. Cấu hình Shipping

Vào:

```text
WooCommerce
→ Settings
→ Shipping
```

Tạo Shipping Zone:

```text
Zone name: Vietnam
Zone regions: Vietnam
```

Shipping method:

```text
Flat rate
```

Thiết lập:

```text
Method title: Giao hàng tiêu chuẩn
Tax status: None
Cost: 30000
```

Kết quả:

```text
Vietnam
→ Giao hàng tiêu chuẩn
```

---

# 23. Cấu hình Payment

Vào:

```text
WooCommerce
→ Settings
→ Payments
```

Bật:

```text
Cash on delivery
```

Cấu hình:

```text
Title: Thanh toán khi nhận hàng (COD)
Description: Thanh toán bằng tiền mặt khi nhận hàng.
Instructions: Vui lòng chuẩn bị tiền mặt khi nhận hàng.
```

Nếu thấy nút:

```text
Manage
```

thì COD đang active.

---

# 24. Thêm 1 sản phẩm thủ công

Vào:

```text
Products
→ Add New Product
```

Ví dụ:

```text
Product name: Đồng hồ Casio MTP-V005L
```

Description:

```text
Đồng hồ nam thiết kế tối giản, dây da, phù hợp sử dụng hằng ngày.
```

Product type:

```text
Simple product
```

General:

```text
Regular price: 850000
Sale price: 750000
```

Inventory:

```text
SKU: CASIO001
Manage stock: bật
Stock quantity: 10
Stock status: In stock
```

Shipping:

```text
Weight: 0.2
```

Category:

```text
Đồng hồ nam
```

Cuối cùng:

```text
Publish
```

Kiểm tra:

```text
Products
→ All Products
```

---

# 25. Import danh sách sản phẩm ban đầu

File giảng viên:

```text
woo.jomashop20200408.txt
```

Vào:

```text
Products
→ All Products
→ Import
```

Chọn file.

Advanced options:

```text
CSV Delimiter: @
Character encoding: Autodetect
Update existing products: KHÔNG tick
Use previous column mapping preferences: KHÔNG tick
```

## Vì sao dùng `@`?

File của giảng viên sử dụng `@` làm delimiter thay vì dấu phẩy.

Một số field như Description có thể chứa dấu phẩy nên dùng `@` tránh tách sai cột.

---

# 26. Mapping import sản phẩm

Các field quan trọng:

```text
ID → ID
Type → Type
SKU → SKU
Name → Name
Published → Published
Visibility in catalog → Visibility in catalog
Short description → Short description
Description → Description
Sale price → Sale price
Regular price → Regular price
In stock? → In stock?
Stock → Stock
Weight(kg) → Weight (kg)
Length(cm) → Length (cm)
Width(cm) → Width (cm)
Height(cm) → Height (cm)
Categories → Categories
Images → Images
Allow customer reviews? → Allow customer reviews?
```

Attributes có thể để WooCommerce auto-map nếu đã nhận đúng.

Sau đó:

```text
Run the importer
```

Không refresh / đóng tab khi đang import.

---

# 27. Kiểm tra import sản phẩm thành công

Vào:

```text
Products
→ All Products
```

Nếu thấy nhiều sản phẩm mới có:

```text
Name
SKU
Stock
Price
Categories
Published
```

=> import thành công.

---

# 28. Update giá + stock bằng giao diện

Vào:

```text
Products
→ All Products
→ Quick Edit
```

Ví dụ đã làm:

```text
Product: Seiko Chronograph Men's Watch SE-SND255
SKU: SE-SND255
```

Thay đổi:

```text
Price
Sale price
Manage stock: tick
Stock qty: nhập số mới
```

Ví dụ:

```text
Price: 9070000
Sale: 3660000
Stock qty: 3636
```

Sau đó:

```text
Update
```

Kiểm tra lại trong All Products:

```text
In stock (3636)
Sale price: 3.660.000đ
```

---

# 29. Kiểm tra kết quả ngoài frontend

Mở sản phẩm.

Ví dụ:

```text
Seiko Chronograph Men’s Watch SE-SND255
```

Frontend hiển thị:

```text
Regular price: 9.070.000đ
Sale price: 3.660.000đ
Stock: 3636 in stock
```

=> chứng minh dữ liệu update đã áp dụng thực tế.

---

# 30. Export danh sách sản phẩm để update hàng loạt

Vào:

```text
Products
→ All Products
→ Export
```

Export file CSV.

Tên file kiểu:

```text
wc-product-export-24-9-2026-....csv
```

Mở bằng Excel.

---

# 31. Chỉnh file CSV bằng Excel

Các cột quan trọng:

```text
ID
SKU
Name
Regular price
Sale price
Stock
```

Có thể sửa các giá trị Stock như:

```text
111
999
788
666
555
444
...
```

và sửa giá nếu cần.

Save lại dưới dạng CSV.

---

# 32. Import file CSV đã sửa để cập nhật sản phẩm

Vào:

```text
Products
→ Import
```

Chọn file CSV export đã chỉnh.

## Quan trọng

File export từ WooCommerce dùng delimiter:

```text
,
```

KHÔNG dùng:

```text
@
```

Cấu hình:

```text
Update existing products: TICK
CSV Delimiter: ,
Use previous column mapping preferences: KHÔNG tick
Character encoding: Autodetect
```

---

# 33. Mapping khi update bằng CSV

Các field quan trọng:

```text
ID → ID
SKU → SKU
Regular price → Regular price
Sale price → Sale price
Stock → Stock
```

Sau đó:

```text
Run the importer
```

Kết quả đã đạt:

```text
Import complete: 106 products updated
```

---

# 34. Phân biệt delimiter – cực kỳ quan trọng

## File TXT của giảng viên

```text
Delimiter = @
```

## File CSV export từ WooCommerce

```text
Delimiter = ,
```

Nếu dùng sai delimiter, WooCommerce có thể đọc cả dòng thành 1 cột hoặc map sai dữ liệu.

---

# 35. Flatsome – chỉnh giao diện

## Chỉnh tổng thể

```text
Appearance
→ Customize
```

Có thể chỉnh:

```text
Logo
Header
Footer
Font
Color
Menu
```

## Chỉnh trang bằng UX Builder

```text
Pages
→ All Pages
→ Home
→ Edit with UX Builder
```

Có thể chỉnh:

```text
Banner
Section
Button
Text
Images
Product section
Categories
Spacing
Background
```

---

# 36. Các URL cần nhớ

Frontend:

```text
http://localhost:8080
```

Admin:

```text
http://localhost:8080/wp-admin
```

---

# 37. Các câu lệnh Docker quan trọng

## Start

```powershell
docker compose up -d
```

## Stop + remove container/network

```powershell
docker compose down
```

## Restart

```powershell
docker compose restart
```

## Xem container

```powershell
docker ps
```

```powershell
docker ps -a
```

## Xem log

```powershell
docker logs --tail 100 ec312_wordpress
```

```powershell
docker logs --tail 100 ec312_nginx
```

## Vào WordPress container

```powershell
docker exec -it ec312_wordpress bash
```

## Test Nginx

```powershell
docker exec ec312_nginx nginx -t
```

## Check PHP limit

```powershell
docker exec -it ec312_wordpress php -i | findstr "upload_max_filesize post_max_size"
```

---

# 38. Các câu lệnh Linux từng dùng trong container

## Fix permission

```bash
chown -R www-data:www-data /var/www/html/wp-content
chmod -R 775 /var/www/html/wp-content
```

## Xóa WooCommerce lỗi

```bash
rm -rf /var/www/html/wp-content/plugins/woocommerce
```

## Check plugin folder

```bash
ls -la /var/www/html/wp-content/plugins
```

```bash
ls -la /var/www/html/wp-content/plugins/woocommerce
```

## Test internet

```bash
curl -I https://wordpress.org
```

```bash
curl -I https://downloads.wordpress.org
```

## Thoát container

```bash
exit
```

---

# 39. Các lỗi đã gặp và cách xử lý

## Lỗi 1: WooCommerce download timeout

Ví dụ:

```text
Installation failed: Download failed.
cURL error 28: Connection timed out
```

Cách xử lý hiệu quả:

```text
Không cài WooCommerce qua online installer.
Tải woocommerce.zip
→ giải nén trên Windows
→ copy vào wp-content/plugins/woocommerce
→ Activate
```

---

## Lỗi 2: WooCommerce upload xong trắng trang

Nguyên nhân:

```text
PHP/Nginx timeout trong lúc giải nén.
```

Kiểm tra:

```powershell
docker logs --tail 100 ec312_wordpress
docker logs --tail 100 ec312_nginx
```

Cách tránh:

```text
Giải nén plugin thủ công trên Windows.
```

---

## Lỗi 3: WooCommerce folder chỉ có `assets`

Nguyên nhân:

```text
Plugin giải nén dở dang.
```

Xóa:

```bash
rm -rf /var/www/html/wp-content/plugins/woocommerce
```

Sau đó copy lại folder WooCommerce hoàn chỉnh.

---

## Lỗi 4: Theme/plugin quá dung lượng

Kiểm tra:

```powershell
docker exec -it ec312_wordpress php -i | findstr "upload_max_filesize post_max_size"
```

Đảm bảo:

```text
128M
```

Nginx:

```nginx
client_max_body_size 128M;
```

---

## Lỗi 5: WordPress không ghi được file

Fix:

```bash
chown -R www-data:www-data /var/www/html/wp-content
chmod -R 775 /var/www/html/wp-content
```

---

## Lỗi 6: Import CSV sai cột

Nguyên nhân thường là delimiter sai.

Nhớ:

```text
File thầy .txt → @
WooCommerce export .csv → ,
```

---

# 40. Lưu ý về Docker volume và dữ liệu

Database dùng named volume:

```text
db_data
```

Vì vậy:

```powershell
docker compose down
docker compose up -d
```

thường không mất database.

Nhưng:

```powershell
docker compose down -v
```

sẽ xóa volume và có thể làm mất database.

Không dùng `-v` nếu không thật sự muốn reset toàn bộ.

---

# 41. Lưu ý về bind mount WordPress

WordPress được mount:

```yaml
- ./wordpress:/var/www/html
```

Nghĩa là:

```text
C:\Users\Admin\Desktop\ec312-lab1\wordpress
```

chính là thư mục WordPress thật đang dùng trong container.

Do đó có thể copy thủ công:

```text
plugins
themes
uploads
```

từ Windows vào project.

---

# 42. Checklist cuối Lab 01

- [x] Docker chạy 3 container
- [x] Nginx hoạt động tại port 8080
- [x] MySQL 8.0 hoạt động
- [x] WordPress cài thành công
- [x] WooCommerce active
- [x] Flatsome active
- [x] Store name cấu hình
- [x] Store address cấu hình
- [x] Shipping cấu hình
- [x] Payment COD cấu hình
- [x] Thêm sản phẩm thủ công
- [x] Import danh sách sản phẩm
- [x] Update giá bằng giao diện
- [x] Update stock bằng giao diện
- [x] Export sản phẩm
- [x] Chỉnh CSV bằng Excel
- [x] Import CSV cập nhật
- [x] 106 products updated
- [x] Frontend hiển thị giá + tồn kho đúng
- [x] Báo cáo Lab01 hoàn thành

---

# 43. Tóm tắt flow hoàn chỉnh

```text
Docker Desktop
    ↓
docker compose up -d
    ↓
Nginx + PHP-FPM + MySQL
    ↓
WordPress
    ↓
Flatsome
    ↓
WooCommerce
    ↓
Shop Settings
    ↓
Manual Product
    ↓
Import Products
    ↓
Quick Edit Price/Stock
    ↓
Export CSV
    ↓
Edit CSV in Excel
    ↓
Import CSV with "Update existing products"
    ↓
106 products updated
    ↓
Frontend kiểm tra kết quả
    ↓
Report
```

---

# 44. Câu lệnh cứu mạng ngắn gọn

Nếu hệ thống có vấn đề, chạy lần lượt:

```powershell
cd C:\Users\Admin\Desktop\ec312-lab1
docker ps -a
docker compose down
docker compose up -d
docker exec ec312_nginx nginx -t
docker logs --tail 100 ec312_nginx
docker logs --tail 100 ec312_wordpress
```

Nếu WordPress không ghi được:

```powershell
docker exec -it ec312_wordpress bash
```

```bash
chown -R www-data:www-data /var/www/html/wp-content
chmod -R 775 /var/www/html/wp-content
exit
```

---

# 45. Ghi nhớ quan trọng nhất

1. **WooCommerce:** nếu upload/install online lỗi, giải nén thủ công vào `wp-content/plugins`.
2. **Flatsome:** cần tăng upload limit nếu ZIP lớn.
3. **TXT giảng viên:** delimiter `@`.
4. **CSV WooCommerce export:** delimiter `,`.
5. **Update hàng loạt:** phải tick `Update existing products`.
6. **Stock:** phải bật `Manage stock` nếu muốn có số lượng cụ thể.
7. **Không dùng `docker compose down -v`** nếu không muốn mất database.
8. **Luôn chụp kết quả sau cùng**, không chỉ chụp màn hình đang thao tác.
