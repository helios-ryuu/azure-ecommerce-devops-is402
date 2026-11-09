<p align="center">
  <a href="https://www.uit.edu.vn/" title="Trường Đại học Công nghệ Thông tin">
    <img src="https://i.imgur.com/WmMnSRt.png" alt="Trường Đại học Công nghệ Thông tin | University of Information Technology">
  </a>
</p>
<h1 align="center"><b>IS402.R11 - ĐIỆN TOÁN ĐÁM MÂY</b></h1>
<p align="center"><b>ĐẶC TẢ YÊU CẦU CHỨC NĂNG & ĐỐI CHIẾU BAREM (FUNCTIONAL REQUIREMENTS)</b></p>
<p align="center"><i>Dự án: Ứng dụng Thương mại điện tử Cloud-Native trên Microsoft Azure (Nhánh Web)</i></p>

---

## MỤC LỤC
- [1. Mục đích & Phạm vi Tinh gọn](#1-mục-đích--phạm-vi-tinh-gọn)
- [2. Quyết định Kỹ thuật & Phiên bản Đã Chốt](#2-quyết-định-kỹ-thuật--phiên-bản-đã-chốt)
- [3. Danh sách 10 Yêu cầu Chức năng Cốt lõi (FR-01 đến FR-10)](#3-danh-sách-10-yêu-cầu-chức-năng-cốt-lõi-fr-01-đến-fr-10)
- [4. Ma trận Truy vết Yêu cầu theo Barem Chấm điểm](#4-ma-trận-truy-vết-yêu-cầu-theo-barem-chấm-điểm)

---

## 1. Mục đích & Phạm vi Tinh gọn

Tài liệu này định nghĩa ngắn gọn, rõ ràng các yêu cầu chức năng của hệ thống **Thương mại điện tử Cloud-Native** theo đúng **Nhánh Web** trong Barem chấm điểm môn IS402 và các công nghệ yêu cầu của **Bài tập 07**. Mọi tính năng rườm rà không nằm trong barem đã được lược bỏ để nhóm tập trung hoàn thành 100% trước ngày **27/12/2026**.

---

## 2. Quyết định Kỹ thuật & Phiên bản Đã Chốt

Để việc lập trình của 3 thành viên diễn ra nhanh chóng và đồng nhất, toàn bộ các điểm thiết kế được chốt cố định như sau:
1. **Ngôn ngữ, Framework & Điều phối Container:**
   - **Hạ tầng Container & Ingress:** `Azure Kubernetes Service (AKS v1.32)` sử dụng **AKS Application Routing Add-on (Managed NGINX Ingress Controller `v1.11`)** kèm tên miền miễn phí `http://is402-ecommerce.southeastasia.cloudapp.azure.com` để định tuyến HTTP vào các service `ClusterIP`.
   - **Frontend Web:** `PHP 8.5.11` chạy trên `Apache 2.4.70` (4 trang giao diện, `ClusterIP: frontend-svc:80`).
   - **User Service (Service 1):** `Java 25` + `Spring Boot 4.1.2` (`ClusterIP: user-svc:8080`).
   - **Order Service (Service 2):** `Python 3.15.2` + `FastAPI 0.162.2` (`ClusterIP: order-svc:8000`).
   - **FaaS Auto-ETL:** `Azure Functions Runtime v4` (`Python 3.15.2` Consumption Plan).
2. **Phân bổ Cơ sở dữ liệu (Polyglot Persistence):**
   - **Azure Database for PostgreSQL (`Flexible Server v18`):** Lưu bảng `users` (tài khoản người dùng) và bảng `products` (danh mục sản phẩm gốc ~10–20 sản phẩm mẫu).
   - **Azure Cache for Redis (`Redis 7.2`):** Lưu cache danh mục sản phẩm (`catalog:products`, TTL 300s) và lưu giỏ hàng của người dùng (`cart:{userId}`).
   - **Azure Cosmos DB (`Core SQL API v4`):** Lưu đơn hàng khi khách bấm mua tại container `orders` (Partition Key: `/userId`).
   - **Azure Storage (`StorageV2` - ADLS Gen2 / Blob):** Lưu các file dữ liệu đơn hàng được đồng bộ tự động từ Azure Functions (`orders-etl/`) và lưu bộ **Dataset lịch sử `> 4GB`** (`benchmark-dataset/`) phục vụ đo kiểm và truy vấn qua **Azure Synapse Analytics**.
3. **Giao tiếp Liên Dịch vụ:**
   - `Order Service (Python 3.15.2)` gọi trực tiếp REST API nội bộ `GET http://user-svc:8080/api/users/verify/{userId}` của `User Service (Java 25)` để xác nhận tài khoản hợp lệ trước khi tạo đơn hàng.

---

## 3. Danh sách 10 Yêu cầu Chức năng Cốt lõi (FR-01 đến FR-10)

### FR-01: Trang chủ (Danh mục sản phẩm & Redis Cache)
- **Thành phần:** Frontend Web (`PHP 8.5.11`), Azure Cache for Redis `7.2`, Azure PostgreSQL `v18`.
- **Mô tả:** Hiển thị danh sách sản phẩm trên Trang chủ (`/index.php`) truy cập qua **AKS Managed NGINX Ingress**.
- **Luồng xử lý:**
  1. Khi người dùng mở Trang chủ, PHP kiểm tra key `catalog:products` trong **Redis 7.2**.
  2. Nếu có (**Cache Hit**): Hiển thị ngay danh sách sản phẩm (độ trễ `< 1ms`) kèm nhãn báo nguồn dữ liệu từ `Redis Cache`.
  3. Nếu chưa có (**Cache Miss**): Truy vấn bảng `products` từ **PostgreSQL v18**, lưu kết quả vào **Redis 7.2** (TTL 300 giây) và hiển thị lên giao diện.

### FR-02: Trang chi tiết sản phẩm
- **Thành phần:** Frontend Web (`PHP 8.5.11`), Azure Cache for Redis `7.2` / PostgreSQL `v18`.
- **Mô tả:** Khi người dùng bấm vào một sản phẩm ở Trang chủ, hệ thống chuyển sang Trang chi tiết (`/detail.php?id={productId}`).
- **Luồng xử lý:** Hiển thị đầy đủ tên sản phẩm, hình ảnh, mô tả, giá bán, ô chọn số lượng và nút **"Thêm vào giỏ hàng"**.

### FR-03: Trang thêm vào giỏ hàng & Trang liệt kê giỏ hàng
- **Thành phần:** Frontend Web (`PHP 8.5.11`), Azure Cache for Redis `7.2`.
- **Mô tả:** Đảm bảo đủ 2 trang chức năng giỏ hàng theo đúng yêu cầu Barem Mục 4.1:
  - **Trang thêm vào giỏ hàng (`/add-to-cart.php`):** Nhận `productId` và `quantity`, lưu/cập nhật vào Hash `cart:{userId}` trên **Azure Cache for Redis 7.2**, hiển thị xác nhận đã thêm thành công và nút chuyển tới giỏ hàng.
  - **Trang liệt kê giỏ hàng (`/cart.php`):** Đọc toàn bộ danh sách mặt hàng trong `cart:{userId}` từ **Redis 7.2**, hiển thị thành tiền từng món, tổng tiền đơn hàng và nút **"Mua sản phẩm"**.

### FR-04: Service 1 — Đăng ký & Xác thực tài khoản (`User Service`)
- **Thành phần:** User Service (`Java 25` + `Spring Boot 4.1.2`), Azure DB for PostgreSQL `v18`.
- **Mô tả:** Quản lý tài khoản người dùng trên cơ sở dữ liệu quan hệ PostgreSQL.
- **Luồng xử lý:**
  - `POST /api/users/register`: Nhận `username`, `email`, `password`, lưu bản ghi mới vào bảng `users` trong PostgreSQL v18.
  - `POST /api/users/login`: Kiểm tra thông tin đăng nhập và trả về `userId` cùng thông tin người dùng cho Web PHP lưu vào session.
  - `GET /api/users/verify/{userId}`: Trả về `HTTP 200 OK` (kèm thông tin user) nếu tài khoản hợp lệ, hoặc `HTTP 404` nếu không tồn tại.

### FR-05: Service 2 — Mua sản phẩm & Giao tiếp liên dịch vụ (`Order Service`)
- **Thành phần:** Order Service (`Python 3.15.2` + `FastAPI 0.162.2`), User Service (`Java 25`), Azure Cosmos DB (`Core SQL v4`).
- **Mô tả:** Xử lý đặt mua sản phẩm từ giỏ hàng và chứng minh giao tiếp giữa 2 Microservices trên AKS.
- **Luồng xử lý:**
  1. Tại trang liệt kê giỏ hàng, người dùng bấm **"Mua sản phẩm"**. Frontend gửi `userId` và danh sách sản phẩm trong giỏ tới `POST http://order-svc:8000/api/orders` của **Order Service**.
  2. **Order Service (`Python 3.15.2`)** gọi HTTP GET nội bộ sang `http://user-svc:8080/api/users/verify/{userId}` của **User Service (`Java 25`)**.
  3. Sau khi User Service xác nhận người dùng hợp lệ, Order Service tính tổng tiền, sinh mã `orderId` và lưu tài liệu JSON đơn hàng vào container `orders` trên **Azure Cosmos DB**.
  4. Xóa giỏ hàng tạm trên Redis và hiển thị thông báo đặt hàng thành công.

### FR-06: Triển khai Blue/Green cho Order Service trên AKS
- **Thành phần:** Azure Kubernetes Service (`AKS v1.32`), K8s Deployments & Service.
- **Mô tả:** Hỗ trợ cập nhật phiên bản dịch vụ không gây gián đoạn (Zero-downtime).
- **Luồng xử lý:**
  1. Duy trì 2 Deployment trên AKS: `order-service-blue` (trả kèm trường `"version": "v1-blue"`) và `order-service-green` (trả kèm trường `"version": "v2-green"`).
  2. K8s Service `order-svc` định tuyến lưu lượng dựa trên selector `version: blue` hoặc `version: green`.
  3. Khi cập nhật selector sang `green`, 100% yêu cầu mua hàng mới lập tức được xử lý bởi bản `v2-green` mà không bị ngắt kết nối.

### FR-07: Sử dụng FaaS & Tự động ETL Đơn hàng sang Storage Cloud
- **Thành phần:** Azure Cosmos DB, Azure Functions v4 (`Python 3.15.2`), Azure Storage (Blob / ADLS Gen2).
- **Mô tả:** Kết hợp yêu cầu **FaaS (Mục 4.4)** và **Luồng ETL tự động từ Web sang Storage Cloud (Mục 3.2)**.
- **Luồng xử lý:**
  1. Khi bản ghi đơn hàng mới được ghi vào **Cosmos DB**, sự kiện tự động kích hoạt hàm **Azure Functions v4 (`Python 3.15.2`)**.
  2. Hàm Function đọc dữ liệu đơn hàng, làm phẳng/chuẩn hóa các trường (`orderId`, `userId`, `totalAmount`, `itemCount`, `createdAt`) và ghi thành file dữ liệu vào thư mục `orders-etl/` trên **Azure Storage**.

### FR-08: Truy vấn Azure Synapse Analytics & Trực quan hóa Power BI Cơ bản
- **Thành phần:** Azure Storage, Azure Synapse Analytics (Serverless SQL), Power BI.
- **Mô tả:** Đáp ứng yêu cầu công nghệ của đề bài ở mức tinh gọn, không lan man.
- **Luồng xử lý:**
  1. **Azure Synapse Serverless SQL** sử dụng `OPENROWSET` tạo View SQL đọc dữ liệu đơn hàng và bộ Dataset `> 4GB` trên Azure Storage.
  2. **Power BI** kết nối vào View của Synapse để hiển thị **1 trang Dashboard tổng quan** (biểu đồ doanh thu theo thời gian/danh mục và tổng số đơn hàng).

### FR-09: Tự động hóa CI/CD với GitHub Actions & ACR
- **Thành phần:** GitHub Actions (`v4`), Azure Container Registry (ACR), `AKS v1.32`.
- **Mô tả:** Tự động hóa quy trình từ khi đẩy code lên GitHub đến khi cập nhật container trên AKS.
- **Luồng xử lý:** Khi push code lên nhánh `main`, GitHub Actions tự động build Docker Image cho 3 service, push lên **ACR** và áp dụng manifest lên **AKS**.

### FR-10: Thực nghiệm Đo kiểm Hiệu năng (Dataset `> 4GB`, Cache vs DB, Cross-Region)
- **Thành phần:** Script Benchmark (`Python 3.15.2`), Azure Storage (`> 4GB`), Synapse, Redis `7.2`, PostgreSQL `v18`, Cosmos DB.
- **Mô tả:** Cung cấp số liệu thực nghiệm đầy đủ cho Mục 1.3 và Mục 3 của Barem:
  1. **Xử lý Dataset `> 4GB` (Local vs Cloud):** Đo thời gian chạy truy vấn tổng hợp trên tập dữ liệu `> 4GB` ở máy Local so với chạy trên Azure Synapse Serverless SQL.
  2. **Tốc độ đọc/ghi & Tối ưu hóa (Cache vs DB):** Đo thời gian đọc danh mục sản phẩm khi có **Redis 7.2 Cache** (`< 1ms`) so với đọc trực tiếp từ **PostgreSQL v18**, và tốc độ ghi/đọc đơn hàng trên **Cosmos DB**.
  3. **Đo độ trễ khác Region (Cross-Region Latency):** Đo độ trễ (ms) khi gọi từ `Southeast Asia` tới tài nguyên cùng Region (`Southeast Asia`) so với tài nguyên đặt ở Region khác (`East US`).

---

## 4. Ma trận Truy vết Yêu cầu theo Barem Chấm điểm

| Mã FR | Tên chức năng | Mục Barem tương ứng | Điểm Barem | Bằng chứng nghiệm thu khi Demo / Báo cáo |
|:---:|:---|:---|:---:|:---|
| **FR-01, 02, 03** | Web 4 trang (`PHP 8.5.11`: Chủ, Chi tiết, Thêm giỏ, Liệt kê giỏ) | **Mục 4.1 (Web)** | **1.0đ** | Demo trực tiếp 4 trang hoạt động mượt qua AKS Managed NGINX Ingress. |
| **FR-04, 05** | 2 Service (`Java 25` & `Python 3.15.2`) giao tiếp với nhau | **Mục 4.3 (Web)** | **1.0đ** | Log và kết quả Order Service gọi sang User Service xác thực trước khi lưu Cosmos DB. |
| **FR-06** | Triển khai Blue/Green trên `AKS v1.32` | **Mục 4.2 (Web)** | **0.5đ** | Demo đổi selector từ `blue` sang `green`, giao diện/API đổi sang `v2-green` tức thì. |
| **FR-07** | Azure Functions v4 (`Python 3.15.2`) tự động ETL sang Storage | **Mục 4.4 (FaaS) & Mục 3.2 (Auto-ETL)** | **1.0đ** *(0.5 + 0.5)* | Tạo đơn trên Web -> Log Azure Functions chạy -> File mới xuất hiện trên Azure Storage. |
| **FR-08** | Truy vấn Synapse Serverless SQL & Power BI Dashboard | **Yêu cầu đề bài & Mục 2.3** | *Đủ điều kiện đề* | Ảnh chụp câu lệnh SQL trên Synapse và 1 màn hình biểu đồ trên Power BI. |
| **FR-09** | Pipeline CI/CD GitHub Actions + ACR + AKS | **Yêu cầu đề bài & Mục 5** | *Đủ điều kiện đề* | Lịch sử chạy GitHub Actions xanh (Success) và image trên ACR. |
| **FR-10** | Thực nghiệm Dataset `> 4GB` (Local vs Cloud), Đọc/Ghi & Độ trễ khác Region | **Mục 1.3 (0.75đ) & Mục 3.1, 3.3, 3.4 (1.5đ)** | **2.25đ** | Bảng số liệu & biểu đồ so sánh thời gian xử lý `> 4GB`, tốc độ Redis vs DB, và độ trễ `Southeast Asia` vs `East US`. |
