output "nsg_id" {
  description = "Mã Resource ID của NSG."
  value       = azurerm_network_security_group.this.id
}

output "nsg_name" {
  description = "Tên của NSG."
  value       = azurerm_network_security_group.this.name
}

