variable "name" {
  description = "Tên của máy ảo."
  type        = string
}

variable "resource_group_name" {
  description = "Tên Resource Group."
  type        = string
}

variable "location" {
  description = "Vùng triển khai máy ảo."
  type        = string
}

variable "subnet_id" {
  description = "Mã Subnet ID nơi kết nối card mạng của VM."
  type        = string
}

variable "private_ip_allocation" {
  description = "Kiểu cấp phát IP: 'Static' hoặc 'Dynamic'."
  type        = string
  default     = "Dynamic"
}

variable "private_ip_address" {
  description = "Địa chỉ IP tĩnh (nếu private_ip_allocation là Static)."
  type        = string
  default     = null
}

variable "vm_size" {
  description = "Kích thước máy ảo (mặc định: Standard_B2as_v2 - 2 vCPU, 8 GiB RAM)."
  type        = string
  default     = "Standard_B2as_v2"
}

variable "admin_username" {
  description = "Tài khoản quản trị đăng nhập VM."
  type        = string
  default     = "azureuser"
}

variable "admin_password" {
  description = "Mật khẩu quản trị đăng nhập VM qua Serial Console."
  type        = string
  default     = "AzureLab@123456"
}

variable "tags" {
  description = "Tags metadata."
  type        = map(string)
  default     = {}
}

variable "enable_public_ip" {
  description = "Tạo và gán Public IP cho VM để kích hoạt Tailscale Direct P2P (Bypass NAT)"
  type        = bool
  default     = false
}

