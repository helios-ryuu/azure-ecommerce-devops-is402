output "vnet_id" {
  description = "Mã Resource ID của VNet."
  value       = azurerm_virtual_network.this.id
}

output "vnet_name" {
  description = "Tên của VNet."
  value       = azurerm_virtual_network.this.name
}

output "address_space" {
  description = "Dải CIDR của VNet."
  value       = azurerm_virtual_network.this.address_space
}

output "subnet_id" {
  description = "Mã Resource ID của Subnet."
  value       = azurerm_subnet.this.id
}

output "subnet_name" {
  description = "Tên của Subnet."
  value       = azurerm_subnet.this.name
}

