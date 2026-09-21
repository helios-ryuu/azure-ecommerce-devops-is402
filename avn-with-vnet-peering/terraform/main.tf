#################################################################################
# Repo: azure-ecommerce-devops-is402 | Dự án: avn-with-vnet-peering             #
# Môn học: IS402 - Điện toán đám mây (UIT)                                      #
# Thư mục: avn-with-vnet-peering/terraform/main.tf                              #
# Mô tả: Module gốc điều phối kết nối các modules: VNet, NSG, VM, Peering, Storage #
#################################################################################

#--------------------------------------------------------------------------------
# 1. Khởi tạo Resource Group chung
#--------------------------------------------------------------------------------

resource "azurerm_resource_group" "rg" {
  name     = var.resource_group_name
  location = var.location_marketing
  tags     = var.tags
}

#--------------------------------------------------------------------------------
# 2. Mạng Marketing (Bên trái: 10.0.0.0/16 & Subnet 10.0.0.0/24)
#--------------------------------------------------------------------------------

module "marketing_vnet" {
  source              = "./modules/vnet"
  name                = var.vnet_marketing_name
  location            = var.location_marketing
  resource_group_name = azurerm_resource_group.rg.name
  address_space       = var.vnet_marketing_address_space
  subnet_name         = var.subnet_marketing_name
  subnet_cidr         = var.subnet_marketing_cidr
  tags                = merge(var.tags, { Department = "Marketing" })
}

#--------------------------------------------------------------------------------
# 3. Mạng Development (Bên phải: 192.168.0.0/20 & Subnet 192.168.0.0/24)
#--------------------------------------------------------------------------------

module "dev_vnet" {
  source              = "./modules/vnet"
  name                = var.vnet_dev_name
  location            = var.location_dev
  resource_group_name = azurerm_resource_group.rg.name
  address_space       = var.vnet_dev_address_space
  subnet_name         = var.subnet_dev_name
  subnet_cidr         = var.subnet_dev_cidr
  tags                = merge(var.tags, { Department = "Development" })
}

#--------------------------------------------------------------------------------
# 4. Network Security Group (Cho phép SSH từ 192.168.0.0/16 vào Marketing)
#--------------------------------------------------------------------------------

module "marketing_nsg" {
  source              = "./modules/nsg"
  name                = "nsg-marketing"
  location            = var.location_marketing
  resource_group_name = azurerm_resource_group.rg.name
  subnet_id           = module.marketing_vnet.subnet_id
  allowed_source_cidr = "192.168.0.0/16" # Khớp chính xác nhãn sơ đồ bài Lab
  tags                = var.tags
}

#--------------------------------------------------------------------------------
# 5. Máy ảo VM Marketing (Mục tiêu SSH: IP tĩnh 10.0.0.100)
#--------------------------------------------------------------------------------

module "marketing_vm" {
  source                = "./modules/vm"
  name                  = "vm-marketing"
  location              = var.location_marketing
  resource_group_name   = azurerm_resource_group.rg.name
  subnet_id             = module.marketing_vnet.subnet_id
  private_ip_allocation = "Static"
  private_ip_address    = var.vm_marketing_private_ip # 10.0.0.100
  vm_size               = var.vm_size
  admin_username        = var.admin_username
  admin_password        = var.admin_password
  tags                  = merge(var.tags, { Role = "Marketing-Target" })
}

#--------------------------------------------------------------------------------
# 6. Máy ảo VM Development (Nguồn kiểm thử: Bật Serial Console)
#--------------------------------------------------------------------------------

module "dev_vm" {
  source                = "./modules/vm"
  name                  = "vm-development"
  location              = var.location_dev
  resource_group_name   = azurerm_resource_group.rg.name
  subnet_id             = module.dev_vnet.subnet_id
  private_ip_allocation = "Dynamic"
  vm_size               = var.vm_size
  admin_username        = var.admin_username
  admin_password        = var.admin_password
  tags                  = merge(var.tags, { Role = "Dev-Tester" })
}

#--------------------------------------------------------------------------------
# 7. Thiết lập VNet Peering 2 chiều (Global Peering giữa 2 VNets)
#--------------------------------------------------------------------------------

module "peering" {
  count                        = var.enable_peering ? 1 : 0
  source                       = "./modules/peering"
  resource_group_name          = azurerm_resource_group.rg.name
  vnet_1_id                    = module.marketing_vnet.vnet_id
  vnet_1_name                  = module.marketing_vnet.vnet_name
  vnet_2_id                    = module.dev_vnet.vnet_id
  vnet_2_name                  = module.dev_vnet.vnet_name
  allow_virtual_network_access = var.allow_virtual_network_access
  allow_forwarded_traffic      = var.allow_forwarded_traffic
}

#--------------------------------------------------------------------------------
# 8. Azure Storage Account (Cloud Data Lake - Dự phòng)
#--------------------------------------------------------------------------------

module "storage" {
  count                = var.enable_storage ? 1 : 0
  source               = "./modules/storage"
  resource_group_name  = azurerm_resource_group.rg.name
  location             = var.location_marketing
  storage_account_name = var.storage_account_name
  allowed_subnet_ids   = [module.marketing_vnet.subnet_id, module.dev_vnet.subnet_id]
  tags                 = merge(var.tags, { Role = "Data-Lake" })
}


