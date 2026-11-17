# Chạy local (không tạo tài nguyên Azure)

## Điều kiện

- Docker Desktop đang chạy.
- Các cổng `5432`, `6379`, `8080`, `8081` còn trống.

## Khởi chạy

```powershell
cd azure-ecommerce-application
docker compose up --build -d
docker compose ps
```

Mở frontend tại `http://localhost:8081`. User Service chạy tại `http://localhost:8080`, Order Service chạy tại `http://localhost:8000`. Order Service lưu đơn local trong SQLite volume `order-data`, nên dữ liệu vẫn còn sau khi restart container.

## Kiểm tra User Service

```powershell
$body = @{username='dai'; password='password123'; email='dai@example.com'} | ConvertTo-Json
Invoke-RestMethod http://localhost:8080/api/users/register -Method Post -ContentType application/json -Body $body
Invoke-RestMethod http://localhost:8080/api/users/login -Method Post -ContentType application/json -Body (@{username='dai';password='password123'} | ConvertTo-Json)
Invoke-RestMethod http://localhost:8080/api/users/verify/1
```

## Benchmark PostgreSQL và Redis

```powershell
python -m venv .venv
.\.venv\Scripts\python -m pip install -r benchmark\requirements.txt
.\.venv\Scripts\python benchmark\redis_benchmark.py -n 1000 -c 20
```

Kết quả đo local ngày 17/11/2026 được lưu trong
[`benchmark/RESULTS.md`](benchmark/RESULTS.md) và
[`benchmark/results-local-2026-11-17.csv`](benchmark/results-local-2026-11-17.csv).
Khi dùng cho báo cáo cuối kỳ, nên chạy lại 3–5 lần trên cùng một cấu hình và lấy
giá trị trung bình.

## Dừng

`docker compose down` giữ dữ liệu. Chỉ dùng `docker compose down -v` khi muốn xóa toàn bộ dữ liệu local.

Không chạy Terraform hoặc lệnh tạo Azure từ tài liệu này.
