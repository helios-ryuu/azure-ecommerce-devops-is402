#################################################################################
# Module: Bidirectional VNet Peering                                            #
# Tệp: modules/peering/main.tf                                                 #
#################################################################################

# Hướng 1: vnet_1 -> vnet_2
resource "azurerm_virtual_network_peering" "peering_1_to_2" {
  name                         = "peer-${var.vnet_1_name}-to-${var.vnet_2_name}"
  resource_group_name          = var.resource_group_name
  virtual_network_name         = var.vnet_1_name
  remote_virtual_network_id    = var.vnet_2_id
  allow_virtual_network_access = var.allow_virtual_network_access
  allow_forwarded_traffic      = var.allow_forwarded_traffic
  allow_gateway_transit        = false
  use_remote_gateways          = false
}

# Hướng 2: vnet_2 -> vnet_1
resource "azurerm_virtual_network_peering" "peering_2_to_1" {
  name                         = "peer-${var.vnet_2_name}-to-${var.vnet_1_name}"
  resource_group_name          = var.resource_group_name
  virtual_network_name         = var.vnet_2_name
  remote_virtual_network_id    = var.vnet_1_id
  allow_virtual_network_access = var.allow_virtual_network_access
  allow_forwarded_traffic      = var.allow_forwarded_traffic
  allow_gateway_transit        = false
  use_remote_gateways          = false
}

