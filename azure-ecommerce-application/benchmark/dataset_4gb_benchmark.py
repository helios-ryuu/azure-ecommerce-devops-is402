#!/usr/bin/env python3
"""
Thực nghiệm 1 (Mục 1.3 Barem - 0.75đ):
Sinh tập dữ liệu E-Commerce > 4GB (CSV/Parquet) và đo thời gian tổng hợp doanh thu trên máy Local
để so sánh với Azure Synapse Serverless SQL Pool trên Cloud.
"""
import argparse
import csv
import json
import os
import time
from collections import defaultdict

CATEGORIES = ["Cloud Apparel", "DevOps Gear", "Database Books", "Containers & K8s", "Serverless Accessories"]


def generate_dataset(path: str, target_gb: float = 4.1) -> None:
    target_bytes = int(target_gb * (1024**3))
    os.makedirs(os.path.dirname(path) or ".", exist_ok=True)
    # Tạo block 10,000 dòng mẫu rồi ghi lặp tốc độ cao để đạt > 4GB nhanh chóng
    chunk_lines = []
    for i in range(1, 10001):
        cat = CATEGORIES[i % len(CATEGORIES)]
        amount = round(49000 + (i % 15) * 35000, 2)
        qty = (i % 5) + 1
        chunk_lines.append(f"ord-{i},user-{(i % 500) + 1},{cat},{qty},{amount},2026-11-{(i % 28) + 1:02d}\n")
    chunk_bytes = "".join(chunk_lines).encode("utf-8")

    started = time.perf_counter()
    written = 0
    with open(path, "wb") as f:
        header = b"order_id,user_id,category,quantity,total_amount,order_date\n"
        f.write(header)
        written += len(header)
        while written < target_bytes:
            f.write(chunk_bytes)
            written += len(chunk_bytes)
    elapsed = time.perf_counter() - started
    print(
        json.dumps(
            {
                "action": "generate_dataset",
                "file": path,
                "size_gb": round(written / (1024**3), 2),
                "elapsed_sec": round(elapsed, 2),
            },
            ensure_ascii=False,
        )
    )


def benchmark_local_aggregation(path: str) -> None:
    started = time.perf_counter()
    counts: dict[str, int] = defaultdict(int)
    revenues: dict[str, float] = defaultdict(float)

    with open(path, "r", encoding="utf-8") as f:
        reader = csv.reader(f)
        next(reader, None)  # skip header
        for row in reader:
            if len(row) >= 5:
                cat = row[2]
                counts[cat] += 1
                revenues[cat] += float(row[4])

    elapsed = time.perf_counter() - started
    summary = {
        cat: {"orders": counts[cat], "total_revenue": round(revenues[cat], 2)}
        for cat in sorted(counts.keys())
    }
    print(
        json.dumps(
            {
                "mode": "local_single_node_python",
                "file_size_gb": round(os.path.getsize(path) / (1024**3), 2),
                "elapsed_sec": round(elapsed, 2),
                "categories": summary,
            },
            ensure_ascii=False,
        )
    )


def main() -> None:
    parser = argparse.ArgumentParser(description="Sinh và đo tốc độ xử lý Dataset > 4GB trên máy Local")
    parser.add_argument("--file", default="/tmp/ecommerce_benchmark_4gb.csv", help="Đường dẫn file CSV > 4GB")
    parser.add_argument("--size-gb", type=float, default=4.1, help="Dung lượng mục tiêu (GB)")
    parser.add_argument("--skip-generate", action="store_true", help="Chỉ chạy đo tốc độ đọc/tổng hợp nếu đã có file")
    args = parser.parse_args()

    if not args.skip_generate or not os.path.exists(args.file):
        generate_dataset(args.file, args.size_gb)
    benchmark_local_aggregation(args.file)


if __name__ == "__main__":
    main()
