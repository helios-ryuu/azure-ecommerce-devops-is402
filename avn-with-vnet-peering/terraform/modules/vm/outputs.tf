output "vm_id" {
  description = "Mã Resource ID của VM."
  value       = azurerm_linux_virtual_machine.this.id
}

output "vm_name" {
  description = "Tên của VM."
  value       = azurerm_linux_virtual_machine.this.name
}

output "private_ip_address" {
  description = "Địa chỉ Private IP của VM."
  value       = azurerm_network_interface.this.private_ip_address
}

output "nic_id" {
  description = "Mã Resource ID của Card mạng."
  value       = azurerm_network_interface.this.id
}

