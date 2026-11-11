output "storage_account_id" {
  description = "ID của Storage Account."
  value       = azurerm_storage_account.this.id
}

output "storage_account_name" {
  description = "Tên của Storage Account."
  value       = azurerm_storage_account.this.name
}

output "primary_blob_endpoint" {
  description = "Endpoint truy cập dịch vụ Blob."
  value       = azurerm_storage_account.this.primary_blob_endpoint
}

output "primary_access_key" {
  description = "Khóa truy cập chính của Storage Account."
  value       = azurerm_storage_account.this.primary_access_key
  sensitive   = true
}

