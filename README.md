# azure-ecommerce-devops-is402

> **Môn học:** IS402 - Điện toán đám mây (Trường ĐH Công Nghệ Thông Tin - UIT)  
> **Repository:** `azure-ecommerce-devops-is402` (Kho lưu trữ đồ án & bài thực hành môn học)

---

## 1. Tổng Quan Repository

Repository `azure-ecommerce-devops-is402` là kho lưu trữ chung cho toàn bộ các bài thực hành chuyên sâu (Labs) và bài tập lớn (Capstone Project) thuộc môn học **IS402 - Điện toán đám mây**. Repository bao gồm các dự án thành phần:

1. **Dự án `avn-with-vnet-peering`:** Kết nối mạng ảo nâng cao qua Azure Global VNet Peering và thực nghiệm phân tán xử lý dữ liệu lớn (>1.2GB) với pipeline ETL chuẩn nén Apache Parquet.
2. **Dự án `azure-ecommerce-application` *(Sắp tới)*:** Bài tập lớn môn học triển khai hệ thống thương mại điện tử kiến trúc Cloud-Native trên Microsoft Azure.

---

## 2. Danh Mục Các Dự Án Thành Phần

| STT | Dự án | Dịch vụ & Trọng tâm Kỹ thuật | Hướng dẫn & Tài liệu |
| :---: | :--- | :--- | :--- |
| 1 | **[`avn-with-vnet-peering/`](avn-with-vnet-peering/)** | **Dự án: Azure Virtual Network Peering & Thực Nghiệm Big Data ETL**<br>• Mạng Marketing (`10.0.0.0/16`, East Asia) & Mạng Development (`192.168.0.0/20`, Korea Central)<br>• Global VNet Peering qua Microsoft Private Backbone Network<br>• 2 Linux VMs (`Standard_B2ms` 8GB RAM), Serial Console, NSG Zero Trust<br>• Nạp dữ liệu qua Tailscale mesh VPN, Pipeline ETL tự động sang Apache Parquet (Snappy) | [Xem `avn-with-vnet-peering/README.md`](avn-with-vnet-peering/README.md) |
| 2 | **`azure-ecommerce-application/`** *(Sắp tới)* | **Dự án: Cloud-Native E-commerce Application on Azure**<br>• Bài tập lớn của môn học IS402<br>• Kiến trúc Microservices ... | *(Sẽ cập nhật khi triển khai)* |