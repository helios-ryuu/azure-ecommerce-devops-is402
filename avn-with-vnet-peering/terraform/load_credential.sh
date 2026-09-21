#!/usr/bin/env bash
# ==============================================================================
# Script nạp biến môi trường xác thực Azure cho Terraform (ARM_*)
# Cách sử dụng: source load_credential.sh
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Tìm kiếm contributor.json linh hoạt (thư mục hiện tại, thư mục script, các thư mục cha hoặc các thư mục con)
JSON_FILE=""
for CANDIDATE in \
    "$PWD/contributor.json" \
    "$SCRIPT_DIR/contributor.json" \
    "$SCRIPT_DIR/../contributor.json" \
    "$SCRIPT_DIR/../../contributor.json" \
    "$SCRIPT_DIR"/*/contributor.json; do
    if [ -f "$CANDIDATE" ]; then
        JSON_FILE="$CANDIDATE"
        break
    fi
done

INIT_LOG=""
for CANDIDATE in \
    "$PWD/init.log" \
    "$SCRIPT_DIR/init.log" \
    "$SCRIPT_DIR/../init.log" \
    "$SCRIPT_DIR/../../init.log" \
    "$SCRIPT_DIR"/*/init.log; do
    if [ -f "$CANDIDATE" ]; then
        INIT_LOG="$CANDIDATE"
        break
    fi
done

# Kiểm tra file Service Principal JSON
if [ ! -f "$JSON_FILE" ]; then
    echo "[-] Error: Không tìm thấy file contributor.json"
    return 1 2>/dev/null || exit 1
fi

# Kiểm tra lệnh jq
if ! command -v jq &> /dev/null; then
    echo "[-] Error: 'jq' chưa được cài đặt. Vui lòng cài đặt: sudo apt install jq"
    return 1 2>/dev/null || exit 1
fi

# 1. Trích xuất thông tin Service Principal từ JSON
export ARM_CLIENT_ID=$(jq -r '.appId // empty' "$JSON_FILE")
export ARM_CLIENT_SECRET=$(jq -r '.password // empty' "$JSON_FILE")
export ARM_TENANT_ID=$(jq -r '.tenant // empty' "$JSON_FILE")

if [ -z "$ARM_CLIENT_ID" ] || [ -z "$ARM_CLIENT_SECRET" ] || [ -z "$ARM_TENANT_ID" ]; then
    echo "[-] Error: File $JSON_FILE thiếu thông tin (appId, password hoặc tenant)."
    return 1 2>/dev/null || exit 1
fi

# 2. Xác định Subscription ID động (HOÀN TOÀN KHÔNG HARDCODE)
# Thứ tự ưu tiên:
#   a. Biến môi trường ARM_SUBSCRIPTION_ID đã có sẵn trong shell
#   b. Đọc từ file JSON nếu có trường subscriptionId
#   c. Truy vấn từ Azure CLI đang đăng nhập (az account show)
#   d. Trích xuất UUID subscription từ file init.log nếu tồn tại
SUB_ID="${ARM_SUBSCRIPTION_ID:-}"

if [ -z "$SUB_ID" ]; then
    SUB_ID=$(jq -r '.subscriptionId // .subscription_id // empty' "$JSON_FILE")
fi

if [ -z "$SUB_ID" ] && command -v az &> /dev/null; then
    SUB_ID=$(az account show --query id -o tsv 2>/dev/null)
fi

if [ -z "$SUB_ID" ] && [ -f "$INIT_LOG" ]; then
    # Trích xuất UUID subscription xuất hiện trong init.log
    SUB_ID=$(grep -oE '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}' "$INIT_LOG" | head -n 1)
fi

# Nếu vẫn không tìm thấy, yêu cầu người dùng chỉ định (không dùng giá trị mặc định hardcode)
if [ -z "$SUB_ID" ]; then
    echo "[-] Error: Không thể tự động xác định Azure Subscription ID."
    echo "    Vui lòng thực hiện một trong các cách sau:"
    echo "      1. Đăng nhập Azure CLI:  az login"
    echo "      2. Xuất biến trước:       export ARM_SUBSCRIPTION_ID=\"<your-subscription-id>\""
    echo "      3. Thêm trường 'subscriptionId' vào file $JSON_FILE"
    return 1 2>/dev/null || exit 1
fi

export ARM_SUBSCRIPTION_ID="$SUB_ID"

echo "[+] Đã nạp thành công biến môi trường Azure Service Principal cho Terraform:"
echo "    ARM_CLIENT_ID       = $ARM_CLIENT_ID"
echo "    ARM_TENANT_ID       = $ARM_TENANT_ID"
echo "    ARM_SUBSCRIPTION_ID = $ARM_SUBSCRIPTION_ID"
echo "    ARM_CLIENT_SECRET   = [PROTECTED]"
echo ""
echo "Bạn có thể tiến hành chạy 'terraform plan' hoặc 'terraform apply'."
