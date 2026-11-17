# Kết quả benchmark PostgreSQL và Redis

## Môi trường đo

- Ngày đo: 17/11/2026
- Môi trường: Docker Desktop local trên Windows
- Số yêu cầu cho mỗi chế độ: 1.000
- Mức đồng thời: 20
- Dữ liệu: danh mục sản phẩm trong bảng `products`
- Redis dùng mẫu Cache-Aside; cache được làm nóng trước khi đo

## Kết quả

| Chế độ | Latency trung bình (ms) | P50 (ms) | P95 (ms) | Throughput (req/s) |
| --- | ---: | ---: | ---: | ---: |
| PostgreSQL trực tiếp | 7,885 | 7,086 | 10,781 | 2.502,13 |
| Redis Cache-Aside | 2,325 | 1,713 | 4,676 | 7.979,26 |

## Nhận xét

- Redis giảm latency trung bình khoảng **70,5%** so với đọc trực tiếp PostgreSQL.
- Throughput của Redis cao hơn khoảng **3,19 lần**.
- Kết quả chỉ phản ánh máy local và bộ dữ liệu hiện tại; cần chạy lại trên Azure để có số liệu chính thức của môi trường cloud.
- Phần đo tốc độ ghi đơn hàng vào Cosmos DB chưa thực hiện vì dự án chưa tạo tài nguyên Azure có thể phát sinh phí.

## Cách tái hiện

```powershell
cd azure-ecommerce-application
docker compose up -d
python -m pip install -r benchmark\requirements.txt
python benchmark\redis_benchmark.py -n 1000 -c 20
```

Nên chạy từ 3 đến 5 lần, bỏ lần đầu làm nóng và lấy trung bình các lần còn lại khi dùng số liệu trong báo cáo cuối kỳ.
