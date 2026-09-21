#################################################################################
# Module: Network Security Group (NSG)                                          #
# Tệp: modules/nsg/main.tf                                                     #
#################################################################################

resource "azurerm_network_security_group" "this" {
  name                = var.name
  location            = var.location
  resource_group_name = var.resource_group_name
  tags                = var.tags

  security_rule {
    name                       = "Allow-SSH-From-Source"
    priority                   = 100
    direction                  = "Inbound"
    access                     = "Allow"
    protocol                   = "Tcp"
    source_port_range          = "*"
    destination_port_range     = "22"
    source_address_prefix      = var.allowed_source_cidr
    destination_address_prefix = "*"
    description                = "Cho phép SSH (cổng 22) từ dải mạng được chỉ định"
  }

  security_rule {
    name                       = "Allow-Tailscale-UDP"
    priority                   = 110
    direction                  = "Inbound"
    access                     = "Allow"
    protocol                   = "Udp"
    source_port_range          = "*"
    destination_port_range     = "41641"
    source_address_prefix      = "*"
    destination_address_prefix = "*"
    description                = "Cho phép kết nối WireGuard Tailscale Direct P2P (chống nghẽn DERP)"
  }
}

resource "azurerm_subnet_network_security_group_association" "this" {
  subnet_id                 = var.subnet_id
  network_security_group_id = azurerm_network_security_group.this.id
}

