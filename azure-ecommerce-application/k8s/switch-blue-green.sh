#!/usr/bin/env bash
set -euo pipefail

TARGET="${1:-}"
if [[ "$TARGET" != "blue" && "$TARGET" != "green" ]]; then
  echo "Cách dùng: $0 <blue|green>"
  exit 1
fi

echo ">>> Chuyển đổi selector của Service 'order-svc' sang phiên bản: $TARGET (Zero-Downtime)..."
kubectl patch service order-svc -p "{\"spec\":{\"selector\":{\"app\":\"order-service\",\"version\":\"$TARGET\"}}}"

echo ">>> Kiểm tra selector hiện tại của order-svc:"
kubectl get service order-svc -o wide
