variable "name" {
  description = "Tên của Virtual Network."
  type        = string
}

variable "resource_group_name" {
  description = "Tên Resource Group."
  type        = string
}

variable "location" {
  description = "Vùng triển khai VNet."
  type        = string
}

variable "address_space" {
  description = "Dải CIDR cho VNet."
  type        = list(string)
}

variable "subnet_name" {
  description = "Tên Subnet bên trong VNet."
  type        = string
}

variable "subnet_cidr" {
  description = "Dải CIDR cho Subnet."
  type        = list(string)
}

variable "tags" {
  description = "Tags metadata."
  type        = map(string)
  default     = {}
}

variable "service_endpoints" {
  description = "Danh sách service endpoints kích hoạt trên Subnet."
  type        = list(string)
  default     = ["Microsoft.Storage"]
}

