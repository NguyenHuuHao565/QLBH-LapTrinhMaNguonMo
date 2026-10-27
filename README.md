# Bloomy Flower Shop

Website bán hoa tươi trực tuyến — đồ án môn **Lập trình mã nguồn mở**.

Khách hàng xem và tìm hoa, thêm vào giỏ, đặt hàng thanh toán khi nhận (COD) hoặc chuyển khoản qua mã QR, theo dõi và tự hủy đơn khi còn chờ xác nhận.
Quản trị viên quản lý danh mục, sản phẩm (kèm ảnh), đơn hàng, khách hàng và xem thống kê doanh thu.

**Công nghệ:** Laravel 13 · PHP 8.3 · MySQL 8.4 · Blade · Bootstrap 5 (CDN) · CSS · JavaScript thuần.
Không dùng React/Vue, không dùng package ngoài ngoài bộ cài mặc định của Laravel.

---

## 1. Yêu cầu môi trường

| Thành phần | Phiên bản đã kiểm tra |
|---|---|
| Laragon | 2026 v8.6.1 |
| PHP | 8.3.30 (của Laragon) — tối thiểu 8.3 |
| Composer | 2.8.x |
| MySQL | 8.4.3 (của Laragon) |
| Trình duyệt | Chrome / Edge bản mới (cần Internet để tải Bootstrap, Bootstrap Icons, Google Fonts từ CDN) |

Extension PHP cần có: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` (ảnh), `ctype`, `tokenizer`, `xml`.
Bản PHP của Laragon đã có đủ.

> **Quan trọng:** chạy mọi lệnh `php` / `composer` trong **Terminal của Laragon** (nút *Terminal*).
> Nếu máy có cài PHP khác (ví dụ Herd), terminal thường có thể dùng nhầm phiên bản. Kiểm tra bằng `php -v` → phải là **8.3.x**.

## 2. Cài đặt lần đầu

Mở **Terminal của Laragon**:

```bash
cd C:\laragon\www\QuanLyBanHoa
composer install
copy .env.example .env
php artisan key:generate
php artisan storage:link
```

`storage:link` tạo liên kết `public/storage` để hiển thị ảnh sản phẩm upload — **bắt buộc** mỗi khi chép dự án sang máy mới.

### 2.1. Cấu hình `.env`

```env
APP_NAME="Bloomy Flower Shop"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=vi

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quanlybanhoa
DB_USERNAME=root
DB_PASSWORD=

# Tài khoản demo cho seeder (để trống = sinh mật khẩu ngẫu nhiên)
SEED_ADMIN_EMAIL=admin@bloomy.test
SEED_ADMIN_PASSWORD=
SEED_CUSTOMER_EMAIL=khach@bloomy.test
SEED_CUSTOMER_PASSWORD=

