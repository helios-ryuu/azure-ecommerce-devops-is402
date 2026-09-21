variable "resource_group_name" {
  description = "Tên Resource Group."
  type        = string
}

variable "vnet_1_id" {
  description = "Mã Resource ID của VNet 1."
  type        = string
}

variable "vnet_1_name" {
  description = "Tên của VNet 1."
  type        = string
}

variable "vnet_2_id" {
  description = "Mã Resource ID của VNet 2."
  type        = string
}

variable "vnet_2_name" {
  description = "Tên của VNet 2."
  type        = string
}

variable "allow_virtual_network_access" {
  description = "Cho phép các VM giữa 2 mạng giao tiếp với nhau."
  type        = bool
  default     = true
}

variable "allow_forwarded_traffic" {
  description = "Cho phép lưu lượng chuyển tiếp."
  type        = bool
  default     = true
}

