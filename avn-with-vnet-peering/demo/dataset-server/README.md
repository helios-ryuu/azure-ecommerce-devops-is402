# Dataset Server (Nginx Docker Compose)

> **Môn học:** IS402 - Điện toán đám mây (Trường ĐH Công Nghệ Thông Tin - UIT)  
> **Repository:** `azure-ecommerce-devops-is402`  
> **Dự án:** `avn-with-vnet-peering`

---

Thư mục này dùng để dựng dịch vụ phân phối bộ dữ liệu nội bộ trong mạng Tailscale (hoặc mạng LAN nội bộ).

## Cấu trúc thư mục:
* `compose.yaml`: File cấu hình Docker Compose chạy Nginx Alpine với chế độ `network_mode: host`.
* `nginx.example.conf`: File cấu hình Nginx mẫu (không chứa thông tin cá nhân).
* `nginx.conf`: File cấu hình Nginx thực tế (đã được cấu hình trong `.gitignore`, an toàn không bị commit).
* `dataset.csv`: File dữ liệu thô >1.2GB (đã được cấu hình trong `.gitignore`, không bị đẩy lên Git).

## Hướng dẫn sử dụng:
1. Sao chép file cấu hình mẫu sang file cấu hình thực tế:
   ```bash
   cp nginx.example.conf nginx.conf
   ```
2. Mở `nginx.conf` và thay thế `<SERVER_IP>` bằng địa chỉ IP Tailscale (hoặc IP LAN) của máy chủ phân phối:
   ```nginx
   listen 100.x.y.z:8000;
   ```
3. Đặt file `dataset.csv` vào cùng thư mục này.
4. Khởi chạy Nginx server dưới nền:
   ```bash
   docker compose up -d
   ```
5. Kiểm tra trạng thái hoạt động:
   ```bash
   docker compose ps
   curl -I http://<SERVER_IP>:8000/dataset.csv
   ```

6. Hướng dẫn client tải nhanh đa luồng bằng `aria2c`:
   Do máy chủ Nginx đã tối ưu `sendfile on;`, `sendfile_max_chunk 8m;` và bật `max_ranges 512` (`Accept-Ranges: bytes`), client nên dùng `aria2c` mở 8 luồng tải song song để đạt tốc độ tối đa:
   ```bash
   sudo apt-get install -y aria2
   aria2c -x 8 -s 8 -k 1M http://<SERVER_IP>:8000/dataset.csv
   ```
