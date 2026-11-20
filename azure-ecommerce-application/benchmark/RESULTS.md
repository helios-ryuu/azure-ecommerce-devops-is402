# Kết quả 3 Kịch bản Thực nghiệm Benchmark (IS402.R11)

## 1. Thực nghiệm 1: Xử lý Dataset `> 4GB` — Local vs Azure Synapse Serverless SQL (`0.75đ`)

- **Ngày đo:** 20/11/2026
- **Tập dữ liệu:** `ecommerce_benchmark_4gb.csv` — **`4.1 GB`** (`81,920,000` bản ghi đơn hàng, 5 danh mục sản phẩm, mỗi danh mục `16,384,000` đơn).
- **Truy vấn đối chiếu:** Tổng hợp `COUNT(*)`, `SUM(total_amount)`, `AVG(total_amount)` theo `category`.

| Môi trường thực thi | Nguồn dữ liệu | Thời gian xử lý (giây) | So sánh tốc độ |
| --- | --- | ---: | --- |
| **Máy Local (Python đơn luồng)** | `/tmp/ecommerce_benchmark_4gb.csv` (RAM tmpfs) | **56.36 s** | Cơ sở (`1.00x`) |
| **Cloud — Azure Synapse Serverless SQL (`synw-is402`)** | ADLS Gen2 `stis402lakehouse/benchmark-dataset/*.csv` | **30.95 s** (`00:00:30.947`) | **Nhanh hơn 1.82x (giảm 45.1% thời gian)** |

- **Kiểm chứng tính toàn vẹn dữ liệu (Khớp 100% giữa Local và Cloud Synapse):**
  - `Serverless Accessories`: `16,384,000` đơn | Doanh thu: `5,962,342,400,000.00`
  - `Containers & K8s`: `16,384,000` đơn | Doanh thu: `5,388,902,400,000.00`
  - `Database Books`: `16,384,000` đơn | Doanh thu: `4,815,462,400,000.00`
  - `DevOps Gear`: `16,384,000` đơn | Doanh thu: `4,242,022,400,000.00`
  - `Cloud Apparel`: `16,384,000` đơn | Doanh thu: `3,671,449,600,000.00`

---

## 2. Thực nghiệm 2: Tốc độ đọc/ghi PostgreSQL vs Redis Cache-Aside (`1.0đ`)

### 2.1 Môi trường Docker Compose Local (Ngày đo: 17/11/2026)

- Số yêu cầu cho mỗi chế độ: 1.000 | Mức đồng thời: 20
- Dữ liệu: danh mục sản phẩm trong bảng `products`
- Redis dùng mẫu Cache-Aside; cache được làm nóng trước khi đo

| Chế độ | Latency trung bình (ms) | P50 (ms) | P95 (ms) | Throughput (req/s) |
| --- | ---: | ---: | ---: | ---: |
| PostgreSQL trực tiếp | 7,885 | 7,086 | 10,781 | 2.502,13 |
| Redis Cache-Aside | 2,325 | 1,713 | 4,676 | 7.979,26 |

- **Nhận xét Local:** Redis giảm latency trung bình khoảng **70,5%** và tăng throughput gấp **3,19 lần** so với đọc trực tiếp PostgreSQL.

### 2.2 Môi trường Cloud Azure — `pg-is402-db` vs `redis-is402-cache` (Ngày đo: 20/11/2026)

- **Môi trường:** Đo từ máy khách (Việt Nam) tới **Azure PostgreSQL Flexible Server (`v18`, `Standard_B1ms`, `Japan East`)** và **Azure Cache for Redis (`Basic C0`, TLS `6380`, `Japan East`)**.
- **Cấu hình tải:** 200 yêu cầu cho mỗi chế độ | Mức đồng thời (`concurrency`): 10.

| Chế độ (Cloud Azure `Japan East`) | Latency trung bình (ms) | P50 (ms) | P95 (ms) | Throughput (req/s) |
| --- | ---: | ---: | ---: | ---: |
| **Azure PostgreSQL v18 trực tiếp** | **394.94** | 269.64 | 975.70 | **24.98** |
| **Azure Cache for Redis (Cache-Aside)** | **149.26** | 100.44 | 381.91 | **65.07** |

- **Nhận xét Cloud Azure:** Dù đã bao gồm độ trễ mạng đường truyền từ Việt Nam sang `Japan East` (~80–100 ms), **Azure Cache for Redis** vẫn giúp **giảm latency trung bình 62.2%** (`394.94 ms` $\rightarrow$ `149.26 ms`), **giảm P95 60.9%** và **tăng thông lượng (throughput) gấp 2.60 lần** (`24.98` $\rightarrow$ `65.07 req/s`).

---

## 3. Thực nghiệm 3: Đo độ trễ Cùng Region (`Japan East`) vs Khác Region (`Central India`) (`0.5đ`)

- **Ngày đo:** 20/11/2026 | **Số mẫu:** 50 HTTP/TLS RTT requests cho mỗi Region (script `benchmark/cross_region_latency.py`).
- **Ghi chú Policy:** Do Azure for Students giới hạn 5 Region (`japaneast`, `centralindia`, `eastasia`, `indonesiacentral`, `koreacentral`), nhóm chọn **`Central India`** (cách `Japan East` ~6.700 km) làm Region đối chứng và xóa ngay tài nguyên test `stis402india` sau khi đo xong.

| Region đích | Endpoint kiểm thử | Min (ms) | Avg (ms) | P50 (ms) | P95 (ms) | Max (ms) |
| --- | --- | ---: | ---: | ---: | ---: | ---: |
| **Japan East** *(Region chính)* | `stis402lakehouse.blob.core.windows.net` | 320.55 | **374.10** | 354.98 | 492.98 | 603.28 |
| **Central India** *(Region đối chứng)* | `stis402india.blob.core.windows.net` | 300.17 | **330.21** | 326.19 | 376.47 | 406.66 |

- **Nhận xét:**
  - Khi đo từ máy khách tại **Việt Nam**, lưu lượng đi ra cả 2 Region đều chịu trễ đường truyền cáp quang quốc tế (`330 ms` – `374 ms`, độ lệch trung bình `~43.9 ms`, P95 `376.47 ms` vs `492.98 ms` phụ thuộc định tuyến ISP tại thời điểm đo).
  - Trong kiến trúc triển khai thực tế trên Azure, toàn bộ cụm **AKS (`aks-is402-ecommerce`)**, **PostgreSQL (`pg-is402-db`)**, **Redis (`redis-is402-cache`)**, **Cosmos DB**, **Azure Functions** và **ADLS Gen2 (`stis402lakehouse`)** đều được đặt đồng bộ trong cùng Region **`Japan East`** nhằm loại bỏ hoàn toàn độ trễ WAN liên vùng và tránh phát sinh cước truyền dữ liệu Cross-Region (`Outbound data transfer`).
