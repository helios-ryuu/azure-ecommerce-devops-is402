#################################################################################
# Repo: azure-ecommerce-devops-is402 | Dự án: avn-with-vnet-peering             #
# Môn học: IS402 - Điện toán đám mây (UIT)                                      #
# Thư mục: avn-with-vnet-peering/terraform/variables.tf                         #
# Mô tả: Khai báo biến cấu hình cho bài Lab Azure VNet Peering                  #
#################################################################################

#--------------------------------------------------------------------------------
# 1. Nhóm tài nguyên chung (Resource Group & Tags)
#--------------------------------------------------------------------------------

variable "resource_group_name" {
  description = "Tên Azure Resource Group."
  type        = string
  default     = "rg-asm"
}

variable "tags" {
  description = "Thẻ metadata phân loại tài nguyên."
  type        = map(string)
  default = {
    Project     = "avn-with-vnet-peering"
    Environment = "Lab"
    Course      = "IS402"
    ManagedBy   = "Terraform"
    LabName     = "VNet-Peering"
  }
}

#--------------------------------------------------------------------------------
# 2. Mạng Marketing (Bên trái sơ đồ: 10.0.0.0/16 - Subnet: 10.0.0.0/24)
#--------------------------------------------------------------------------------

variable "location_marketing" {
  description = "Vùng Azure triển khai Marketing VNet (mặc định: East Asia)."
  type        = string
  default     = "eastasia"
}

variable "vnet_marketing_name" {
  description = "Tên Virtual Network của Marketing (đánh số theo chuẩn)."
  type        = string
  default     = "vnet-1"
}

variable "vnet_marketing_address_space" {
  description = "Dải CIDR cho Marketing VNet theo đúng sơ đồ Lab (10.0.0.0/16)."
  type        = list(string)
  default     = ["10.0.0.0/16"]
}

variable "subnet_marketing_name" {
  description = "Tên Subnet bên trong Marketing VNet (đánh số theo chuẩn)."
  type        = string
  default     = "subnet-1"
}

variable "subnet_marketing_cidr" {
  description = "Dải CIDR cho Subnet Marketing (10.0.0.0/24)."
  type        = list(string)
  default     = ["10.0.0.0/24"]
}

variable "vm_marketing_private_ip" {
  description = "Địa chỉ IP tĩnh của Marketing VM theo đúng sơ đồ Lab (10.0.0.100)."
  type        = string
  default     = "10.0.0.100"
}

#--------------------------------------------------------------------------------
# 3. Mạng Development (Bên phải sơ đồ: 192.168.0.0/20 - Subnet: 192.168.0.0/24)
#--------------------------------------------------------------------------------

variable "location_dev" {
  description = "Vùng Azure triển khai Development VNet (mặc định: Korea Central - Global Peering)."
  type        = string
  default     = "koreacentral"
}

variable "vnet_dev_name" {
  description = "Tên Virtual Network của phòng Phát triển (Development - đánh số theo chuẩn)."
  type        = string
  default     = "vnet-2"
}

variable "vnet_dev_address_space" {
  description = "Dải CIDR cho Development VNet theo đúng sơ đồ Lab (192.168.0.0/20)."
  type        = list(string)
  default     = ["192.168.0.0/20"]
}

variable "subnet_dev_name" {
  description = "Tên Subnet bên trong Development VNet (đánh số theo chuẩn: subnet-2)."
  type        = string
  default     = "subnet-2"
}

variable "subnet_dev_cidr" {
  description = "Dải CIDR cho Subnet Development (192.168.0.0/24)."
  type        = list(string)
  default     = ["192.168.0.0/24"]
}

#--------------------------------------------------------------------------------
# 4. Cấu hình Máy ảo (VMs) & Đăng nhập Serial Console
#--------------------------------------------------------------------------------

variable "vm_size" {
  description = "Kích thước máy ảo Linux (Standard_B2ms: 2 vCPU, 8 GB RAM đáp ứng tiêu chí bộ nhớ >4GB)."
  type        = string
  default     = "Standard_B2ms"
}

variable "admin_username" {
  description = "Tài khoản quản trị đăng nhập VM qua Serial Console."
  type        = string
  default     = "azureuser"
}

variable "admin_password" {
  description = "Mật khẩu đăng nhập VM qua Serial Console trên Azure Portal."
  type        = string
  default     = "AzureLab@123456"
}

#--------------------------------------------------------------------------------
# 5. Cấu hình VNet Peering
#--------------------------------------------------------------------------------

variable "enable_peering" {
  description = "Bật/Tắt kết nối Peering: false (Step 4: trước peering) và true (Step 5 & 6: sau peering)."
  type        = bool
  default     = true
}

variable "allow_virtual_network_access" {
  description = "Cho phép các VM giữa 2 mạng giao tiếp nội bộ qua Peering."
  type        = bool
  default     = true
}

variable "allow_forwarded_traffic" {
  description = "Cho phép lưu lượng chuyển tiếp qua Peering."
  type        = bool
  default     = true
}

#--------------------------------------------------------------------------------
# 6. Cấu hình Azure Storage Account (Cloud Data Lake - Dự phòng)
#--------------------------------------------------------------------------------

variable "enable_storage" {
  description = "Khởi tạo Azure Storage Account làm Cloud Data Lake."
  type        = bool
  default     = false
}

variable "storage_account_name" {
  description = "Tên Azure Storage Account (phải là chữ thường, số, độ dài 3-24 ký tự)."
  type        = string
  default     = "stgasmlabis402"
}


