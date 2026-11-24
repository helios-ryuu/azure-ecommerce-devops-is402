# Nhật ký triển khai (Deployment Log — IS402.R11)

1. **`[ĐÃ LÀM]` Ngày 09/11/2026:** Hoàn tất thiết kế kiến trúc tổng thể, vẽ sơ đồ kiến trúc (`architecture.png`) và ASCII Master Diagram (16:9), định nghĩa cấu trúc lưu trữ (PostgreSQL, Redis, Cosmos DB, Storage ADLS Gen2) và luồng giao tiếp 10 bước.  
   - Chi tiết xem tại: [`ARCHITECTURE.md`](./ARCHITECTURE.md)
2. **`[ĐÃ LÀM]` Ngày 10/11/2026:** Tạo Resource Group `(1/3 Task 2.1)`
   - Subscription: Azure for Students
   - Resource group name: rg-is402-ecommerce
   - Region: Japan East
3. **`[ĐÃ LÀM]` Ngày 10/11/2026:** Thêm 2 thành viên làm Contributor trong resource group `(2/3 Task 2.1)`
   - Scope: /subscriptions/ba732803-dbba-4a7c-a523-8dcfed07e3e5/resourceGroups/rg-is402-ecommerce
   - Members:
     - Name: Lê Quốc Đại
       Object ID: 44359b62-65cd-41f0-ad43-86038fa4f012
       Type: User
     - Name: Đào Bảo Phúc
       Object ID: 024ccfa2-2177-4033-a519-ce0b3b0c34a9
       Type: User
       Description: No description
4. **`[ĐÃ LÀM]` Ngày 10/11/2026:** Tạo Budget `(3/3 Task 2.1 — Hoàn thành)`
   - Budget name: budget-is402-ecommerce
   - Time period: Monthly
   - Alert conditions:
       - Actual cost: 50% of budget (12.5) → Action group: None
       - Actual cost: 80% of budget (20) → Action group: None
       - Forecasted cost: 90% of budget (22.5) → Action group: None
      - Alert recipients (email): 23521367@gm.uit.edu.vn
      - Action groups: None
      - Language preference: English (United States)
5. **`[ĐÃ LÀM]` Ngày 10/11/2026:** Tạo Virtual Network `(1/3 Task 2.2)`
    - Subscription: Azure for Students
    - Resource group: rg-is402-ecommerce
    - Region: Japan East
    - Azure Bastion: Disabled
    - Azure Firewall: Disabled
    - Azure DDoS Network Protection: Disabled
    - Address space: 10.0.0.0/16 (65,536 địa chỉ)
    - Subnets:
       - snet-aks: 10.0.1.0/24 (256 địa chỉ)
       - snet-data: 10.0.2.0/24 (256 địa chỉ)
6. **`[ĐÃ LÀM]` Ngày 10/11/2026:** Tạo Azure Cosmos DB for NoSQL `(1/4 Task 2.4)`
   - Subscription: Azure for Students
   - Resource group: rg-is402-ecommerce
   - Region: Japan East
   - Capacity mode: Provisioned throughput
   - Geo-redundancy: Disabled
   - Multi-region writes: Disabled
   - Availability zones: Disabled
   - Backup policy: Periodic
   - Backup storage redundancy: Locally-redundant backup storage
   - Key-based authentication: Enabled
   - Data encryption: Service Managed
   - Connectivity method: All networks
   - Minimum TLS protocol: TLS 1.2
7. **`[ĐÃ LÀM]` Ngày 10/11/2026:** Tạo Azure Storage Account ADLS Gen2 `(2/4 Task 2.4)`
   - Subscription: Azure for Students
   - Resource group: rg-is402-ecommerce
   - Region: Japan East
   - Storage account name: stis402lakehouse
   - Performance: Standard
   - Redundancy: Locally-redundant storage (LRS)
   - Access tier: Hot
   - Hierarchical namespace (ADLS Gen2): Enabled
   - NFS v3 / SFTP / Cross-tenant replication: Disabled
   - Public network access: Enabled (All networks)
   - Routing preference: Microsoft network routing
   - Require secure transfer (HTTPS-only): Enabled
   - Blob anonymous access: Disabled
   - Storage account key access: Enabled
   - Minimum TLS version: TLS 1.2
   - Encryption type: Microsoft-managed keys (Microsoft.Storage)
   - Data protection (Soft delete, Versioning, Change feed, PITR): Disabled
   - Lệnh Azure CLI đã thực thi:
     ```bash
     az storage account create \
       --name stis402lakehouse \
       --resource-group rg-is402-ecommerce \
       --location japaneast \
       --sku Standard_LRS \
       --kind StorageV2 \
       --access-tier Hot \
       --enable-hierarchical-namespace true \
       --enable-nfs-v3 false \
       --enable-sftp false \
       --allow-cross-tenant-replication false \
       --public-network-access Enabled \
       --default-action Allow \
       --routing-choice MicrosoftRouting \
       --https-only true \
       --allow-blob-public-access false \
       --allow-shared-key-access true \
       --min-tls-version TLS1_2 \
       --encryption-key-source Microsoft.Storage
     ```
