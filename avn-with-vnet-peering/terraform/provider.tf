#################################################################################
# Repo: azure-ecommerce-devops-is402 | Dự án: avn-with-vnet-peering             #
# Môn học: IS402 - Điện toán đám mây (UIT)                                      #
# Thư mục: avn-with-vnet-peering/terraform/provider.tf                          #
# Mô tả: Cấu hình Azure Resource Manager Provider                              #
#################################################################################

provider "azurerm" {
  features {
    resource_group {
      # Tự động xóa tài nguyên con khi xóa Resource Group
      prevent_deletion_if_contains_resources = false
    }
  }
}

