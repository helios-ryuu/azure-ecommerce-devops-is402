<p align="center">
  <a href="https://www.uit.edu.vn/" title="Trường Đại học Công nghệ Thông tin">
    <img src="https://i.imgur.com/WmMnSRt.png" alt="Trường Đại học Công nghệ Thông tin | University of Information Technology">
  </a>
</p>
<h1 align="center"><b>IS402.R11 - ĐIỆN TOÁN ĐÁM MÂY</b></h1>
<p align="center"><b>Kho lưu trữ đồ án & bài thực hành môn học: azure-ecommerce-devops-is402</b></p>

---

## MỤC LỤC
- [1. Giới thiệu & Thành viên nhóm](#1-giới-thiệu--thành-viên-nhóm)
- [2. Cấu trúc](#2-cấu-trúc)
- [3. Tài liệu tham khảo](#3-tài-liệu-tham-khảo)

---

## 1. Giới thiệu & Thành viên nhóm

### 1.1 Thông tin chung
- **Môn học:** IS402 - Điện toán đám mây
- **Lớp:** IS402.R11 — HK2 2025-2026
- **Đơn vị:** Khoa Hệ thống Thông tin — Trường Đại học Công nghệ Thông tin, ĐHQG-HCM (UIT)
- **Repository:** `azure-ecommerce-devops-is402`

Repository này là không gian lưu trữ và quản lý chung cho toàn bộ các bài thực hành và bài tập lớn thuộc học phần IS402:
1. **[`avn-with-vnet-peering/`](avn-with-vnet-peering/)**: Thực nghiệm kết nối mạng nâng cao Azure Global VNet Peering đa vùng và pipeline ETL dữ liệu lớn (>1.2GB) nén Apache Parquet.
2. **[`azure-ecommerce-application/`](azure-ecommerce-application/)**: **Bài tập 07: Triển khai ứng dụng trên cloud (Cloud-native applications)** — Nhóm ứng dụng: Thương mại điện tử (Điều kiện: Java, PHP, script).

### 1.2 Thành viên nhóm

| STT | MSSV | Họ và Tên | Vai trò | Github | Email |
|:---:|:---:|:---|:---|:---|:---|
| 1 | 23521367 | Ngô Tiến Sỹ | Trưởng nhóm | [@helios-ryuu](https://github.com/helios-ryuu) | 23521367@gm.uit.edu.vn |
| 2 | 23520214 | Lê Quốc Đại | Thành viên | [@daile2701](https://github.com/daile2701) | 23520214@gm.uit.edu.vn |
| 3 | 23521192 | Đào Bảo Phúc | Thành viên | [@baorphuc](https://github.com/baorphuc) | 23521192@gm.uit.edu.vn |

---

## 2. Cấu trúc

### 2.1 Cấu trúc repository

```
azure-ecommerce-devops-is402/
├── README.md                           # Tài liệu tổng quan repository (Cổng điều hướng)
├── avn-with-vnet-peering/              # Dự án 1: Azure VNet Peering & Big Data ETL
│   ├── README.md                       # Tài liệu chi tiết dự án VNet Peering
│   ├── demo/                           # Hướng dẫn bài Lab 5 phần & Dataset Server
│   └── terraform/                      # Mã nguồn IaC tự động hóa hạ tầng
└── azure-ecommerce-application/        # Dự án 2: Bài tập lớn Cloud-Native E-Commerce
    ├── README.md                       # Tài liệu chi tiết BTL E-Commerce
    └── ...
```

### 2.2 Danh mục các dự án thành phần

Toàn bộ nội dung kỹ thuật chi tiết, sơ đồ kiến trúc, mã nguồn IaC, quy trình CI/CD, hướng dẫn khởi chạy và giám sát được tổ chức độc lập trong từng dự án con:

| STT | Dự án | Trọng tâm kỹ thuật | Tài liệu chi tiết |
|:---:|:---|:---|:---|
| 1 | **[`avn-with-vnet-peering/`](avn-with-vnet-peering/)** | **Dự án: Azure Virtual Network Peering & Thực Nghiệm Big Data ETL**<br>• Kết nối mạng đa vùng (East Asia & Korea Central) qua Microsoft Global Private Backbone.<br>• Mô hình Zero Trust với NSG, quản trị an toàn qua Serial Console.<br>• Nạp dữ liệu qua Tailscale mesh VPN, pipeline ETL tự động chuẩn nén Apache Parquet (>1.2GB). | 👉 [Xem `avn-with-vnet-peering/README.md`](avn-with-vnet-peering/README.md) |
| 2 | **[`azure-ecommerce-application/`](azure-ecommerce-application/)** | **Bài tập 07: Triển khai ứng dụng trên cloud (Cloud-native applications)**<br>• Nhóm ứng dụng: Thương mại điện tử.<br>• Điều kiện: Java, PHP, script.<br>• Yêu cầu: Container trên AKS, CI/CD, Function as a service, Azure Database for PostgreSQL, Azure Cosmos DB, Azure Cache for Redis, Azure Synapse Analytics, Power BI. | 👉 [Xem `azure-ecommerce-application/README.md`](azure-ecommerce-application/README.md) |

---

## 3. Tài liệu tham khảo

1. [Microsoft Learn: Cloud-native applications architecture on Azure](https://learn.microsoft.com/en-us/azure/architecture/solution-ideas/articles/cloud-native-apps) (Tài liệu tham khảo Bài tập 07)
2. [Tài liệu Microsoft Azure Virtual Network](https://learn.microsoft.com/en-us/azure/virtual-network/)
3. [Tài liệu HashiCorp Terraform Azure Provider](https://registry.terraform.io/providers/hashicorp/azurerm/latest/docs)
4. Giáo trình & bài giảng học phần IS402 - Điện toán đám mây (UIT).