8. **`[ĐÃ LÀM]` Ngày 11/11/2026:** Tạo 2 Containers trong `stis402lakehouse` trên Web `(3/4 Task 2.4)`
   - Containers:
       - `orders-etl` (Anonymous access level: Private)
       - `benchmark-dataset` (Anonymous access level: Private)
9. **`[ĐÃ LÀM]` Ngày 11/11/2026:** Tạo Database & Container trong `cosmos-is402-nosql` trên Web `(4/4 Task 2.4 — Hoàn thành)`
   - Database id: `ecommerce`
   - Container id: `orders`
   - Partition key: `/userId`
   - Container throughput: 400 RU/s (Manual)
10. **`[ĐÃ LÀM]` Ngày 11/11/2026:** Tạo Azure Synapse Analytics Workspace trên Web `(1/4 Task 4.2)`
    - Subscription: Azure for Students
    - Resource group: rg-is402-ecommerce
    - Region: Japan East
    - Workspace name: synw-is402
    - Data Lake Storage Gen2 account: `https://stis402lakehouse.dfs.core.windows.net`
    - Data Lake Storage Gen2 file system: `synapse-fs`
    - Role assignments: `Storage Blob Data Contributor` (workspace managed identity & current user)
    - Authentication method: Use both local and Microsoft Entra ID authentication
    - SQL Server admin login: `synapseadmin`
    - Double encryption: No
    - Managed virtual network: No
    - Allow connections from all IP addresses: Yes
    - SQL Pool sử dụng: Built-in Serverless SQL Pool (`$5.00/TB` quét dữ liệu; chờ = `$0`)
11. **`[ĐÃ LÀM]` Ngày 11/11/2026:** Tạo Azure Function App trên Web `(1/3 Task 4.1)`
    - Subscription: Azure for Students (`ba732803-dbba-4a7c-a523-8dcfed07e3e5`)
    - Resource group: rg-is402-ecommerce
    - Region: Japan East
    - Function App name: `func-is402-etl`
    - Hosting plan: Flex Consumption (`ASP-rgis402ecommerce-9843`)
    - Operating System: Linux
    - Runtime stack: Python 3.14
    - Instance size: 512 MB (Scale to zero = `$0` khi nhàn rỗi)
    - Storage account đi kèm (Host & Deployment): `stis402func` (New)
    - Application Insights: Not enabled
    - Azure OpenAI: Not enabled
    - Basic authentication: Enabled
    - Continuous deployment: Not enabled
    - Authentication type: Secrets
    - **Cập nhật 17/11/2026 (`[ĐÃ LÀM]` — Kiểm toán hạ tầng, xóa tài nguyên của thành viên khác & chuẩn hóa mã nguồn):**
      - Hợp nhất PR #1, sửa lỗi và hoàn thiện toàn bộ mã nguồn 3 microservices (`frontend-php`, `user-service`, `order-service`), `azure-functions/`, `synapse/`, `k8s/` và `benchmark/`.
      - Kiểm tra toàn diện Azure Activity Log, Deployments, Storage và Cosmos DB để xóa sạch 100% tài nguyên/dữ liệu thử nghiệm không phải do bạn (`23521367@ms.uit.edu.vn`) tạo:
        1. Xóa `pg-is402-db-23520214` và 2 bản ghi Deployment (`PostgreSqlFlexibleServer_...`, `firewallRules-...`) do `23520214@ms.uit.edu.vn` tạo thử.
        2. Xóa cụm `aks-is402-ecommerce` kèm Resource Group phụ `MC_rg-is402-ecommerce_aks-is402-ecommerce_japaneast` (do `23521192@ms.uit.edu.vn` tạo ngày 11/11 với ổ cứng mặc định `128 GiB` và tự sinh VNet riêng tốn phí ngầm).
        3. Xóa `acris402ecommerce` (do `23521192@ms.uit.edu.vn` tạo thử ngày 11/11).
        4. Xóa bản ghi đơn hàng test (`id: 5b1fdc44-248d-43d5-8197-75fd5816964c`, `userId: u123`) do `23521192@ms.uit.edu.vn` chèn thử trong `cosmos-is402-nosql` (`ecommerce/orders`).
      - Lệnh dọn dẹp đã thực thi:
        ```bash
        az aks delete --resource-group rg-is402-ecommerce --name aks-is402-ecommerce --yes
        az acr delete --resource-group rg-is402-ecommerce --name acris402ecommerce --yes
        az deployment group delete -g rg-is402-ecommerce -n firewallRules-f114d2b7-0ecb-4b10-8d89-3fc84c413f0b-0
        az deployment group delete -g rg-is402-ecommerce -n PostgreSqlFlexibleServer_4fa077d65a0444f3bc69a25d768625fe
        ```
