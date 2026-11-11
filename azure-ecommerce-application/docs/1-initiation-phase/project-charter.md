<p align="center">
  <a href="https://www.uit.edu.vn/" title="Trường Đại học Công nghệ Thông tin">
    <img src="https://i.imgur.com/WmMnSRt.png" alt="Trường Đại học Công nghệ Thông tin | University of Information Technology">
  </a>
</p>
<h1 align="center"><b>IS402.R11 - ĐIỆN TOÁN ĐÁM MÂY</b></h1>
<p align="center"><b>Hiến chương dự án - Project Charter</b></p>

---

## MỤC LỤC
- [1. Thông tin chung](#1-thông-tin-chung)
- [2. Mục tiêu dự án](#2-mục-tiêu-dự-án)
- [3. Phạm vi dự án](#3-phạm-vi-dự-án)
- [4. Phân công trách nhiệm](#4-phân-công-trách-nhiệm)
- [5. Lộ trình & Sản phẩm bàn giao](#5-lộ-trình--sản-phẩm-bàn-giao)
- [6. Ngân sách & Quản lý chi phí (Quotas)](#6-ngân-sách--quản-lý-chi-phí-quotas)
- [7. Tiêu chí nghiệm thu](#7-tiêu-chí-nghiệm-thu)

---

## 1. Thông tin chung

- **Môn học:** IS402 - Điện toán đám mây (Lớp: IS402.R11)
- **Tên bài tập:** **Bài tập 07: Triển khai ứng dụng trên cloud (Cloud-native applications)**
- **Nhóm ứng dụng:** Thương mại điện tử (E-Commerce)
- **Hướng hiện thực theo Barem:** **Nhánh Web** (Không đi sâu vào nhánh Trực quan dữ liệu phức tạp hay Huấn luyện mô hình ML)
- **Thời hạn hoàn thành:** Hoàn tất trước ngày **27/12/2026** (Tuần báo cáo chính thức bắt đầu từ **28/12/2026**)

### Thành viên nhóm

| STT | MSSV | Họ và Tên | Vai trò chính | Email | Github |
|:---:|:---:|:---|:---|:---|:---|
| 1 | 23521367 | **Ngô Tiến Sỹ** | Trưởng nhóm — Hạ tầng AKS v1.32, Managed NGINX Ingress Add-on, K8s Blue/Green, CI/CD GitHub Actions, Benchmark `>4GB` & Cross-Region | 23521367@gm.uit.edu.vn | [@helios-ryuu](https://github.com/helios-ryuu) |
| 2 | 23520214 | **Lê Quốc Đại** | Thành viên — Frontend Web 4 trang (`PHP 8.5.11`), User Service (`Java 25` & `Spring Boot 4.1.2`), PostgreSQL v18 & Redis 7.2 | 23520214@gm.uit.edu.vn | [@daile2701](https://github.com/daile2701) |
| 3 | 23521192 | **Đào Bảo Phúc** | Thành viên — Order Service (`Python 3.14` & `FastAPI 0.162.2`), Cosmos DB, Azure Functions v4 (FaaS ETL), Synapse & Power BI | 23521192@gm.uit.edu.vn | [@baorphuc](https://github.com/baorphuc) |

---

## 2. Mục tiêu dự án

Dự án tập trung giải quyết gọn gàng, đúng trọng tâm các yêu cầu của **Bài tập 07** và **Barem chấm điểm hướng Web**:
1. **Triển khai Web & Microservices trên AKS (`v1.32`):** Đóng gói container cho 3 thành phần viết bằng **PHP 8.5.11, Java 25, Python 3.14**, định tuyến qua **AKS Application Routing Add-on (Managed NGINX Ingress Controller `v1.11`)** với tên miền miễn phí `*.japaneast.cloudapp.azure.com`:
   - Giao diện Web (`PHP 8.5.11` & `Apache 2.4.70`) có đủ **4 trang**: Trang chủ, Trang chi tiết, Trang thêm vào giỏ hàng, Trang liệt kê giỏ hàng.
   - **2 Microservices nghiệp vụ** có giao tiếp trực tiếp với nhau qua K8s `ClusterIP`: **User Service** (`Java 25` & `Spring Boot 4.1.2` - đăng ký/xác thực tài khoản) và **Order Service** (`Python 3.14` & `FastAPI 0.162.2` - mua sản phẩm).
   - Áp dụng **Blue/Green Deployment** cho Order Service để cập nhật phiên bản không gián đoạn.
2. **Sử dụng 3 công nghệ lưu trữ quản lý (Managed Databases):**
   - **Azure Database for PostgreSQL (`Flexible Server v18`):** Lưu trữ tài khoản người dùng (`users`) và danh mục sản phẩm gốc (`products`).
   - **Azure Cache for Redis (`Redis 7.2`):** Cache danh mục sản phẩm (`< 1ms`) và lưu giỏ hàng tạm của người dùng.
   - **Azure Cosmos DB (`Core SQL API v4`):** Lưu trữ đơn hàng (`orders`) dạng JSON Document.
3. **Kết hợp FaaS & Luồng ETL tự động (`Client -> Storage Cloud`):**
   - Sử dụng **Azure Functions v4 (`Python 3.14`)** tự động nhận sự kiện khi có đơn hàng mới từ Cosmos DB, chuẩn hóa dữ liệu và lưu sang **Azure Storage (Blob / ADLS Gen2)**.
4. **Tích hợp Azure Synapse Analytics, Power BI & Thực nghiệm Dữ liệu lớn (`> 4GB`):**
   - Lưu trữ bộ Dataset thương mại điện tử `> 4GB` trên Azure Storage để thực hiện bài đo so sánh tốc độ xử lý giữa **Máy chủ truyền thống (Local) và Hệ thống Cloud (Synapse Serverless SQL)**.
   - Thực hiện đo tốc độ đọc/ghi (có Redis Cache vs không Cache) và **đo độ trễ (delay) khi đặt server ở Region khác**.
   - Kết nối **Synapse Analytics** và **Power BI** ở mức tinh gọn (1 View SQL + 1 biểu đồ Dashboard tổng quan) để hoàn thiện trọn vẹn chuỗi công nghệ của đề bài.
5. **Tự động hóa CI/CD:** Sử dụng **GitHub Actions (`v4`)** để tự động build Docker image, đẩy lên **Azure Container Registry (ACR)** và triển khai lên **AKS**.

---

## 3. Phạm vi dự án

### 3.1 Trong phạm vi (In-Scope)

| Hạng mục | Nội dung triển khai cụ thể |
|:---|:---|
| **1. Web & Microservices trên AKS (`v1.32`)** | • **Frontend Web (`PHP 8.5.11` + `Apache 2.4.70`):** Định tuyến qua **AKS Application Routing Add-on (Managed NGINX Ingress)**; gồm 4 trang (Trang chủ, Chi tiết SP, Thêm vào giỏ hàng, Liệt kê giỏ hàng & Đặt mua).<br>• **User Service (`Java 25` + `Spring Boot 4.1.2`):** API đăng ký, đăng nhập và API xác thực `userId` nội bộ (`ClusterIP`).<br>• **Order Service (`Python 3.14` + `FastAPI 0.162.2`):** API tạo đơn mua hàng, gọi HTTP nội bộ sang User Service để kiểm tra tài khoản trước khi lưu đơn; triển khai 2 bản **Blue (v1)** và **Green (v2)**. |
| **2. Lưu trữ Đám mây (PaaS)** | • **Azure PostgreSQL Flexible Server (`v18`):** Bảng `users` và `products`.<br>• **Azure Cache for Redis (`7.2`):** Cache danh sách/chi tiết sản phẩm và lưu giỏ hàng.<br>• **Azure Cosmos DB (`Core SQL v4`):** Container `orders` lưu đơn mua hàng. |
| **3. FaaS & Tự động ETL** | • **Azure Functions v4 (`Python 3.14`):** Tự động kích hoạt khi có đơn hàng mới, chuyển đổi và ghi dữ liệu đơn hàng sang **Azure Storage**. |
| **4. Synapse, Power BI & Benchmark** | • **Azure Storage (`StorageV2` - ADLS Gen2 / Blob):** Chứa dữ liệu đơn hàng ETL từ Web và bộ Dataset mẫu `> 4GB` (Parquet/CSV).<br>• **Azure Synapse Analytics:** Serverless SQL Pool truy vấn dữ liệu trên Storage.<br>• **Power BI Service:** 1 trang Dashboard tổng quan cơ bản kết nối từ Synapse.<br>• **Kịch bản đo kiểm:** So sánh Local vs Cloud trên Dataset `> 4GB`; đo tốc độ đọc/ghi Redis 7.2 vs PostgreSQL v18; đo độ trễ cùng Region (`Japan East`) vs khác Region (`East US`). |
| **5. CI/CD & Quản lý dự án** | • **GitHub Actions + ACR:** Tự động build image và deploy lên AKS.<br>• **Quản trị Cloud:** Các thành viên cùng làm việc trên 1 Resource Group chung, quản lý chi phí trong gói `$100` Azure for Students. |

### 3.2 Ngoài phạm vi (Out-of-Scope — Đã lược bỏ để tránh lan man)
- **Không** làm các yêu cầu chuyên sâu của nhánh "Trực quan dữ liệu" (không làm 4 trang báo cáo Power BI phức tạp, không làm Dynamic RLS, không làm Mobile Layout 9:16).
- **Không** làm các yêu cầu của nhánh "Phân tích dữ liệu / Machine Learning" (không huấn luyện model AI/ML).
- **Không** sinh file PDF hóa đơn phức tạp trong Azure Functions; không tích hợp cổng thanh toán thật.
- **Không** tự cài Ingress Controller thủ công hay cần mua tên miền riêng mà dùng **AKS Application Routing Add-on (Managed NGINX Ingress Controller `v1.11`)** kết hợp tên miền `.japaneast.cloudapp.azure.com` miễn phí của Azure; bỏ SSL/TLS cổng 443, HPA và Trivy Scan.

---

## 4. Phân công trách nhiệm

| Thành viên | Phụ trách chính | Sản phẩm bàn giao |
|:---|:---|:---|
| **Ngô Tiến Sỹ**<br>*(Trưởng nhóm)* | • Khởi tạo Resource Group chung, VNet, ACR và cụm `AKS v1.32`.<br>• Bật **AKS Application Routing Add-on (Managed NGINX Ingress)**, cấu hình K8s Manifests và cơ chế chuyển đổi **Blue/Green**.<br>• Xây dựng pipeline **CI/CD GitHub Actions**.<br>• Thực hiện đo độ trễ khác Region (Cross-Region) và quản lý chi phí (Quotas). | • Kịch bản hạ tầng & K8s Ingress/Manifests.<br>• Workflow GitHub Actions.<br>• Kết quả đo Cross-Region Latency & Báo cáo chi phí Azure. |
| **Lê Quốc Đại**<br>*(Thành viên)* | • Phát triển **Frontend Web (`PHP 8.5.11`)** đủ 4 trang chức năng.<br>• Phát triển **User Service (`Java 25` & `Spring Boot 4.1.2`)** đăng ký/đăng nhập/verify user.<br>• Cấu hình **Azure PostgreSQL `v18`** (`users`, `products`) và **Azure Cache for Redis `7.2`** (cache SP, giỏ hàng).<br>• Đo tốc độ đọc/ghi và chứng minh tối ưu hóa nhờ Redis Cache. | • Mã nguồn Frontend PHP 8.5.11 & Dockerfile.<br>• Mã nguồn User Service Java 25 & Dockerfile.<br>• Kết quả đo tốc độ đọc/ghi Cache Hit vs DB. |
| **Đào Bảo Phúc**<br>*(Thành viên)* | • Phát triển **Order Service (`Python 3.14` & `FastAPI 0.162.2`)** mua sản phẩm, gọi sang User Service và lưu vào **Azure Cosmos DB**.<br>• Viết **Azure Functions v4 (`Python 3.14`)** tự động ETL đơn hàng mới sang Azure Storage.<br>• Chuẩn bị Dataset `> 4GB`, chạy benchmark Local vs Cloud trên **Synapse** và dựng 1 **Power BI Dashboard** cơ bản. | • Mã nguồn Order Service Python 3.14 & Dockerfile.<br>• Mã nguồn Azure Functions (Auto-ETL).<br>• Kết quả Benchmark `> 4GB`, View Synapse & Power BI Dashboard. |

---

## 5. Lộ trình & Sản phẩm bàn giao

### 5.1 Kế hoạch thời gian (Hoàn thành trước 27/12/2026)
Chi tiết phân rã công việc xem tại [wbs.md](../2-planning-phase/wbs.md):
1. **Giai đoạn 1 (01/11 – 10/11/2026):** Chốt phạm vi tinh gọn theo Barem nhánh Web, hoàn thiện `project-charter.md`, `architecture.md`, `functional-requirements.md`, `wbs.md`.
2. **Giai đoạn 2 (11/11 – 22/11/2026):** Cấp phát Resource Group, AKS `v1.32`, ACR, PostgreSQL `v18`, Redis `7.2`, Cosmos DB và Azure Storage.
3. **Giai đoạn 3 (18/11 – 10/12/2026):** Phát triển 3 ứng dụng (`PHP 8.5.11`, `Java 25`, `Python 3.14`), đóng gói container, cấu hình Managed NGINX Ingress Add-on, Blue/Green và CI/CD.
4. **Giai đoạn 4 (05/12 – 20/12/2026):** Xây dựng Azure Functions Auto-ETL, nạp Dataset `> 4GB`, cấu hình Synapse + Power BI cơ bản, chạy 3 bài thực nghiệm đo kiểm.
5. **Giai đoạn 5 (18/12 – 26/12/2026):** Hoàn thiện báo cáo Word, Slide PowerPoint chuẩn KLTN và tổng duyệt Demo **trước 27/12/2026** (sẵn sàng cho tuần báo cáo từ **28/12/2026**).

### 5.2 Danh mục sản phẩm bàn giao (Deliverables)
- **D01:** Bộ tài liệu quy hoạch dự án (`project-charter.md`, `architecture.md`, `functional-requirements.md`, `wbs.md`).
- **D02:** Mã nguồn 3 dịch vụ (`frontend-php`, `user-service-java`, `order-service-python`) + K8s Manifests (Managed NGINX Ingress & Blue/Green) + GitHub Actions CI/CD.
- **D03:** Mã nguồn `azure-function-etl` (`Python 3.14`) tự động đồng bộ đơn hàng từ Cosmos DB sang Azure Storage.
- **D04:** Bộ Dataset `> 4GB` trên Azure Storage, script truy vấn Synapse SQL, 1 trang Power BI Dashboard và bảng số liệu thực nghiệm (Local vs Cloud, Tốc độ đọc/ghi, Độ trễ khác Region).
- **D05:** Báo cáo Word (`.docx`) và Slide thuyết trình (`.pptx`) theo đúng định dạng KLTN.

---

## 6. Ngân sách & Quản lý chi phí (Quotas)

Dự án sử dụng gói **Azure for Students ($100.00 USD)** với cấu hình tiết kiệm tối đa:

| Dịch vụ Azure | Cấu hình lựa chọn | Chi phí ước tính |
|:---|:---|:---:|
| **Azure Kubernetes Service (`AKS v1.32`)** | 1–2 Node `Standard_B2als_v2` + Application Routing Add-on (Tắt khi không dùng) | ~$15.00 – $20.00 |
| **Azure DB for PostgreSQL (`v18`)** | Flexible Server Burstable `B1ms` (Có thể Stop khi không dùng) | ~$10.00 – $15.00 |
| **Azure Cache for Redis (`7.2`)** | Basic Tier `C0` (250MB) | ~$8.00 |
| **Azure Cosmos DB (`Core SQL v4`)** | Bật **Free Tier** (1000 RU/s, 25GB miễn phí) | **$0.00** |
| **Azure Functions (`Runtime v4`)** | Flex Consumption (`Python 3.14`, Scale to zero, miễn phí 250K lượt/tháng) | **$0.00** |
| **Azure Storage (ADLS Gen2 / Blob)** | Lưu trữ Dataset `> 4GB` Parquet/CSV & dữ liệu ETL đơn hàng | ~$1.00 |
| **Azure Synapse Analytics** | Serverless SQL Pool (Tính phí theo lượng dữ liệu quét: $5/TB) | ~$1.00 – $2.00 |
| **Azure Container Registry (ACR)** | ACR Basic Tier + Ingress Public IP | ~$7.00 |
| **TỔNG CỘNG DỰ KIẾN** | *(An toàn tuyệt đối trong hạn mức $100)* | **~$42.00 – $53.00** |

---

## 7. Tiêu chí nghiệm thu

1. **Web 4 trang trên AKS (1.0đ):** Truy cập được qua AKS Managed NGINX Ingress: Trang chủ -> Trang chi tiết -> Thêm vào giỏ hàng -> Liệt kê giỏ hàng.
2. **Blue/Green Deployment (0.5đ):** Đổi selector giữa `blue` (v1) và `green` (v2) trên AKS thành công, không gây downtime.
3. **2 Service giao tiếp với nhau (1.0đ):** Đăng ký tài khoản trên User Service (`Java 25`) -> Mua hàng trên Order Service (`Python 3.14`) -> Order Service gọi API xác thực qua User Service thành công rồi lưu vào Cosmos DB.
4. **FaaS & Auto-ETL (1.0đ = 0.5đ FaaS + 0.5đ ETL):** Có đơn hàng mới trong Cosmos DB -> Azure Functions tự động chạy và xuất file dữ liệu đơn hàng vào Azure Storage.
5. **Đo kiểm Mô hình dữ liệu & Dataset > 4GB (2.25đ):** Có bảng số liệu và hình chụp thực tế cho: (a) Xử lý Dataset `> 4GB` Local vs Cloud; (b) Tốc độ đọc/ghi có Redis Cache vs DB; (c) Độ trễ truy xuất giữa 2 Region khác nhau.
6. **Synapse & Power BI:** Truy vấn được dữ liệu từ Azure Storage qua Synapse và hiển thị lên biểu đồ Power BI.