# Thông tin liên hệ hiển thị ở footer (không bắt buộc)
SHOP_PHONE="0900 000 000"
SHOP_ADDRESS="140 Lê Trọng Tấn, Phường Tây Thạnh, Quận Tân Phú, TP. Hồ Chí Minh (Trường Đại học Công Thương TP.HCM)"
SHOP_EMAIL=lienhe@bloomy.test
```

Laragon mặc định MySQL dùng `root` không mật khẩu. Mật khẩu thật (nếu có) chỉ ghi trong `.env` —
file này nằm trong `.gitignore`, **không** đưa lên Git hay vào báo cáo.

### 2.2. Tạo database, migrations và dữ liệu mẫu

Tạo CSDL `quanlybanhoa` (utf8mb4) bằng HeidiSQL (nút *Cơ sở dữ liệu* của Laragon) hoặc lệnh:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS quanlybanhoa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Rồi chạy:

```bash
php artisan migrate --seed
```

Seeder tạo: 2 tài khoản demo, 6 danh mục, 55 sản phẩm hoa kèm ảnh thật (1 sản phẩm cố ý để tồn kho = 0 để minh họa “Hết hàng”).
Ảnh mẫu lấy từ Wikimedia Commons (giấy phép tự do) — nguồn và tác giả ghi trong `database/seeders/images/CREDITS.md`.

| Lệnh | Tác dụng |
|---|---|
| `php artisan migrate` | Chỉ chạy migration mới, **không** xóa dữ liệu |
| `php artisan db:seed` | Cập nhật danh mục/sản phẩm mẫu (không tạo trùng) và **đặt lại mật khẩu** 2 tài khoản demo |
| `php artisan db:seed --class=ProductSeeder` | Chỉ cập nhật sản phẩm mẫu |
| `php artisan migrate:fresh --seed` | ⚠️ **Xóa toàn bộ dữ liệu** rồi tạo lại — chỉ dùng khi muốn làm lại từ đầu |

## 3. Chạy website bằng Laragon

**Cách 1 — Apache của Laragon (khuyên dùng):** bấm **Bắt đầu** rồi **Tải lại** trên Laragon, mở:

- `http://quanlybanhoa.test:8000` (virtual host tự động; máy phát triển đặt Apache ở cổng **8000**), hoặc
- `http://localhost:8000/QuanLyBanHoa/public`

**Cách 2 — server tích hợp của Laravel:**

```bash
php artisan serve --port=8090
```

Mở `http://127.0.0.1:8090`. Không dùng cổng 8000 (trùng Apache) và 8080 (trên máy phát triển bị Windows chiếm).

## 4. Tài khoản demo

| Vai trò | Email |
|---|---|
| Quản trị (ADMIN) | `admin@bloomy.test` |
| Khách hàng (CUSTOMER) | `khach@bloomy.test` |

Mật khẩu **không viết cứng trong code**. Khi chạy `php artisan db:seed`:

- nếu `.env` có `SEED_ADMIN_PASSWORD` / `SEED_CUSTOMER_PASSWORD` → dùng giá trị đó;
- nếu để trống → seeder sinh mật khẩu ngẫu nhiên và **in ra màn hình một lần**.

Mật khẩu luôn được lưu dạng hash bcrypt. Khách mới có thể tự đăng ký tại `/dang-ky` (luôn là CUSTOMER).

## 5. Chức năng và phân quyền

| Chức năng | Khách vãng lai | CUSTOMER | ADMIN |
|---|:---:|:---:|:---:|
| Trang chủ, danh sách hoa, tìm kiếm, lọc danh mục, sắp xếp giá, phân trang | ✔ | ✔ | ✔ |
| Chi tiết sản phẩm, trạng thái còn/hết hàng | ✔ | ✔ | ✔ |
| Giỏ hàng (thêm, sửa số lượng, xóa) | ✔ | ✔ | ✔ |
| Đăng ký, đăng nhập | ✔ | — | — |
| Đặt hàng COD hoặc chuyển khoản (mã QR), trang xác nhận | | ✔ | ✔ |
| Tự hủy đơn khi còn *Chờ xác nhận* và chưa thanh toán (hoàn kho ngay) | | ✔ | ✔ |
| Trang Liên hệ có bản đồ (Leaflet + OpenStreetMap) | ✔ | ✔ | ✔ |
| Trang tài khoản, lịch sử & chi tiết **đơn của mình** | | ✔ | ✔ |
| Dashboard thống kê | | | ✔ |
| Quản lý đơn: tìm theo mã, lọc trạng thái/ngày, cập nhật trạng thái | | | ✔ |
| Quản lý khách hàng: thông tin, lịch sử mua | | | ✔ |
| CRUD danh mục, CRUD sản phẩm + upload ảnh, ngừng kinh doanh | | | ✔ |

**Quy tắc trạng thái đơn:** `Chờ xác nhận → Đã xác nhận → Đang giao → Hoàn thành`; từ 3 trạng thái đầu có thể
chuyển sang `Đã hủy`. `Hoàn thành` và `Đã hủy` là trạng thái cuối. Hủy đơn → hoàn lại tồn kho.
Hoàn thành → đơn COD được đánh dấu *Đã thanh toán*. Đơn chuyển khoản phải được admin bấm
*Xác nhận đã nhận chuyển khoản* trước khi được Hoàn thành.

