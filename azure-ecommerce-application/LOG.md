# Nhật ký triển khai (Deployment Log — IS402.R11)

1. **Ngày 09/11/2026:** Hoàn tất thiết kế kiến trúc tổng thể, vẽ sơ đồ kiến trúc (`architecture.png`) và ASCII Master Diagram (16:9), định nghĩa cấu trúc lưu trữ (PostgreSQL, Redis, Cosmos DB, Storage ADLS Gen2) và luồng giao tiếp 10 bước.  
   - Chi tiết xem tại: [`architecture.md`](./docs/2-planning-phase/architecture.md)
   - Subscription: Azure for Students
   - Resource group name: rg-is402-ecommerce
   - Region: Japan East
3. **Ngày 10/11/2026:** Thêm 2 thành viên làm Contributor trong resource group `(2/3 Task 2.1)`
   - Scope: /subscriptions/ba732803-dbba-4a7c-a523-8dcfed07e3e5/resourceGroups/rg-is402-ecommerce
   - Members:
     - Name: Lê Quốc Đại
       Object ID: 44359b62-65cd-41f0-ad43-86038fa4f012
       Type: User
     - Name: Đào Bảo Phúc
       Object ID: 024ccfa2-2177-4033-a519-ce0b3b0c34a9
       Type: User
       Description: No description
4. **Ngày 10/11/2026:** Tạo Budget `(3/3 Task 2.1 — Hoàn thành)`
   - Budget name: budget-is402-ecommerce
   - Time period: Monthly
   - Alert conditions:
       - Actual cost: 50% of budget (12.5) → Action group: None
       - Actual cost: 80% of budget (20) → Action group: None
       - Forecasted cost: 90% of budget (22.5) → Action group: None
      - Alert recipients (email): 23521367@gm.uit.edu.vn
      - Action groups: None
      - Language preference: English (United States)
5. **Ngày 10/11/2026:** Tạo Virtual Network `(1/3 Task 2.2)`
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
6. **Ngày 10/11/2026:** Tạo Azure Cosmos DB for NoSQL `(1/4 Task 2.4)`
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
7. **Ngày 10/11/2026:** Tạo Azure Storage Account ADLS Gen2 `(2/4 Task 2.4)`
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
8. **Ngày 11/11/2026:** Tạo 2 Containers trong `stis402lakehouse` trên Web `(3/4 Task 2.4)`
   - Containers:
       - `orders-etl` (Anonymous access level: Private)
       - `benchmark-dataset` (Anonymous access level: Private)
9. **Ngày 11/11/2026:** Tạo Database & Container trong `cosmos-is402-nosql` trên Web `(4/4 Task 2.4 — Hoàn thành)`
   - Database id: `ecommerce`
   - Container id: `orders`
   - Partition key: `/userId`
   - Container throughput: 400 RU/s (Manual)
10. **Ngày 11/11/2026:** Tạo Azure Synapse Analytics Workspace trên Web `(1/4 Task 4.2)`
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
11. **Ngày 11/11/2026:** Tạo Azure Function App trên Web `(1/3 Task 4.1)`
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
12. **[Sắp tới]** Tạo Azure Container Registry `acris402ecommerce` (SKU `Basic`, `Japan East`, `~$5/tháng`) `(2/3 Task 2.2)`
13. **[Sắp tới]** Tạo Azure DB for PostgreSQL Flexible Server `pg-is402-db` (`Burstable B1ms`, `32 GiB`, `Stop` khi chưa dùng) & nạp schema `users`/`products` + 15 sản phẩm mẫu `(1/3 & 2/3 Task 2.3)`
14. **[Sắp tới — ~20/11/2026]** Tạo Azure Cache for Redis `redis-is402-cache` (`Basic C0`, `~$16/tháng`, trước đó test local Docker `$0`) `(3/3 Task 2.3)`
15. **[Sắp tới — ~01/12/2026]** Tạo cụm AKS `aks-is402-ecommerce` (`Standard_B2als_v2`, Zone 3, OS Disk `32 GiB`, Managed NGINX Ingress) `(3/3 Task 2.2)`
16. **[Sắp tới — Tuần này, $0]** Dựng khung Source Code Phase 3 (`frontend-php`, `user-service`, `order-service`, `azure-functions`, `k8s/`) trên GitHub `(Gói 3)`