<p align="center">
  <a href="https://www.uit.edu.vn/" title="Trường Đại học Công nghệ Thông tin">
    <img src="https://i.imgur.com/WmMnSRt.png" alt="Trường Đại học Công nghệ Thông tin | University of Information Technology">
  </a>
</p>
<h1 align="center"><b>IS402.R11 - ĐIỆN TOÁN ĐÁM MÂY</b></h1>
<p align="center"><b>CẨM NANG TRIỂN KHAI TOÀN TRÌNH TRÊN MICROSOFT AZURE (CLOUD RUNBOOK)</b></p>
<p align="center"><i>Hướng dẫn thực hành từng bước từ khởi tạo hạ tầng (Web Portal & CLI) đến vận hành Demo & Benchmark</i></p>

---

## MỤC LỤC & TRẠNG THÁI THỰC THI

| Giai đoạn / Bước | Nội dung thực hiện | Mục LOG | Trạng thái |
|:---|:---|:---:|:---:|
| **Giai đoạn 1** | **Hạ tầng Nền tảng & Serverless (Do bạn tạo ngày 10–11/11/2026)** | **Mục 1–11** | **`[ĐÃ LÀM]`** |
| └─ [Bước 1](#bước-1-mục-24-log--đã-làm-tạo-resource-group-phân-quyền-iam--cài-đặt-budget-task-21--33) | Tạo `rg-is402-ecommerce`, phân quyền IAM Contributor & tạo `budget-is402-ecommerce` | Mục 2–4 | **`[ĐÃ LÀM]`** |
| └─ [Bước 2](#bước-2-mục-5-log--đã-làm-tạo-virtual-network--2-subnets-task-22--13) | Tạo `vnet-is402-ecommerce` (`snet-aks`, `snet-data`) | Mục 5 | **`[ĐÃ LÀM]`** |
| └─ [Bước 3](#bước-3-mục-6--9-log--đã-làm-tạo-azure-cosmos-db-for-nosql--container-orders-task-24) | Tạo `cosmos-is402-nosql` (Free Tier) + DB `ecommerce` & Container `orders` | Mục 6 & 9 | **`[ĐÃ LÀM]`** |
| └─ [Bước 4](#bước-4-mục-7--8-log--đã-làm-tạo-azure-storage-account-adls-gen2--2-containers-task-24) | Tạo `stis402lakehouse` (ADLS Gen2) + 2 containers `orders-etl` & `benchmark-dataset` | Mục 7 & 8 | **`[ĐÃ LÀM]`** |
| └─ [Bước 5](#bước-5-mục-10-log--đã-làm-tạo-azure-synapse-analytics-workspace-task-42--14) | Tạo `synw-is402` (Serverless SQL Pool + `synapse-fs`) | Mục 10 | **`[ĐÃ LÀM]`** |
| └─ [Bước 6](#bước-6-mục-11-log--đã-làm-tạo-azure-function-app-task-41--13) | Tạo `func-is402-etl` (`Python 3.14` Flex Consumption + `stis402func`) | Mục 11 | **`[ĐÃ LÀM]`** |
| **Giai đoạn 2** | **Khởi tạo ACR, PostgreSQL, Redis & Cụm AKS (Hoàn thành 17/11/2026)** | **Mục 12–15** | **`[ĐÃ LÀM]`** |
| └─ [Bước 7](#bước-7-mục-12-log--đã-làm-tạo-azure-container-registry-acris402ecommerce-task-22--23) | Tạo Azure Container Registry `acris402ecommerce` (`Basic`, `Japan East`) | Mục 12 | **`[ĐÃ LÀM]`** |
| └─ [Bước 8](#bước-8-mục-13-log--đã-làm-tạo-azure-db-for-postgresql-flexible-server-pg-is402-db--nạp-schema-task-23--13--23) | Tạo PostgreSQL `pg-is402-db` (`B1ms`, `32 GiB`) & nạp `database/init.sql` | Mục 13 | **`[ĐÃ LÀM]`** |
| └─ [Bước 9](#bước-9-mục-14-log--đã-làm-tạo-azure-cache-for-redis-redis-is402-cache-task-23--33) | Tạo Azure Cache for Redis `redis-is402-cache` (`Basic C0`) | Mục 14 | **`[ĐÃ LÀM]`** |
| └─ [Bước 10](#bước-10-mục-15-log--đã-làm-tạo-cụm-aks-aks-is402-ecommerce-task-22--33) | Tạo cụm AKS `aks-is402-ecommerce` (`Standard_B2als_v2`, Zone 3, Disk `32 GiB`) | Mục 15 | **`[ĐÃ LÀM]`** |
| **Giai đoạn 3** | **Đóng gói Image, Triển khai AKS, NGINX Ingress & Blue/Green (Hoàn thành 20/11/2026)** | **Mục 16–17** | **`[ĐÃ LÀM]`** |
| └─ [Bước 11](#bước-11-mục-16-log---phần-1--đã-làm-build--push-3-docker-images-lên-acr) | Build & Push 3 Docker Images (`frontend-php`, `user-service`, `order-service`) lên ACR | Mục 16 (P1) | **`[ĐÃ LÀM]`** |
| └─ [Bước 12](#bước-12-mục-16-log---phần-2--đã-làm-tạo-kubernetes-secret--triển-khai-manifests-lên-aks) | Tạo `app-secrets.secret.yaml` & `kubectl apply` toàn bộ `k8s/` lên AKS | Mục 16 (P2) | **`[ĐÃ LÀM]`** |
| └─ [Bước 13](#bước-13-mục-17-log--đã-làm-kiểm-thử-luồng-mua-hàng--chuyển-đổi-bluegreen-0-downtime) | Kiểm thử Web 4 trang qua Ingress & chuyển đổi `v1-blue` $\leftrightarrow$ `v2-green` | Mục 17 | **`[ĐÃ LÀM]`** |
| **Giai đoạn 4** | **Triển khai Azure Functions Auto-ETL, Synapse SQL & Power BI (Hoàn thành 20/11/2026)** | **Mục 18–19** | **`[ĐÃ LÀM]`** |
| └─ [Bước 14](#bước-14-mục-18-log--đã-làm-cấu-hình--deploy-hàm-auto-etl-lên-func-is402-etl-task-41) | Cấu hình biến môi trường & Publish code `azure-functions/` lên `func-is402-etl` | Mục 18 | **`[ĐÃ LÀM]`** |
| └─ [Bước 15](#bước-15-mục-19-log--đã-làm-tạo-view-trên-azure-synapse-synw-is402--kết-nối-power-bi-task-42) | Chạy `synapse/create_views.sql` trên `synw-is402` & dựng Power BI Dashboard | Mục 19 | **`[ĐÃ LÀM]`** |
| **Giai đoạn 5** | **Chạy 3 Kịch bản Thực nghiệm Benchmark & Thiết lập CI/CD (Hoàn thành 20/11/2026)** | **Mục 20–23** | **`[ĐÃ LÀM]`** |
| └─ [Bước 16](#bước-16-mục-2022-log--đã-làm-chạy-3-kịch-bản-thực-nghiệm-đo-kiểm-tasks-43-44-45) | Chạy 3 bài Benchmark (Dataset `> 4GB`, Redis vs DB, Cross-Region `Japan East` vs `Central India`) | Mục 20–22 | **`[ĐÃ LÀM]`** |
| └─ [Bước 17](#bước-17-mục-23-log--đã-làm-thiết-lập-github-actions-cicd-task-35) | Tạo `AZURE_CREDENTIALS` & kích hoạt GitHub Actions CI/CD | Mục 23 | **`[ĐÃ LÀM]`** |
| **Giai đoạn 6** | **Tiết kiệm Chi phí & Dọn dẹp Tài nguyên sau Báo cáo (Đã hoàn tất toàn bộ dự án 24/11/2026)** | **Mục 24** | **`[ĐÃ LÀM]`** |

---

## Giai đoạn 1: Các bước Hạ tầng Nền tảng & Serverless Đã Hoàn thành (Mục 1–11 LOG)

*(Trạng thái: **`[ĐÃ LÀM]` 100%** trên Subscription `Azure for Students` tại Region **`Japan East`** — xem chi tiết nhật ký tại [`LOG.md`](./LOG.md))*

### Bước 1 (Mục 2–4 LOG) — `[ĐÃ LÀM]`: Tạo Resource Group, Phân quyền IAM & Cài đặt Budget (`Task 2.1 — 3/3`)
1. **Tạo Resource Group (Web Portal):**
   - Vào **Resource groups** $\rightarrow$ **+ Create**:
     - Subscription: `Azure for Students`
     - Resource group: `rg-is402-ecommerce`
     - Region: `(Asia Pacific) Japan East`
2. **Phân quyền IAM cho 3 thành viên:**
   - Vào `rg-is402-ecommerce` $\rightarrow$ **Access control (IAM)** $\rightarrow$ **+ Add** $\rightarrow$ **Add role assignment**:
     - Tab **Privileged administrator roles** $\rightarrow$ chọn **`Contributor`**.
     - Chọn tài khoản của **Lê Quốc Đại** (`23520214@ms.uit.edu.vn`) và **Đào Bảo Phúc** (`23521192@ms.uit.edu.vn`) $\rightarrow$ **Review + assign**.
3. **Cài đặt Cost Budget Alert:**
   - Vào `rg-is402-ecommerce` $\rightarrow$ **Cost Management** $\rightarrow$ **Budgets** $\rightarrow$ **+ Add**:
     - Name: `budget-is402-ecommerce` | Amount: **`25`** (USD/tháng).
     - Alert conditions: Actual `50%` ($12.5), Actual `80%` ($20), Forecasted `90%` ($22.5) gửi về email nhóm.

### Bước 2 (Mục 5 LOG) — `[ĐÃ LÀM]`: Tạo Virtual Network & 2 Subnets (`Task 2.2 — 1/3`)
- Vào **Virtual networks** $\rightarrow$ **+ Create**:
  - Resource group: `rg-is402-ecommerce` | Name: `vnet-is402-ecommerce` | Region: `Japan East`.
  - Tab **Security**: **Bỏ tích toàn bộ** (`Virtual network encryption`, `Azure Bastion`, `Azure Firewall`, `Azure DDoS Network Protection` đều **Disabled**).
  - Tab **IP addresses**: Address space `10.0.0.0/16`, tạo 2 Subnets (**bỏ tích** `Enable private subnet` ở cả 2):
    - `snet-aks`: `10.0.1.0/24` (256 địa chỉ)
    - `snet-data`: `10.0.2.0/24` (256 địa chỉ)

### Bước 3 (Mục 6 & 9 LOG) — `[ĐÃ LÀM]`: Tạo Azure Cosmos DB for NoSQL & Container `orders` (`Task 2.4`)
1. **Tạo tài khoản Cosmos DB (Web Portal):**
   - Vào **Azure Cosmos DB** $\rightarrow$ **+ Create** $\rightarrow$ chọn **`Azure Cosmos DB for NoSQL`**:
     - Resource group: `rg-is402-ecommerce` | Account Name: `cosmos-is402-nosql` | Location: `Japan East`.
     - Capacity mode: **`Provisioned throughput`** | **Apply Free Tier Discount:** **`Apply`** (`$0/tháng`).
     - Availability Zones / Geo-Redundancy / Multi-region Writes: **`Disable`**.
     - Networking: **`All networks`** | Backup policy: **`Periodic`** (`Locally-redundant backup storage`) | Encryption: **`Key-based authentication: Enable`**, `Service-managed key`.
2. **Tạo Database & Container (Web Portal):**
   - Vào `cosmos-is402-nosql` $\rightarrow$ **Data Explorer** $\rightarrow$ **New Container**:
     - Database id: `ecommerce` | Container id: `orders` | Partition key: `/userId` | Throughput: **`Manual 400 RU/s`**.

### Bước 4 (Mục 7 & 8 LOG) — `[ĐÃ LÀM]`: Tạo Azure Storage Account ADLS Gen2 & 2 Containers (`Task 2.4`)
1. **Tạo Storage Account `stis402lakehouse` (Azure CLI):**
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
2. **Tạo 2 Containers bên trong `stis402lakehouse` (Web Portal):**
   - Vào `stis402lakehouse` $\rightarrow$ **Data storage $\rightarrow$ Containers** $\rightarrow$ **+ Container** (mức `Private`):
     - `orders-etl`
     - `benchmark-dataset`

### Bước 5 (Mục 10 LOG) — `[ĐÃ LÀM]`: Tạo Azure Synapse Analytics Workspace (`Task 4.2 — 1/4`)
- Vào **Azure Synapse Analytics** $\rightarrow$ **+ Create**:
  - Resource group: `rg-is402-ecommerce` | Workspace name: `synw-is402` | Region: `Japan East`.
  - Select Data Lake Storage Gen2: Account `stis402lakehouse` | File system: `(New) synapse-fs`.
  - Tích chọn `Assign myself the Storage Blob Data Contributor role...`.
  - Tab **Security**: SQL Server admin login `synapseadmin` + mật khẩu; Double encryption: `Disable`.
  - Tab **Networking**: Managed virtual network: `No` | Allow connections from all IP addresses: `Yes`.

### Bước 6 (Mục 11 LOG) — `[ĐÃ LÀM]`: Tạo Azure Function App (`Task 4.1 — 1/3`)
- Vào **Function App** $\rightarrow$ **+ Create** $\rightarrow$ chọn gói **`Flex Consumption`**:
  - Resource group: `rg-is402-ecommerce` | Name: `func-is402-etl` | Region: `Japan East`.
  - Runtime stack: **`Python 3.14`** | Instance size: **`512 MB`** | Zone redundancy: **`Disabled`**.
  - Tab **Storage**: Tạo mới `stis402func` (`Standard_LRS`) | Blob diagnostic settings: `Configure later`.
  - Tab **Networking**: Public access `On` | VNet integration `Off`.
  - Tab **Monitoring**: Enable Application Insights: **`No`**.
  - Tab **Durable Functions**: Create Durable Task Scheduler: **`No`**.

> [!IMPORTANT]
> - **Điểm bắt đầu tiếp theo:** Bắt đầu thực hiện từ **Bước 7 (Mục 12 LOG)** ngay bên dưới.

---

## Giai đoạn 2: Khởi tạo ACR, PostgreSQL, Redis & Cụm AKS (Mục 12–15 LOG)

*(Thực hiện ngay trên Web Portal / CLI để chuẩn bị cho buổi báo cáo **24/11/2026**)*

### Bước 7 (Mục 12 LOG) — `[ĐÃ LÀM]`: Tạo Azure Container Registry `acris402ecommerce` (`Task 2.2 — 2/3`)
- **Cách 1 (Trên Web Portal):**
  - Tìm **`Container registries`** $\rightarrow$ **+ Create**:
    - Resource group: `rg-is402-ecommerce`
    - Registry name: `acris402ecommerce`
    - Location: `(Asia Pacific) Japan East`
    - Pricing plan (SKU): **`Basic`** (~`$5/tháng`)
  - Bấm **Review + create** $\rightarrow$ **Create**.
- **Cách 2 (Bằng 1 lệnh CLI):**
  ```bash
  az acr create \
    --resource-group rg-is402-ecommerce \
    --name acris402ecommerce \
    --sku Basic \
    --location japaneast
  ```

### Bước 8 (Mục 13 LOG) — `[ĐÃ LÀM]`: Tạo Azure DB for PostgreSQL Flexible Server `pg-is402-db` & Nạp Schema (`Task 2.3 — 1/3 & 2/3`)
1. **Tạo trên Web Portal:**
   - Tìm **`Azure Database for PostgreSQL flexible servers`** $\rightarrow$ **+ Create**:
     - Resource group: `rg-is402-ecommerce` | Server name: `pg-is402-db` | Region: `(Asia Pacific) Japan East`.
     - PostgreSQL version: **`18`** *(hoặc bản cao nhất hiện có như `17`/`16`)*.
     - Workload type: Chọn **`Development`** (tự động chọn **`Burstable`**, **`Standard_B1ms`** 1 vCPU / 2 GiB RAM, Storage **`32 GiB`**, tắt High Availability).
     - Authentication method: **`PostgreSQL authentication only`** $\rightarrow$ Admin username: `pgadmin` + mật khẩu mạnh (lưu lại).
   - Tab **Networking**:
     - Connectivity method: Chọn **`Public access (allowed IP addresses)`**.
     - Tích chọn **`Allow public access from any Azure service within Azure to this server`**.
     - Tích chọn **`Add current client IP address`** (hoặc thêm rule `0.0.0.0 - 255.255.255.255` khi demo/test).
   - Bấm **Review + create** $\rightarrow$ **Create**.
2. **Tạo database `ecommerce` & Chạy script `database/init.sql` (nạp user mẫu + 15 sản phẩm):**
   - Vào `pg-is402-db` trên Portal $\rightarrow$ menu trái chọn **`Databases`** $\rightarrow$ **+ Add** tạo database tên **`ecommerce`**.
   - Trên máy cá nhân, chạy lệnh nạp bảng và 15 sản phẩm mẫu:
     ```bash
     psql "host=pg-is402-db.postgres.database.azure.com port=5432 dbname=ecommerce user=pgadmin sslmode=require" \
       -f azure-ecommerce-application/database/init.sql
     ```

### Bước 9 (Mục 14 LOG) — `[ĐÃ LÀM]`: Tạo Azure Cache for Redis `redis-is402-cache` (`Task 2.3 — 3/3`)
1. **Tạo trên Web Portal:**
   - Tìm **`Azure Cache for Redis`** $\rightarrow$ **+ Create**:
     - Resource group: `rg-is402-ecommerce`
     - DNS name: `redis-is402-cache`
     - Location: `(Asia Pacific) Japan East`
     - Cache SKU: Chọn **`Basic`** $\rightarrow$ **`C0 (250 MB)`** *(rẻ nhất)*.
   - Tab **Networking**: Chọn **`Public endpoint`**.
   - Tab **Advanced**: Giữ cổng TLS `6380` (có thể bỏ tích Microsoft Entra Authentication nếu muốn dùng Access Key thuần).
   - Bấm **Review + create** $\rightarrow$ **Create**.
2. **Lấy Primary Access Key:**
   - Sau khi tạo xong, vào `redis-is402-cache` $\rightarrow$ **Settings $\rightarrow$ Access keys** (hoặc **Authentication $\rightarrow$ Access keys**), tích bật `Access keys authentication` và copy **Primary key**.

### Bước 10 (Mục 15 LOG) — `[ĐÃ LÀM]`: Tạo Cụm AKS `aks-is402-ecommerce` (`Task 2.2 — 3/3`)
1. **Tạo trên Web Portal:**
   - Tìm **`Kubernetes services`** $\rightarrow$ **+ Create** $\rightarrow$ **Create a Kubernetes cluster**:
   - **Tab `Basics`:**
     - Resource group: `rg-is402-ecommerce` | Cluster name: `aks-is402-ecommerce` | Region: `(Asia Pacific) Japan East`.
     - Cluster preset configuration: **`Dev/Test`**.
     - Availability zones: Chọn **`Zone 3`** *(hoặc `None` — vì `Standard_B2als_v2` ở `Japan East` mở tại Zone 3)*.
     - AKS pricing tier: **`Free`** (`$0` Control Plane).
     - Automatic upgrade: **`Disabled`**.
     - Authentication and Authorization: **`Local accounts with Kubernetes RBAC`**.
   - **Tab `Node pools`:**
     - Bấm vào `agentpool` $\rightarrow$ Node size: Chọn **`Standard_B2als_v2`** (2 vCPU, 4 GiB RAM, `$35.77/tháng`).
     - Scale method: **`Manual`** | Node count: **`2`** (hoặc `1`) | **OS disk size:** Hạ xuống **`32 GiB`**.
   - **Tab `Networking`:**
     - Network configuration: Chọn **`Azure CNI Overlay`** (hoặc `Azure CNI`) $\rightarrow$ tích **`Bring your own Azure virtual network`** $\rightarrow$ chọn VNet `vnet-is402-ecommerce` và Subnet `snet-aks`.
     - **Application routing:** Tích bật **`Enable application routing`** (Managed NGINX Ingress Controller).
   - **Tab `Integrations`:**
     - Container registry: Chọn **`acris402ecommerce`**.
   - **Tab `Monitoring`:**
     - **Bỏ tích** `Enable Container insights` và `Enable Managed Prometheus` (tiết kiệm chi phí log).
   - Bấm **Review + create** $\rightarrow$ **Create**.
2. **Kết nối `kubectl` từ máy cá nhân vào cụm AKS (lưu ra file riêng trong `secrets/` để không ghi đè `~/.kube/config` của cụm khác):**
   ```bash
   az aks get-credentials \
     --resource-group rg-is402-ecommerce \
     --name aks-is402-ecommerce \
     --file azure-ecommerce-application/secrets/aks-kubeconfig.yaml

   export KUBECONFIG="$(pwd)/azure-ecommerce-application/secrets/aks-kubeconfig.yaml"
   kubectl get nodes -o wide
   kubectl get pods -n app-routing-system
   ```

> [!IMPORTANT]
> **Giai đoạn 2 đã hoàn thành 100% (17/11/2026)!** Toàn bộ hạ tầng (`acris402ecommerce`, `pg-is402-db`, `redis-is402-cache`, `aks-is402-ecommerce` dùng `Standard_B2als_v2` qua CLI với `--service-cidr 10.100.0.0/16`) đã sẵn sàng.
> **Điểm bắt đầu tiếp theo:** Chạy `az aks get-credentials` kiểm tra node rồi thực hiện **Giai đoạn 3 — Bước 11 (Build & Push 3 Docker Images lên ACR)**.

---

## Giai đoạn 3: Đóng gói Image, Triển khai AKS, NGINX Ingress & Blue/Green

### Bước 11 (Mục 16 LOG - Phần 1) — `[ĐÃ LÀM]`: Build & Push 3 Docker Images lên ACR
Từ thư mục gốc của repo, đăng nhập ACR (tương thích 100% cả Docker và Podman) rồi build & push 3 images:
```bash
# Đăng nhập ACR bằng token (chạy mượt trên cả Podman & Docker)
ACR_TOKEN=$(az acr login --name acris402ecommerce --expose-token --query accessToken -o tsv)
echo "$ACR_TOKEN" | docker login acris402ecommerce.azurecr.io -u 00000000-0000-0000-0000-000000000000 --password-stdin

docker build -t acris402ecommerce.azurecr.io/frontend-php:latest ./azure-ecommerce-application/frontend-php
docker push acris402ecommerce.azurecr.io/frontend-php:latest

docker build -t acris402ecommerce.azurecr.io/user-service:latest ./azure-ecommerce-application/user-service
docker push acris402ecommerce.azurecr.io/user-service:latest

docker build --target runtime -t acris402ecommerce.azurecr.io/order-service:latest ./azure-ecommerce-application/order-service
docker push acris402ecommerce.azurecr.io/order-service:latest
```

### Bước 12 (Mục 16 LOG - Phần 2) — `[ĐÃ LÀM]`: Tạo Kubernetes Secret & Triển khai Manifests lên AKS
1. **Đảm bảo đang trỏ `kubectl` đúng cụm AKS (không đụng `~/.kube/config` cụm khác):**
   ```bash
   export KUBECONFIG="$(pwd)/azure-ecommerce-application/secrets/aks-kubeconfig.yaml"
   kubectl get nodes -o wide
   ```
2. **Tạo file Secret thật trên máy (đã được `.gitignore` bảo vệ):**
   ```bash
   cp azure-ecommerce-application/k8s/app-secrets.example.yaml azure-ecommerce-application/k8s/app-secrets.secret.yaml
   ```
   Lấy nhanh 2 khóa Redis & Cosmos DB mới nhất bằng CLI (mật khẩu Postgres nằm ở `azure-ecommerce-application/secrets/pgdbb-password.txt`):
   ```bash
   az redis list-keys -g rg-is402-ecommerce -n redis-is402-cache --query primaryKey -o tsv
   az cosmosdb keys list -g rg-is402-ecommerce -n cosmos-is402-nosql --query primaryMasterKey -o tsv
   ```
   Mở `azure-ecommerce-application/k8s/app-secrets.secret.yaml` điền:
   - Mật khẩu `pg-is402-db` vào `DB_PASSWORD` và `SPRING_DATASOURCE_PASSWORD`.
   - Primary key của `redis-is402-cache` vào `REDIS_PASSWORD`.
   - Primary key của `cosmos-is402-nosql` vào `COSMOS_KEY`.
3. **Áp dụng Secret và các Manifests K8s:**
   ```bash
   kubectl apply -f azure-ecommerce-application/k8s/app-secrets.secret.yaml
   kubectl apply -f azure-ecommerce-application/k8s/user-service.yaml
   kubectl apply -f azure-ecommerce-application/k8s/order-service-blue-deployment.yaml
   kubectl apply -f azure-ecommerce-application/k8s/order-service-green-deployment.yaml
   kubectl apply -f azure-ecommerce-application/k8s/order-service-svc.yaml
   kubectl apply -f azure-ecommerce-application/k8s/frontend-web.yaml
   kubectl apply -f azure-ecommerce-application/k8s/ecommerce-ingress.yaml
   ```
4. **Kiểm tra trạng thái Pods, Services & Lấy Public IP / Domain của Ingress:**
   ```bash
   kubectl get pods -o wide
   kubectl get svc
   kubectl get ingress ecommerce-ingress
   ```
   *(Để gắn thêm tên miền `http://is402-ecommerce.japaneast.cloudapp.azure.com` cho Public IP của Managed NGINX Ingress, vào Resource Group phụ `MC_rg-is402-ecommerce_aks-is402-ecommerce_japaneast` trên Portal $\rightarrow$ bấm vào tài nguyên **Public IP address** của `kubernetes` $\rightarrow$ mục **Configuration** $\rightarrow$ điền **DNS name label:** `is402-ecommerce` $\rightarrow$ **Save**!)*

### Bước 13 (Mục 17 LOG) — `[ĐÃ LÀM]`: Kiểm thử Luồng Mua hàng & Chuyển đổi Blue/Green (`0%` Downtime)
1. Mở trình duyệt truy cập `http://is402-ecommerce.japaneast.cloudapp.azure.com` (hoặc địa chỉ `ADDRESS` của `ecommerce-ingress`):
   - Duyệt **Trang chủ** $\rightarrow$ bấm **Xem chi tiết** $\rightarrow$ bấm **Thêm vào giỏ hàng** $\rightarrow$ bấm **Xem giỏ hàng & Mua sản phẩm**.
   - Bấm nút **Mua sản phẩm**: Hệ thống hiển thị thông báo đặt hàng thành công kèm nhãn **`v1-blue`**.
2. **Thực hiện chuyển đổi Blue $\rightarrow$ Green không gián đoạn:**
   ```bash
   bash azure-ecommerce-application/k8s/switch-blue-green.sh green
   ```
   - Đặt mua thêm 1 đơn hàng trên Web: Giao diện lập tức hiển thị nhãn **`v2-green`** mà không hề bị gián đoạn kết nối!
   - Chuyển ngược về `blue` khi cần:
     ```bash
     bash azure-ecommerce-application/k8s/switch-blue-green.sh blue
     ```

---

## Giai đoạn 4: Triển khai Azure Functions Auto-ETL, Synapse SQL & Power BI

### Bước 14 (Mục 18 LOG) — `[ĐÃ LÀM]`: Cấu hình & Deploy hàm Auto-ETL lên `func-is402-etl` (`Task 4.1`)
1. **Cấu hình 2 biến môi trường (có thể làm nhanh bằng CLI hoặc trên Web Portal):**
   - **Cách nhanh (Azure CLI):**
     ```bash
     COSMOS_CONN=$(az cosmosdb keys list -g rg-is402-ecommerce -n cosmos-is402-nosql --type connection-strings --query "connectionStrings[0].connectionString" -o tsv)
     STORAGE_CONN=$(az storage account show-connection-string -g rg-is402-ecommerce -n stis402lakehouse --query connectionString -o tsv)

     az functionapp config appsettings set \
       --resource-group rg-is402-ecommerce \
       --name func-is402-etl \
       --settings \
         "CosmosDBConnection=$COSMOS_CONN" \
         "LAKEHOUSE_STORAGE_CONNECTION_STRING=$STORAGE_CONN"
     ```
   - *(Hoặc trên Web Portal: Vào `func-is402-etl` $\rightarrow$ **Settings $\rightarrow$ Environment variables** $\rightarrow$ **+ Add** thêm `CosmosDBConnection` và `LAKEHOUSE_STORAGE_CONNECTION_STRING` $\rightarrow$ **Apply** $\rightarrow$ **Confirm**).*
2. **Đóng gói & Deploy code từ `azure-functions/` lên `func-is402-etl` (không cần cài `func` Core Tools):**
   ```bash
   (cd azure-ecommerce-application/azure-functions && zip -r /tmp/func-is402-etl.zip function_app.py host.json requirements.txt)

   az functionapp deployment source config-zip \
     --resource-group rg-is402-ecommerce \
     --name func-is402-etl \
     --src /tmp/func-is402-etl.zip \
     --build-remote true
   ```
   *(Sau khi deploy ~15–30 giây, `orders_auto_etl` sẽ tự tạo container `leases`, tự đồng bộ 2 đơn hàng vừa test ở Giai đoạn 3 và mọi đơn hàng mới từ Cosmos DB sang `stis402lakehouse/orders-etl/orders_YYYYMMDD_*.jsonl`!)*
3. **Kiểm tra file `.jsonl` đã xuất hiện trong `stis402lakehouse/orders-etl`:**
   ```bash
   az storage blob list \
     --account-name stis402lakehouse \
     --container-name orders-etl \
     --auth-mode key -o table
   ```

### Bước 15 (Mục 19 LOG) — `[ĐÃ LÀM]`: Tạo View trên Azure Synapse (`synw-is402`) & Kết nối Power BI (`Task 4.2`)
1. **Chạy script SQL trên Synapse Studio:**
   - Vào `synw-is402` trên Azure Portal $\rightarrow$ bấm **Open Synapse Studio** $\rightarrow$ menu trái chọn **Develop** $\rightarrow$ **+ $\rightarrow$ SQL script** (đặt tên script: `create_views`).
   - Bôi đen đoạn tạo database (dòng 6–9) trong [`../synapse/create_views.sql`](../synapse/create_views.sql) $\rightarrow$ bấm **Run**.
   - Đổi dropdown **Use database** trên thanh công cụ từ `master` sang **`ecommerce_bi`**, bôi đen đoạn `CREATE OR ALTER VIEW dbo.vw_orders_summary ...` (dòng 16–33) $\rightarrow$ bấm **Run**.
   - Chạy kiểm tra `SELECT * FROM dbo.vw_orders_summary;` để xác nhận thấy các đơn hàng Auto-ETL $\rightarrow$ bấm **Publish**.
2. **Kết nối trên giao diện Power BI Web (`app.powerbi.com`):**
   - Ở cột menu bên trái, bấm **`My workspace`** $\rightarrow$ **`+ New item`** $\rightarrow$ chọn **`Semantic model`**.
   - Trong khung **Power Query (Get data)**, tìm và chọn connector **`Azure Synapse Analytics (SQL DW)`**:
     - **Server:** `synw-is402-ondemand.sql.azuresynapse.net`
     - **Database:** `ecommerce_bi`
     - **Data gateway:** `(none)` | **Authentication kind:** **`Basic`** (`synapseadmin` + mật khẩu Synapse) — *hoặc **`Organizational account`** nếu cần quyền Entra ID đọc ADLS Gen2*.
     - Tích **`Use encrypted connection`** $\rightarrow$ bấm **Next**.
   - Tích chọn View **`dbo.vw_orders_summary`** $\rightarrow$ kiểm tra bảng preview hiện đủ 7 cột $\rightarrow$ bấm **`Create a report`** $\rightarrow$ nhập **Semantic model name:** **`is402-ecommerce-orders`** $\rightarrow$ bấm **Create**.
   - Khi màn hình Model view của `is402-ecommerce-orders` mở ra, bấm nút **`New report`** trên thanh công cụ **Home** (nhóm **Explore**).
3. **Gợi ý bố cục trình bày 1 trang Dashboard (`IS402 Sales & Orders Overview`):**
   - **Hàng trên cùng (3 thẻ KPI `Card` xếp ngang đều nhau):**
     1. **Card 1 — Tổng doanh thu:** Kéo `totalAmount` vào ô *Fields* (mặc định `Sum of totalAmount`) $\rightarrow$ đổi tiêu đề/label thành **`Tổng doanh thu (VNĐ)`**.
     2. **Card 2 — Tổng số đơn hàng:** Kéo `orderId` vào ô *Fields* $\rightarrow$ bấm mũi tên chọn **`Count`** $\rightarrow$ đổi label thành **`Tổng số đơn hàng`**.
     3. **Card 3 — Tổng sản phẩm đã bán:** Kéo `itemsCount` vào ô *Fields* (`Sum of itemsCount`) $\rightarrow$ đổi label thành **`Tổng số lượng bán`**.
   - **Hàng giữa bên trái (Chiếm ~60% chiều ngang — Biểu đồ cột `Clustered column chart`):**
     - **X-axis:** `createdAt` *(bấm mũi tên đổi từ Date Hierarchy sang giá trị `createdAt` gốc để hiện từng mốc giờ/phút)* hoặc `orderId`.
     - **Y-axis:** `Sum of totalAmount`.
     - Tiêu đề: **`Doanh thu theo đơn hàng / thời gian`**.
   - **Hàng giữa bên phải (Chiếm ~40% chiều ngang — Biểu đồ tròn `Donut chart` hoặc `Pie chart`):**
     - **Legend:** `orderStatus` (hoặc `userId`).
     - **Values:** `Count of orderId`.
     - Tiêu đề: **`Phân bổ đơn hàng theo Trạng thái`**.
   - **Hàng dưới cùng (Chiếm 100% chiều ngang — Bảng chi tiết `Table`):**
     - Kéo các cột: `orderId`, `userId`, `itemsCount`, `totalAmount`, `orderStatus`, `createdAt`, `etlProcessedAt`.
     - Giúp Giảng viên thấy trực quan ngay trên Dashboard toàn bộ đơn hàng thật vừa đặt từ Web PHP đã tự động chảy qua Cosmos DB $\rightarrow$ Azure Functions $\rightarrow$ ADLS Gen2 $\rightarrow$ Synapse $\rightarrow$ Power BI!
   - Bấm **File $\rightarrow$ Save**, đặt tên report **`IS402 Sales & Orders Overview`** (khi cần cập nhật đơn mới từ Web, chỉ cần vào `My workspace` bấm **Refresh** trên semantic model `is402-ecommerce-orders` rồi bấm **Refresh** trên report).

---

## Giai đoạn 5: Chạy 3 Kịch bản Thực nghiệm Benchmark & Thiết lập CI/CD

### Bước 16 (Mục 20–22 LOG) — `[ĐÃ LÀM]`: Chạy 3 Kịch bản Thực nghiệm Đo kiểm (`Tasks 4.3, 4.4, 4.5`)
1. **Thực nghiệm 1 — So sánh xử lý Dataset `> 4GB` (Local vs Cloud Synapse — `0.75đ`):**
   - **Đo trên máy Local** (sinh file `4.1 GB` CSV trên `/tmp` và đo thời gian tổng hợp bằng Python):
     ```bash
     python3 azure-ecommerce-application/benchmark/dataset_4gb_benchmark.py --file /tmp/ecommerce_benchmark_4gb.csv --size-gb 4.1
     ```
   - **Upload dataset `4.1 GB` lên `stis402lakehouse/benchmark-dataset/`:**
     ```bash
     az storage blob upload \
       --account-name stis402lakehouse \
       --container-name benchmark-dataset \
       --name ecommerce_benchmark_4gb.csv \
       --file /tmp/ecommerce_benchmark_4gb.csv \
       --auth-mode key \
       --overwrite
     ```
     *(Xong nhớ xóa file tạm Local `rm -f /tmp/ecommerce_benchmark_4gb.csv` để giải phóng RAM `/tmp`!)*
   - **Đo trên Cloud (Synapse Serverless SQL):** Mở **Synapse Studio (`synw-is402`)** $\rightarrow$ chạy **phần 2** (dòng 36–51) trong [`../synapse/create_views.sql`](../synapse/create_views.sql) và ghi nhận thời gian chạy (giây) để so sánh với kết quả Local!
2. **Thực nghiệm 2 — Đo tốc độ Đọc/Ghi PostgreSQL vs Redis Cache-Aside (`1.0đ`):**
   - Cài tạm 2 thư viện trong `.venv` (nếu chưa có):
     ```bash
     python3 -m venv /tmp/bench-venv && /tmp/bench-venv/bin/pip install -q "psycopg[binary,pool]" redis
     ```
   - Chạy đo trực tiếp vào `pg-is402-db` và `redis-is402-cache` trên Azure (tự động URL-encode mật khẩu chứa ký tự đặc biệt như `@`, `/`, `+`):
     ```bash
     PG_PASS=$(python3 -c "import urllib.parse; print(urllib.parse.quote(open('azure-ecommerce-application/secrets/pgdbb-password.txt').read().strip(), safe=''))")
     REDIS_KEY=$(az redis list-keys -g rg-is402-ecommerce -n redis-is402-cache --query primaryKey -o tsv | python3 -c "import sys, urllib.parse; print(urllib.parse.quote(sys.stdin.read().strip(), safe=''))")

     DB_DSN="postgresql://pgadmin:${PG_PASS}@pg-is402-db.postgres.database.azure.com:5432/ecommerce?sslmode=require" \
     REDIS_URL="rediss://:${REDIS_KEY}@redis-is402-cache.redis.cache.windows.net:6380/0" \
     /tmp/bench-venv/bin/python azure-ecommerce-application/benchmark/redis_benchmark.py -n 200 -c 10
     ```
     *(Lưu ý: Nếu muốn đo không bị cộng độ trễ mạng từ VN sang Nhật Bản, có thể chạy kết hợp cùng bảng đo Local trong `benchmark/RESULTS.md` để so sánh rõ độ trễ nội bộ vs từ xa!)*
3. **Thực nghiệm 3 — Đo độ trễ 2 Region (`Japan East` vs `Central India` — `0.5đ`):**
   - Tạo tạm 1 Storage Account test tại `Central India`:
     ```bash
     az storage account create --name stis402india --resource-group rg-is402-ecommerce --location centralindia --sku Standard_LRS --kind StorageV2
     ```
   - Chạy script đo 50 mẫu RTT (`Min`, `Avg`, `P50`, `P95`, `Max`):
     ```bash
     python3 azure-ecommerce-application/benchmark/cross_region_latency.py -n 50 --cross-region-url "https://stis402india.blob.core.windows.net/?comp=list"
     ```
   - Đo xong xóa ngay tài nguyên test ở `Central India`:
     ```bash
     az storage account delete --name stis402india --resource-group rg-is402-ecommerce --yes
     ```

### Bước 17 (Mục 23 LOG) — `[ĐÃ LÀM]`: Thiết lập GitHub Actions CI/CD (`Task 3.5`)
1. Tạo Service Principal cấp quyền trên `rg-is402-ecommerce` để lấy JSON cho GitHub Secret `AZURE_CREDENTIALS`:
   ```bash
   SUBSCRIPTION_ID=$(az account show --query id -o tsv)
   az ad sp create-for-rbac \
     --name "sp-is402-github-cicd" \
     --role Contributor \
     --scopes "/subscriptions/$SUBSCRIPTION_ID/resourceGroups/rg-is402-ecommerce" \
     --sdk-auth
   ```
2. Copy toàn bộ chuỗi JSON vừa in ra $\rightarrow$ vào GitHub Repository $\rightarrow$ **Settings $\rightarrow$ Secrets and variables $\rightarrow$ Actions** $\rightarrow$ **New repository secret**:
   - Name: `AZURE_CREDENTIALS` | Secret: dán chuỗi JSON vào.
3. Từ lúc này, mỗi khi push code lên nhánh `main` (hoặc bấm **Run workflow** trong tab **Actions**), workflow [`.github/workflows/deploy.yml`](../../.github/workflows/deploy.yml) sẽ tự động build 3 images, push lên `acris402ecommerce` và rollout lên `aks-is402-ecommerce`!

---

## Giai đoạn 6 — `[ĐÃ LÀM]`: Tiết kiệm Chi phí & Dọn dẹp Tài nguyên sau Báo cáo

- **Tạm dừng (`Stop`) cụm AKS và PostgreSQL khi nghỉ ngơi chưa dùng tới:**
  ```bash
  az aks stop --resource-group rg-is402-ecommerce --name aks-is402-ecommerce
  az postgres flexible-server stop --resource-group rg-is402-ecommerce --name pg-is402-db
  ```
- **Khởi động lại (`Start`) trước buổi Demo:**
  ```bash
  az aks start --resource-group rg-is402-ecommerce --name aks-is402-ecommerce
  az postgres flexible-server start --resource-group rg-is402-ecommerce --name pg-is402-db
  ```
- **Xóa toàn bộ tài nguyên sau khi hoàn tất báo cáo cuối kỳ (sau ngày `24/11/2026`):**
  ```bash
  az group delete --name rg-is402-ecommerce --yes --no-wait
  ```
