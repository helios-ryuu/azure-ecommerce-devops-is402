variable "name" {
  description = "Tên của Network Security Group."
  type        = string
}

variable "resource_group_name" {
  description = "Tên Resource Group."
  type        = string
}

variable "location" {
  description = "Vùng triển khai NSG."
  type        = string
}

variable "subnet_id" {
  description = "Mã Resource ID của Subnet cần gắn NSG."
  type        = string
}

variable "allowed_source_cidr" {
  description = "Dải CIDR nguồn được phép SSH vào cổng 22 (ví dụ: 192.168.0.0/16)."
  type        = string
}

variable "tags" {
  description = "Tags metadata."
  type        = map(string)
  default     = {}
}

