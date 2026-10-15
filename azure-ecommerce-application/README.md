<p align="center">
  <a href="https://www.uit.edu.vn/" title="Trường Đại học Công nghệ Thông tin">
    <img src="https://i.imgur.com/WmMnSRt.png" alt="Trường Đại học Công nghệ Thông tin | University of Information Technology">
  </a>
</p>
<h1 align="center"><b>IS402.R11 - ĐIỆN TOÁN ĐÁM MÂY</b></h1>
<p align="center"><b>Bài tập 07: Triển khai ứng dụng trên cloud (Cloud-native applications)</b></p>
<p align="center"><i>Nhóm ứng dụng: Thương mại điện tử (E-Commerce)</i></p>

---

## MỤC LỤC
- [1. Giới thiệu & Thành viên nhóm](#1-giới-thiệu--thành-viên-nhóm)
- [2. Kiến trúc & Công nghệ](#2-kiến-trúc--công-nghệ)
- [3. Hạ tầng IaC & CI/CD Pipeline](#3-hạ-tầng-iac--cicd-pipeline)
- [4. Hướng dẫn khởi chạy](#4-hướng-dẫn-khởi-chạy)
- [5. Giám sát](#5-giám-sát)
- [6. Tài liệu tham khảo](#6-tài-liệu-tham-khảo)

---

## 1. Giới thiệu & Thành viên nhóm

### 1.1 Thông tin đề tài
- **Môn học:** IS402 - Điện toán đám mây (IS402.R11 — HK2 2025-2026)
- **Đơn vị:** Khoa Hệ thống Thông tin — Trường Đại học Công nghệ Thông tin, ĐHQG-HCM (UIT)
- **Tên bài tập:** **Bài tập 07: Triển khai ứng dụng trên cloud (Cloud-native applications)**
- **Ngôn ngữ triển khai:** PHP 8.5, Java 25 LTS, Python 3.14
- **Mô tả đề tài:**  
  Xây dựng hệ thống thương mại điện tử phân tán trên nền tảng Microsoft Azure theo chuẩn kiến trúc Cloud-Native. Hệ thống áp dụng mô hình vi dịch vụ (Microservices), điều phối container trên Kubernetes, xử lý không đồng bộ qua Serverless FaaS, đa dạng hóa lưu trữ dữ liệu (Polyglot Persistence) và tích hợp hồ dữ liệu Lakehouse phục vụ phân tích kinh doanh thông minh (Business Intelligence).

### 1.2 Thành viên nhóm

| STT | MSSV | Họ và Tên | Vai trò chính trong dự án | Github | Email |
|:---:|:---:|:---|:---|:---|:---|
| 1 | 23521367 | **Ngô Tiến Sỹ** | **Trưởng nhóm** — Quản trị Hạ tầng Cloud (AKS, VNet), Ingress NGINX, CI/CD Pipeline & FinOps | [@helios-ryuu](https://github.com/helios-ryuu) | 23521367@gm.uit.edu.vn |
| 2 | 23520214 | **Lê Quốc Đại** | Frontend Web (PHP/CDN), User Service (Java Spring Boot), PostgreSQL 18 & Redis Cache | [@daile2701](https://github.com/daile2701) | 23520214@gm.uit.edu.vn |
| 3 | 23521192 | **Đào Bảo Phúc** | Order Service (FastAPI), Azure Cosmos DB, Azure Functions (FaaS), Dataset Parquet & Power BI | [@baorphuc](https://github.com/baorphuc) | 23521192@gm.uit.edu.vn |

---

## 2. Kiến trúc & Công nghệ

### 2.1 Danh mục Công nghệ
- **Container Orchestration:** Azure Kubernetes Service (AKS v1.31)
- **Microservices:** PHP 8.5 (Frontend SSR), Java 25 LTS / Spring Boot 4.1.1 (User Service), Python 3.14 / FastAPI (Order Service)
- **Cơ sở dữ liệu & Caching (PaaS):** Azure Cache for Redis 7.2, Azure Database for PostgreSQL Flexible Server v18, Azure Cosmos DB (Core SQL API)
- **Serverless Compute (FaaS):** Azure Functions (Python Programming Model v2)
- **Data Lakehouse & BI:** Azure Data Lake Storage Gen2 (ADLS Gen2), Azure Synapse Analytics (Serverless SQL Pool), Power BI Service
- **CI/CD & DevSecOps:** GitHub Actions, Azure Container Registry (ACR), Trivy Security Scanner

### 2.2 Sơ đồ Kiến trúc Tổng thể
Sơ đồ kiến trúc toàn cảnh và luồng xử lý chi tiết được mô tả tại:
- [Tài liệu Thiết kế Kiến trúc Hệ thống (Markdown & Mermaid)](./docs/2-planning-phase/architecture.md)
- [Bản Thuyết minh Kiến trúc & Cẩm nang Bản vẽ (`arch.txt`)](./arch.txt)

---

## 3. Hạ tầng IaC & CI/CD Pipeline

*(Coming soon)*

---

## 4. Hướng dẫn khởi chạy

*(Coming soon)*

---

## 5. Giám sát

*(Coming soon)*

---

## 6. Tài liệu tham khảo

1. [Hiến chương Dự án (Project Charter)](./docs/1-initiation-phase/project-charter.md)
2. [Tài liệu Thiết kế Kiến trúc Hệ thống](./docs/2-planning-phase/architecture.md)
3. [Bản Thuyết minh Kiến trúc & Cẩm nang Bản vẽ (`arch.txt`)](./arch.txt)
4. [Microsoft Learn: Cloud-native applications architecture on Azure](https://learn.microsoft.com/en-us/azure/architecture/solution-ideas/articles/cloud-native-apps)