**Quy tắc doanh thu:** tổng tiền các đơn **Hoàn thành**. Đơn **Đã hủy** không bao giờ được tính.

## 6. Cấu trúc MVC

```
app/
├── Models/                 User, Category, Product, Order, OrderItem (Eloquent + quan hệ)
├── Http/
│   ├── Controllers/        Home, Product, Cart, Checkout, Order, Account, Auth/{Login,Register}
│   │   └── Admin/          Dashboard, Category, Product, Order, Customer
│   ├── Requests/           CheckoutRequest, Admin/{CategoryRequest, ProductRequest} (validation)
│   └── Middleware/         EnsureUserIsAdmin (phân quyền), RejectInvalidUtf8
├── Policies/OrderPolicy    Chỉ chủ đơn hoặc ADMIN được xem đơn
├── Services/               Cart (giỏ hàng session), OrderPlacer (tạo đơn trong transaction),
│                           OrderStatusUpdater (chuyển trạng thái, hoàn kho)
└── Exceptions/             CheckoutException, OrderStatusException
database/migrations/        12 migration (bảng hệ thống Laravel + 5 bảng nghiệp vụ + bổ sung)
database/seeders/           UserSeeder, CategorySeeder, ProductSeeder
resources/views/
├── components/             layout, site-header, site-footer, product-card, product-image,
│                           price, flower-art (minh họa SVG), admin-layout, admin-order-status
├── home, products/, cart/, checkout/, orders/, account/, auth/
└── admin/                  dashboard, categories/, products/, orders/, customers/
routes/web.php              38 route có tên (named routes)
public/css, public/js       bloomy.css, admin.css, bloomy.js
tests/Feature               81 test tự động
```

## 7. Quan hệ database

```
users 1 ──── n orders 1 ──── n order_items n ──── 1 products n ──── 1 categories
```

| Bảng | Cột chính | Ràng buộc |
|---|---|---|
| `users` | name, email, password (hash), phone, address, role (`ADMIN`/`CUSTOMER`) | email UNIQUE, index role |
| `categories` | name, slug, description, is_active | slug UNIQUE, index is_active |
| `products` | category_id, name, slug, description, price, sale_price, stock, image, is_active | FK → categories (RESTRICT), slug UNIQUE |
| `orders` | code, idempotency_key, user_id, recipient_name/phone, shipping_address, note, delivery_date, total, payment_method, payment_status, status, placed_at | code UNIQUE, idempotency_key UNIQUE, FK → users (SET NULL), index status, placed_at |
| `order_items` | order_id, product_id, **product_name, price** (lúc mua), quantity, subtotal | FK → orders (CASCADE), FK → products (SET NULL) |

Tiền dùng `DECIMAL(12,0)` (đồng Việt Nam không có phần lẻ).

## 8. Các biện pháp bảo mật

- **Mật khẩu** hash bcrypt (cast `hashed`); không có mật khẩu demo viết cứng trong code.
- **Phân quyền phía server**: middleware `auth` + `admin` bọc toàn bộ `/admin/*`; `OrderPolicy` kiểm tra quyền xem đơn.
  Ẩn nút trên giao diện chỉ là phụ.
- Không tự đăng ký ADMIN: `role` không nằm trong `$fillable`.
- **CSRF**: mọi form POST/PUT/PATCH/DELETE có `@csrf`; thiếu token → 419. Đăng xuất bắt buộc dùng POST.
- **Session**: `regenerate()` khi đăng nhập, `invalidate()` + `regenerateToken()` khi đăng xuất. Giới hạn 5 lần đăng nhập sai.
- **XSS**: Blade `{{ }}` tự escape; các chỗ cần xuống dòng dùng `nl2br(e(...))`.
- **SQL injection**: chỉ dùng Eloquent/Query Builder với tham số ràng buộc; ký tự `%` `_` trong ô tìm kiếm được escape.
- **Upload ảnh**: chỉ nhận JPG/PNG/WEBP thật (kiểm tra nội dung + MIME), ≤ 2 MB, 200–5000 px; tên file do Laravel sinh ngẫu nhiên;
  chặn SVG, PHP, EXE.
