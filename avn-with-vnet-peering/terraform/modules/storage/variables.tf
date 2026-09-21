variable "resource_group_name" {
  description = "Tên Resource Group."
  type        = string
}

variable "location" {
  description = "Vùng triển khai Storage Account."
  type        = string
}

variable "storage_account_name" {
  description = "Tên duy nhất toàn cầu của Storage Account (chỉ chứa chữ thường và số, 3-24 ký tự)."
  type        = string
}

variable "account_tier" {
  description = "Bậc lưu trữ (Standard/Premium)."
  type        = string
  default     = "Standard"
}

variable "account_replication_type" {
  description = "Cơ chế nhân bản (LRS, GRS, ZRS)."
  type        = string
  default     = "LRS"
}

variable "allowed_subnet_ids" {
  description = "Danh sách ID các subnet được phép truy cập qua Service Endpoint."
  type        = list(string)
  default     = []
}

variable "tags" {
  description = "Thẻ metadata."
  type        = map(string)
  default     = {}
}

