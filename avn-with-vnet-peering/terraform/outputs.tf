#################################################################################
# Repo: azure-ecommerce-devops-is402 | Dự án: avn-with-vnet-peering             #
# Môn học: IS402 - Điện toán đám mây (UIT)                                      #
# Thư mục: avn-with-vnet-peering/terraform/outputs.tf                           #
# Mô tả: Xuất IP máy ảo, tài khoản Serial Console và hướng dẫn test bài Lab    #
#################################################################################

#--------------------------------------------------------------------------------
# 1. Thông tin Resource Group & Mạng
#--------------------------------------------------------------------------------

output "resource_group_name" {
  description = "Tên Resource Group."
  value       = azurerm_resource_group.rg.name
}

output "marketing_vnet_address_space" {
  description = "Dải CIDR của Marketing VNet."
  value       = module.marketing_vnet.address_space
}

output "development_vnet_address_space" {
  description = "Dải CIDR của Development VNet."
  value       = module.dev_vnet.address_space
}

#--------------------------------------------------------------------------------
# 2. Thông tin Máy ảo (VMs) & Kiểm thử Serial Console
#--------------------------------------------------------------------------------

output "vm_marketing_target_ip" {
  description = "Địa chỉ IP tĩnh của máy ảo Marketing (10.0.0.100 theo sơ đồ bài Lab)."
  value       = module.marketing_vm.private_ip_address
}

output "vm_dev_tester_ip" {
  description = "Địa chỉ IP của máy ảo Development (máy dùng để mở Serial Console)."
  value       = module.dev_vm.private_ip_address
}

output "serial_console_login" {
  description = "Thông tin đăng nhập máy ảo qua Azure Serial Console."
  value = {
    username = var.admin_username
    password = var.admin_password
  }
}

#--------------------------------------------------------------------------------
# 3. Hướng dẫn các bước kiểm thử trong bài Lab
#--------------------------------------------------------------------------------

output "lab_testing_guide" {
  description = "Hướng dẫn 2 cách mở Serial Console kiểm thử kết nối bài Lab."
  value = {
    method_1_terminal_cli = {
      command      = "az serial-console connect -n vm-development -g ${azurerm_resource_group.rg.name}"
      instructions = "Nhấn Enter -> Login: '${var.admin_username}' / Password: '${var.admin_password}' -> Test: 'nc -zv ${module.marketing_vm.private_ip_address} 22' -> Thoát: nhấn 'Ctrl + ]' rồi gõ 'q'"
    }
    method_2_web_portal = {
      step_1 = "Vào Azure Portal > Virtual Machines > chọn 'vm-development'"
      step_2 = "Trong menu bên trái (mục Help), chọn 'Serial console'"
      step_3 = "Đăng nhập với username: '${var.admin_username}' và password: '${var.admin_password}'"
      step_4 = "Gõ lệnh kiểm thử kết nối tới VM Marketing: nc -zv ${module.marketing_vm.private_ip_address} 22 (hoặc ssh ${var.admin_username}@${module.marketing_vm.private_ip_address})"
    }
  }
}

#--------------------------------------------------------------------------------
# 4. Trạng thái Peering & Lệnh Azure CLI
#--------------------------------------------------------------------------------

output "peering_enabled" {
  description = "Trạng thái Peering đang bật hay tắt trong cấu hình."
  value       = var.enable_peering
}

output "verify_peering_commands" {
  description = "Các lệnh Azure CLI để kiểm tra trạng thái Peering thực tế."
  value = {
    check_marketing_peering = "az network vnet peering list --resource-group ${azurerm_resource_group.rg.name} --vnet-name ${module.marketing_vnet.vnet_name} --output table"
    check_dev_peering       = "az network vnet peering list --resource-group ${azurerm_resource_group.rg.name} --vnet-name ${module.dev_vnet.vnet_name} --output table"
  }
}