12. **`[ĐÃ LÀM]` Ngày 17/11/2026:** Tạo Azure Container Registry `acris402ecommerce` trên Web `(2/3 Task 2.2)`
    - Subscription: Azure for Students
    - Resource group: rg-is402-ecommerce
    - Region: Japan East
    - Registry name: `acris402ecommerce` (`acris402ecommerce.azurecr.io`)
    - Domain name label scope: Unsecure
    - Availability zones: Disabled
    - Pricing plan: Basic (`~$5/tháng`)
    - Role assignment permissions mode: RBAC Registry Permissions
    - Public network access: Yes (IPv4 only)
    - Encryption: Customer-Managed Key Disabled (Microsoft-managed)
13. **`[ĐÃ LÀM]` Ngày 17/11/2026:** Tạo Azure DB for PostgreSQL Flexible Server `pg-is402-db` trên Web & nạp schema `(1/3 & 2/3 Task 2.3 — Hoàn thành)`
    - Subscription: Azure for Students
    - Resource group: rg-is402-ecommerce
    - Region: Japan East
    - Server name: `pg-is402-db` (`pg-is402-db.postgres.database.azure.com`)
    - PostgreSQL version: `18`
    - Compute + Storage: Burstable `Standard_B1ms` (1 vCore, 2 GiB RAM), `32 GiB` storage (`P4`)
    - Networking: Public access Enabled + Allow public access from any Azure service (`Yes`) + Firewall rule (`AllowAll_2026-11-17_10-28-48`: `0.0.0.0 - 255.255.255.255`)
    - Database & Schema: Đã tạo database `ecommerce` và nạp thành công `database/init.sql` (`users`: 1 user mẫu `demo`, `products`: 15 sản phẩm mẫu)
14. **`[ĐÃ LÀM]` Ngày 17/11/2026:** Tạo Azure Cache for Redis `redis-is402-cache` trên Web `(3/3 Task 2.3 — Hoàn thành)`
    - Subscription: Azure for Students
    - Resource group: rg-is402-ecommerce
    - Region: Japan East
    - DNS name: `redis-is402-cache` (`redis-is402-cache.redis.cache.windows.net`)
    - Cache SKU: `Basic C0` (250 MB)
    - Redis version: `6.0`
    - Ports & Auth: Non-TLS port Disabled, TLS port `6380`, Access Keys Authentication Enabled
15. **`[ĐÃ LÀM]` Ngày 17/11/2026:** Tạo cụm AKS `aks-is402-ecommerce` bằng Azure CLI `(3/3 Task 2.2 — Hoàn thành)`
    - Subscription: Azure for Students
    - Resource group: rg-is402-ecommerce (Managed RG: `MC_rg-is402-ecommerce_aks-is402-ecommerce_japaneast`)
    - Region: Japan East (`Zone 3`)
    - Kubernetes version: `1.36.5` | Tier: `Free`
    - Node pool (`nodepool1`): `1` node `Standard_B2als_v2` (2 vCPUs, 4 GiB RAM), `Ubuntu`, OS Disk `32 GiB` (`Managed`)
    - Networking: `Azure CNI Overlay` gắn vào `vnet-is402-ecommerce/snet-aks`, `serviceCidr: 10.100.0.0/16`, `dnsServiceIp: 10.100.0.10`, `podCidr: 10.244.0.0/16`
    - Add-ons & Integrations: `--enable-app-routing` (Managed NGINX Ingress Controller), `--attach-acr acris402ecommerce` (`AcrPull`)
    - Lệnh Azure CLI đã thực thi:
      ```bash
      SUBNET_ID=$(az network vnet subnet show -g rg-is402-ecommerce --vnet-name vnet-is402-ecommerce -n snet-aks --query id -o tsv)
      az aks create \
        --resource-group rg-is402-ecommerce \
        --name aks-is402-ecommerce \
        --location japaneast \
        --tier free \
        --node-vm-size Standard_B2als_v2 \
        --node-count 1 \
        --node-osdisk-size 32 \
        --os-sku Ubuntu \
        --zones 3 \
        --network-plugin azure \
        --network-plugin-mode overlay \
        --vnet-subnet-id "$SUBNET_ID" \
        --service-cidr 10.100.0.0/16 \
        --dns-service-ip 10.100.0.10 \
        --enable-app-routing \
        --attach-acr acris402ecommerce \
        --generate-ssh-keys
      ```
