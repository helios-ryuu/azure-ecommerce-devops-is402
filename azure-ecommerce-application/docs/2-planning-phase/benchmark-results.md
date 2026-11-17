# Kết quả benchmark PostgreSQL, Redis và Cosmos DB

Ngày đo: 17/11/2026

Người thực hiện: Lê Quốc Đại (23520214)

## 1. Môi trường

- PostgreSQL 18 và Redis 7.2 chạy bằng Docker local.
- Azure Cosmos DB for NoSQL, database `ecommerce`, container `orders`, partition key `/userId`.
- Máy khách Windows, Python 3.14.
- Bài đọc: 1.000 requests, concurrency 20.
- Bài ghi: 100 documents tuần tự; dữ liệu benchmark được xóa sau phép đo.

PostgreSQL Azure đã được tạo và kiểm thử kết nối thành công trước đó, nhưng bị một tài khoản thành viên khác xóa trong lúc thực nghiệm. Vì vậy bảng so sánh Cache Hit và DB dưới đây dùng PostgreSQL/Redis local để bảo đảm cùng môi trường mạng.

## 2. Kết quả đọc PostgreSQL và Redis Cache-Aside

| Chỉ số | PostgreSQL | Redis Cache-Aside |
|---|---:|---:|
| Requests | 1.000 | 1.000 |
| Concurrency | 20 | 20 |
| Latency trung bình | 7,149 ms | 2,364 ms |
| P50 | 6,354 ms | 1,646 ms |
| P95 | 9,028 ms | 4,206 ms |
| Throughput | 2.759,62 req/s | 7.913,82 req/s |

Redis giảm 66,9% latency trung bình và đạt throughput cao gấp khoảng 2,87 lần so với đọc trực tiếp PostgreSQL.

## 3. Kết quả ghi Azure Cosmos DB

| Chỉ số | Cosmos DB Write |
|---|---:|
| Writes | 100 |
| Latency trung bình | 92,306 ms |
| P50 | 86,178 ms |
| P95 | 92,772 ms |
| Throughput | 10,83 writes/s |

## 4. Cách chạy lại

```powershell
python -m venv .venv
& ".\.venv\Scripts\python.exe" -m pip install -r ".\benchmark\requirements.txt"
& ".\.venv\Scripts\python.exe" ".\benchmark\redis_benchmark.py" -n 1000 -c 20
```

Với Cosmos DB, đặt `COSMOS_ENDPOINT` và `COSMOS_KEY` trong biến môi trường, sau đó chạy:

```powershell
& ".\.venv\Scripts\python.exe" ".\benchmark\cosmos_write_benchmark.py" -n 100
Remove-Item Env:COSMOS_KEY
```

Không commit endpoint, primary key, mật khẩu hay connection string vào repository.