- **Giá và tổng tiền** luôn tính lại ở server từ database; giá/tổng gửi từ trình duyệt bị bỏ qua.
- **Tồn kho**: kiểm tra khi thêm giỏ, khi thanh toán và trong transaction (`SELECT … FOR UPDATE`) — không bán vượt.
- **Chống đặt trùng**: khóa chống trùng (`idempotency_key` UNIQUE) + khóa nút đặt hàng sau lần bấm đầu.
- Dữ liệu sai mã hóa UTF-8 bị từ chối (400) thay vì gây lỗi 500.

## 9. Tài liệu báo cáo

- Nội dung báo cáo đồ án: [`docs/BAO_CAO_DO_AN.md`](docs/BAO_CAO_DO_AN.md)
- Sơ đồ (PNG): `docs/images/` — use case, ERD, kiến trúc MVC, trạng thái đơn, luồng đặt hàng
- Mã nguồn sơ đồ (Mermaid, sửa được): `docs/diagrams/*.mmd`

## 10. Kiểm thử tự động

```bash
php artisan test
```

81 test (Feature) chạy trên SQLite trong bộ nhớ — **không đụng tới database MySQL thật**.

## 11. Hạn chế hiện tại

- Thanh toán: COD và chuyển khoản bằng **mã QR tĩnh** (`public/images/payment-qr.jpg`, ví MoMo của chủ shop) — khách tự nhập số tiền
  và mã đơn, admin đối soát và xác nhận thủ công. Chưa tích hợp cổng thanh toán tự động (VNPay/MoMo API cần mã merchant).
- Chưa có phí vận chuyển, mã giảm giá.
- Múi giờ ứng dụng đang là **UTC** → giờ đặt hàng hiển thị lệch 7 tiếng so với giờ Việt Nam và bộ lọc ngày trong quản trị
  tính theo ngày UTC. Có thể sửa bằng `APP_TIMEZONE=Asia/Ho_Chi_Minh` (chưa áp dụng).
- Ảnh sản phẩm mẫu lấy từ Wikimedia Commons, chỉ mang tính minh họa (một số ảnh là tranh vẽ hoa, không đúng hoàn toàn với tên sản phẩm); admin có thể thay bằng ảnh thật qua trang quản trị.
- Chưa có: quên mật khẩu, xác minh email, sửa thông tin cá nhân, nhật ký thay đổi trạng thái đơn, gửi email thông báo.
- Giao diện phụ thuộc CDN (Bootstrap, font) → cần Internet.
- Trang lỗi (403/404/419) dùng giao diện mặc định của Laravel; `APP_DEBUG=true` chỉ dùng khi phát triển.

## 12. Xử lý lỗi thường gặp

| Lỗi | Cách xử lý |
|---|---|
| `SQLSTATE[HY000] [2002]` | MySQL chưa chạy — bấm *Bắt đầu* trong Laragon |
| `Unknown database 'quanlybanhoa'` | Chưa tạo CSDL (mục 2.2) |
| `No application encryption key` | `php artisan key:generate` |
| Ảnh sản phẩm upload không hiện | `php artisan storage:link` |
| Sửa `.env` không có tác dụng | `php artisan config:clear` |
| `419 Page Expired` | Phiên làm việc hết hạn — tải lại trang rồi gửi lại form |
| `Failed to listen on 127.0.0.1:...` | Cổng đang bận — đổi `--port` khác |
| `php -v` ra 8.5 / phiên bản lạ | Đang dùng nhầm PHP — mở Terminal từ Laragon |