16. **`[ĐÃ LÀM]` Ngày 20/11/2026:** Đóng gói Docker Images 3 dịch vụ đẩy lên ACR & triển khai K8s Manifests lên AKS `(Gói 3 — Hoàn thành)`
    - Build & Push thành công 3 container images lên `acris402ecommerce.azurecr.io`: `frontend-php:latest`, `user-service:latest`, `order-service:latest`.
    - Khởi tạo `k8s/app-secrets.secret.yaml` (đã `.gitignore`) & triển khai toàn bộ manifests lên `aks-is402-ecommerce` bằng kubeconfig riêng (`secrets/aks-kubeconfig.yaml`).
    - Toàn bộ 4 Pods (`frontend-web`, `user-service`, `order-service-blue`, `order-service-green`) đạt trạng thái `1/1 Running` (`0` restarts).
    - Managed NGINX Ingress (`ecommerce-ingress`, class `webapprouting.kubernetes.azure.com`) nhận Public IP `20.27.160.34`, gắn DNS name label **`is402-ecommerce`** $\rightarrow$ truy cập trực tiếp tại **`http://is402-ecommerce.japaneast.cloudapp.azure.com`**.
17. **`[ĐÃ LÀM]` Ngày 20/11/2026:** Kiểm thử Web 4 trang qua Ingress & chuyển đổi Blue/Green (`0%` downtime) `(Tasks 3.1–3.4 — Hoàn thành)`
    - Đặt mua thành công đơn hàng trên `v1-blue` (Cosmos DB Document ID: `d39547c3-fe84-4143-a037-426a5c2c62af`, xác thực nội bộ `user-svc:8080` `User ID #1`).
    - Chạy `bash azure-ecommerce-application/k8s/switch-blue-green.sh green` $\rightarrow$ đặt mua thành công đơn hàng tiếp theo lập tức chuyển sang **`v2-green`** (Cosmos DB Document ID: `8450736e-81b1-4cf3-bf3f-9c4954b9109b`) không gián đoạn, sau đó rollback về `blue` thành công.
18. **`[ĐÃ LÀM]` Ngày 20/11/2026:** Cấu hình biến môi trường & Deploy hàm Auto-ETL (`azure-functions/`) lên `func-is402-etl` `(Task 4.1 — Hoàn thành)`
    - Cấu hình 2 App Settings (`CosmosDBConnection`, `LAKEHOUSE_STORAGE_CONNECTION_STRING`) và deploy gói zip (`--build-remote true`) lên `func-is402-etl` (`Python 3.14` Flex Consumption).
    - Xác nhận trigger `func-is402-etl/orders_auto_etl` (`cosmosDBTrigger`) hoạt động: tự tạo container `leases` trong `cosmos-is402-nosql/ecommerce` và tự động ETL 2 đơn hàng sang **`stis402lakehouse/orders-etl/orders_20261120_112219_414523.jsonl`** (`464 bytes`).
19. **`[ĐÃ LÀM]` Ngày 20/11/2026:** Chạy script `synapse/create_views.sql` trên `synw-is402` & dựng 1 trang Power BI Dashboard `(Task 4.2 — Hoàn thành)`
    - Tạo database `ecommerce_bi` và View `dbo.vw_orders_summary` (script `create_views`) trên Synapse Studio (`synw-is402`) đọc trực tiếp `stis402lakehouse/orders-etl/*.jsonl`.
    - Kết nối Power BI Service (`app.powerbi.com`) tới `synw-is402-ondemand.sql.azuresynapse.net` (connector `Azure Synapse Analytics (SQL DW)`), tạo Semantic model `is402-ecommerce-orders` và dựng hoàn chỉnh 1 trang Dashboard **`IS402 Sales & Orders Overview`** (3 KPI Cards: Tổng doanh thu `158.00K`, Tổng số đơn `2`, Tổng số lượng `2`; Column Chart doanh thu theo thời gian; Donut Chart trạng thái đơn hàng; Table chi tiết đơn hàng).
