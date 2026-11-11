#!/usr/bin/env python3
"""
IS402 - Big Data ETL & Compression Benchmark Script
Thực thi xử lý dữ liệu lớn (>1.2GB) và nén dữ liệu hoàn toàn bằng thư viện chuẩn Python (Standard Library).
Không phụ thuộc bất kỳ thư viện bên ngoài nào (No external packages required).
"""
import os
import sys
import time
import csv
import gzip

def main():
    input_csv = sys.argv[1] if len(sys.argv) > 1 else "dataset.csv"
    out_gz = sys.argv[2] if len(sys.argv) > 2 else "dataset_clean.csv.gz"

    if not os.path.exists(input_csv):
        print(f"Error: Khong tim thay file '{input_csv}'!")
        sys.exit(1)

    csv_size = os.path.getsize(input_csv)
    print(f"[1] Kich thuoc file CSV goc: {csv_size / (1024*1024):.2f} MB ({csv_size:,} bytes)")

    # 1. EXTRACT & TRANSFORM & LOAD (Pipeline Streaming to Compressed Gzip)
    t0 = time.perf_counter()
    valid_rows = 0
    total_rows = 0
    summary = {}  # key: (year, topic) -> [sum_val, min_val, max_val, count]

    with open(input_csv, "r", encoding="utf-8", errors="replace") as fin, \
         gzip.open(out_gz, "wt", encoding="utf-8", compresslevel=6) as fout:
        
        reader = csv.reader(fin)
        writer = csv.writer(fout)
        
        try:
            header = next(reader)
            writer.writerow(header)
            year_idx = header.index("Year") if "Year" in header else 0
            topic_idx = header.index("Topic") if "Topic" in header else 1
            val_idx = header.index("Data_Value") if "Data_Value" in header else -1
        except StopIteration:
            header = []
            val_idx = -1

        for row in reader:
            total_rows += 1
            if not row or len(row) <= max(year_idx, topic_idx, val_idx):
                continue
            
            val_str = row[val_idx].strip() if val_idx >= 0 else ""
            if not val_str or val_str in ("NA", "Value suppressed", "~", ""):
                continue
            
            try:
                val = float(val_str)
            except ValueError:
                continue

            valid_rows += 1
            writer.writerow(row)

            # Aggregation
            y = row[year_idx]
            t = row[topic_idx]
            k = (y, t)
            if k not in summary:
                summary[k] = [val, val, val, 1]
            else:
                s = summary[k]
                s[0] += val
                if val < s[1]: s[1] = val
                if val > s[2]: s[2] = val
                s[3] += 1

    t_process = time.perf_counter() - t0
    read_speed = (csv_size / (1024*1024)) / max(t_process, 0.001)
    gz_size = os.path.getsize(out_gz)

    print(f"[2] EXTRACT & TRANSFORM: Doc, lam sach {total_rows:,} dong mat: {t_process:.3f} s (Toc do: {read_speed:.2f} MB/s)")
    print(f"[3] LOAD (Gzip Compressed): Ghi nen thanh cong, giu lai {valid_rows:,} dong hop le")
    print(f"    Kich thuoc file sau nen: {gz_size / (1024*1024):.2f} MB ({gz_size:,} bytes)")

    # 2. BENCHMARK READ (Đo tốc độ đọc & giải nén file GZ)
    t0 = time.perf_counter()
    read_gz_lines = 0
    with gzip.open(out_gz, "rt", encoding="utf-8") as f_gz:
        for _ in f_gz:
            read_gz_lines += 1
    t_read_gz = time.perf_counter() - t0
    gz_read_speed = (csv_size / (1024*1024)) / max(t_read_gz, 0.001)
    print(f"[4] BENCHMARK GZ READ: Doc lai toan bo file nen {read_gz_lines:,} dong mat: {t_read_gz:.3f} s (Toc do tuong duong: {gz_read_speed:.2f} MB/s)")

    # 3. TỔNG HỢP CHỈ SỐ
    saved_pct = ((csv_size - gz_size) / csv_size) * 100
    ratio = csv_size / max(gz_size, 1)
    speedup = t_process / max(t_read_gz, 0.0001)
    print("\n" + "="*50)
    print(f"-> Ty le nen du lieu:           {ratio:.2f}x (Tiet kiem {saved_pct:.2f}% dung luong luu tru)")
    print(f"-> Tang toc do doc (Speedup):   {speedup:.2f} lan so voi xu ly file goc")
    print("="*50)

if __name__ == "__main__":
    main()

