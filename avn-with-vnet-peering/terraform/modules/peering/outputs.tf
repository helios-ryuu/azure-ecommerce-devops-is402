output "peering_1_to_2_id" {
  description = "Mã Resource ID kết nối Peering từ VNet 1 sang VNet 2."
  value       = azurerm_virtual_network_peering.peering_1_to_2.id
}

output "peering_1_to_2_name" {
  description = "Tên kết nối Peering từ VNet 1 sang VNet 2."
  value       = azurerm_virtual_network_peering.peering_1_to_2.name
}

output "peering_2_to_1_id" {
  description = "Mã Resource ID kết nối Peering từ VNet 2 sang VNet 1."
  value       = azurerm_virtual_network_peering.peering_2_to_1.id
}

output "peering_2_to_1_name" {
  description = "Tên kết nối Peering từ VNet 2 sang VNet 1."
  value       = azurerm_virtual_network_peering.peering_2_to_1.name
}