20. **`[ĐÃ LÀM]` Ngày 20/11/2026:** Chạy Thực nghiệm 1: So sánh xử lý Dataset `> 4GB` (`4.1 GB`, `81,920,000` dòng CSV) giữa Local và Cloud Synapse `(Task 4.3 — Hoàn thành)`
    - **Local (`dataset_4gb_benchmark.py` trên `/tmp`):** Sinh file `0.74s`, tổng hợp 5 danh mục mất **`56.36 giây`**.
    - **Cloud (`synw-is402` Serverless SQL đọc `stis402lakehouse/benchmark-dataset/ecommerce_benchmark_4gb.csv`):** Tổng hợp 5 danh mục mất **`30.95 giây`** (`00:00:30.947`, khớp 100% từng đơn vị doanh thu & `16,384,000` đơn/danh mục) $\rightarrow$ **nhanh hơn ~1.82 lần (giảm 45.1% thời gian)** so với Local.
21. **`[ĐÃ LÀM]` Ngày 20/11/2026:** Chạy Thực nghiệm 2: Đo tốc độ đọc/ghi PostgreSQL vs Redis Cache-Aside `(Task 4.4 — Hoàn thành)`
    - **Local Docker (`1,000 req, c=20`):** PostgreSQL `7.89 ms` (`2,502 req/s`) vs Redis `2.33 ms` (`7,979 req/s`) $\rightarrow$ giảm **70.5%** latency, tăng **3.19x** throughput.
    - **Cloud Azure (`pg-is402-db` vs `redis-is402-cache`, `200 req, c=10`, đo từ VN $\rightarrow$ `Japan East`):** PostgreSQL `394.94 ms` (`24.98 req/s`) vs Redis `149.26 ms` (`65.07 req/s`) $\rightarrow$ giảm **62.2%** latency, tăng **2.60x** throughput.
22. **`[ĐÃ LÀM]` Ngày 20/11/2026:** Chạy Thực nghiệm 3: Đo độ trễ mạng 2 Region (`Japan East` vs `Central India`, 50 mẫu RTT) `(Task 4.5 — Hoàn thành)`
    - Tạo tạm `stis402india` tại `centralindia` (do Policy Azure for Students cho phép 5 vùng: `japaneast`, `centralindia`, `eastasia`, `indonesiacentral`, `koreacentral`).
    - Kết quả đo 50 mẫu từ VN: `Japan East` (`stis402lakehouse`) Avg `374.10 ms` (P50 `354.98 ms`, P95 `492.98 ms`) vs `Central India` (`stis402india`) Avg `330.21 ms` (P50 `326.19 ms`, P95 `376.47 ms`).
    - Đã xóa sạch tài nguyên test `stis402india` ngay sau khi đo xong (`az storage account delete`).
23. **`[ĐÃ LÀM]` Ngày 20–21/11/2026:** Thiết lập & chạy thành công GitHub Actions CI/CD (`AZURE_CREDENTIALS`) `(Task 3.5 — Hoàn thành)`
    - Tạo Service Principal `sp-is402-github-cicd` (quyền `Contributor` trên `rg-is402-ecommerce`) và lưu vào GitHub Repository Secret `AZURE_CREDENTIALS`.
    - Tối ưu `resources.requests` (`25m/50m` CPU) & rollout tuần tự cho cụm 1-node AKS (`Standard_B2als_v2`), workflow `.github/workflows/deploy.yml` chạy **`Success`** (`commit 204ea40`, tổng thời gian **`3m 31s`**: Job 1 Build & Push 3 Images `1m 39s` + Job 2 Deploy & Rollout AKS `1m 45s`).
24. **`[ĐÃ LÀM]` Ngày 24/11/2026:** Tân trang giao diện Web (`frontend-php`), hoàn thiện tài liệu & sẵn sàng báo cáo KLTN `(Gói 5 — Hoàn thành 100%)`
    - Nâng cấp toàn bộ `frontend-php/` (`bootstrap.php`, `index.php`, `detail.php`, `add-to-cart.php`, `cart.php`, `login.php`, `register.php`) sang phong cách **Microsoft Minimal Fluent Design** (Mica, Acrylic topbar, SVG inline, responsive) tự động đổi theme **Blue (`#0078d4`) $\leftrightarrow$ Green (`#107c10`)** theo `order-svc/health`.
    - Kiểm tra cú pháp PHP 8.5 (`php -l`: 8/8 file không lỗi), đồng bộ 100% trạng thái trên `WBS.md`, `LOG.md`, `RUNBOOK.md` cho buổi tổng duyệt & báo cáo ngày **24/11/2026**.