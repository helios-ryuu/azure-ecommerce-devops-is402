# Mã Nguồn Terraform: Azure Virtual Network Peering Lab (`avn-with-vnet-peering/terraform`)

> **Môn học:** IS402 - Điện toán đám mây (Trường ĐH Công Nghệ Thông Tin - UIT)  
> **Repository:** `azure-ecommerce-devops-is402`  
> **Dự án:** `avn-with-vnet-peering` (Phần mã nguồn IaC dự phòng: `terraform/`)  
> **Mục tiêu:** Tự động hóa toàn bộ hạ tầng mạng, máy ảo và kết nối Peering cho bài Lab bằng Terraform.

---

## 1. Cấu Trúc Thư Mục Terraform

```
avn-with-vnet-peering/terraform/
├── versions.tf              # Yêu cầu phiên bản Terraform (>= 1.5.0) & azurerm (~> 3.0)
├── provider.tf              # Cấu hình AzureRM Provider (skip registration & unprotect RG)
├── variables.tf             # Khai báo các tham số đầu vào
├── main.tf                  # File điều phối trung tâm gọi 4 submodules
├── outputs.tf               # Xuất IP máy ảo, tài khoản Serial Console và hướng dẫn test
├── terraform.tfvars.example # File mẫu cấu hình chuẩn
├── terraform.tfvars         # File biến thực tế (local, đã được gitignore)
├── load_credential.sh      # Script nạp biến môi trường xác thực Service Principal
└── modules/                 # Các submodules tái sử dụng
    ├── vnet/                # Module tạo Virtual Network & Subnet
    ├── nsg/                 # Module tạo Network Security Group (Allow SSH)
    ├── vm/                  # Module tạo Linux VM (Standard_B2ms 8GB RAM & Serial Console)
    ├── peering/             # Module thiết lập kết nối VNet Peering 2 chiều
    └── storage/             # Module tạo Azure Storage Account & Containers (Dự phòng)
```

---

## 2. Hướng Dẫn Vận Hành Từng Bước

Thực hiện toàn bộ các lệnh dưới đây bên trong thư mục `avn-with-vnet-peering/terraform/`:

### Bước 1: Chuẩn Bị & Nạp Credentials
```bash
# Nạp biến môi trường xác thực từ contributor.json
source load_credential.sh

# Khởi tạo Terraform và tải các Provider/Module
terraform init

# Kiểm tra cú pháp mã nguồn
terraform validate
```

### Bước 2: Triển Khai Hạ Tầng (Apply)

* **Cách 1: Triển khai hoàn chỉnh toàn bộ hạ tầng (Bao gồm Peering)**
  ```bash
  terraform apply -auto-approve
  ```

* **Cách 2: Mô phỏng bài lab qua 2 giai đoạn**
  1. *Giai đoạn trước Peering (mô phỏng Step 4 của Lab)*:
     ```bash
     terraform apply -var="enable_peering=false" -auto-approve
     ```
     Kiểm thử kết nối qua Serial Console → **Timeout** (thất bại).
  2. *Giai đoạn sau Peering (mô phỏng Step 5 & 6 của Lab)*:
     ```bash
     terraform apply -var="enable_peering=true" -auto-approve
     ```
     Kiểm thử kết nối lại → **Thành công 100%**.

---

## 3. Kiểm Thử Kết Nối (Serial Console)

Sau khi `terraform apply` thành công:

### Cách 1: Mở Serial Console ngay trong Terminal (Khuyên dùng)
```bash
az serial-console connect -n vm-2 -g rg-asm
```
* Bấm **Enter** → Đăng nhập: `azureuser` / `AzureLab@123456`
* Gõ lệnh kiểm tra kết nối:
  ```bash
  nc -zv 10.0.0.100 22
  # hoặc SSH:
  ssh azureuser@10.0.0.100
  ```
* Thoát Serial Console: Bấm tổ hợp phím **`Ctrl + ]`** rồi ấn **`q`**.

### Cách 2: Mở Serial Console trên Azure Portal
Vào **Virtual Machines** → chọn **`vm-2`** → mục **Help** → chọn **Serial console** → Đăng nhập và gõ lệnh test như trên.

---

## 4. Hủy Toàn Bộ Tài Nguyên

Khi thực hành xong bài lab, phương pháp dọn dẹp **nhanh nhất và sạch sẽ nhất** là sử dụng một câu lệnh duy nhất qua Azure CLI:
```bash
az group delete --name rg-asm --yes --no-wait
```

*(Hoặc nếu muốn hủy thông qua Terraform: `terraform destroy -auto-approve`)*.

