#################################################################################
# Module: Azure Storage Account & Containers (Cloud Data Lake)                 #
# Tệp: modules/storage/main.tf                                                 #
#################################################################################

resource "azurerm_storage_account" "this" {
  name                     = var.storage_account_name
  resource_group_name      = var.resource_group_name
  location                 = var.location
  account_tier             = var.account_tier
  account_replication_type = var.account_replication_type
  min_tls_version          = "TLS1_2"
  tags                     = var.tags

  # Kích hoạt Network Rules nếu có Subnet IDs được truyền vào
  dynamic "network_rules" {
    for_each = length(var.allowed_subnet_ids) > 0 ? [1] : []
    content {
      default_action             = "Allow"
      virtual_network_subnet_ids = var.allowed_subnet_ids
    }
  }
}

resource "azurerm_storage_container" "raw" {
  name                  = "raw-data"
  storage_account_name  = azurerm_storage_account.this.name
  container_access_type = "private"
}

resource "azurerm_storage_container" "processed" {
  name                  = "processed-data"
  storage_account_name  = azurerm_storage_account.this.name
  container_access_type = "private"
}

