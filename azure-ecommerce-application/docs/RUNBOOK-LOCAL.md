# Hướng dẫn Khởi chạy Môi trường Local (Docker Compose)

Tài liệu này hướng dẫn khởi chạy nhanh toàn bộ 5 container (`postgres:18-alpine`, `redis:7.2-alpine`, `user-service`, `order-service`, `frontend-php`) trên máy cá nhân để phát triển và kiểm thử trước khi đẩy lên cụm **Azure AKS (`Japan East`)**.

## 1. Điều kiện

- Docker & Docker Compose đang chạy.
- Các cổng `5432`, `6379`, `8000`, `8080`, `8081` còn trống.

## 2. Khởi chạy

```bash
cd azure-ecommerce-application
docker compose up --build -d
docker compose ps
```

- **Frontend Web 4 trang (`PHP 8.5.11`):** `http://localhost:8081`
  - Tài khoản mẫu đã được nạp sẵn (`id = 1`): Username `demo` / Password `password123` (hoặc mua hàng ngay với `DEMO_USER_ID=1`).
  - Danh mục đã nạp sẵn **15 sản phẩm mẫu** trong PostgreSQL và tự động nạp vào Redis (`catalog:products`, TTL 300s).
- **User Service (`Java 25`):** `http://localhost:8080/api/users/health`
- **Order Service (`Python 3.14`):** `http://localhost:8000/health` (Lưu đơn local trong SQLite volume `order-data` khi `ORDER_STORAGE=sqlite`, và lưu vào Azure Cosmos DB khi `ORDER_STORAGE=cosmos` trên AKS).

## 3. Kiểm tra Giao tiếp Liên Dịch vụ (User Service & Order Service)

```bash
curl -s http://localhost:8080/api/users/verify/1
curl -s -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{"user_id":"1","items":[{"product_id":"1","quantity":2,"price":499000}],"total_amount":998000}'
```

## 4. Chạy 3 Kịch bản Thực nghiệm Đo kiểm (Benchmark)

```bash
python3 -m venv .venv
.venv/bin/pip install -r benchmark/requirements.txt

# Thực nghiệm 2: Tốc độ đọc PostgreSQL trực tiếp vs Redis Cache-Aside (catalog:products)
.venv/bin/python benchmark/redis_benchmark.py -n 1000 -c 20

# Thực nghiệm 1: Sinh và đo tốc độ xử lý Dataset > 4GB trên máy Local (so với Azure Synapse)
.venv/bin/python benchmark/dataset_4gb_benchmark.py --file /tmp/ecommerce_benchmark_4gb.csv --size-gb 4.1

# Thực nghiệm 3: Đo độ trễ Cùng Region (Japan East) vs Khác Region (East US)
.venv/bin/python benchmark/cross_region_latency.py -n 50
```

Kết quả đo được tổng hợp trong [`benchmark/RESULTS.md`](../benchmark/RESULTS.md).

## 5. Dừng môi trường Local

`docker compose down` giữ nguyên dữ liệu. Dùng `docker compose down -v` khi muốn xóa sạch volume khởi tạo lại từ đầu.
