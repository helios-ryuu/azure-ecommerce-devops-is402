#!/usr/bin/env python3
"""
Thực nghiệm 3 (Mục 3.3 Barem - 0.5đ):
Đo độ trễ mạng & HTTP/Storage RTT (ms) giữa Cùng Region (Japan East) vs Khác Region (East US).
"""
import argparse
import json
import statistics
import time
import urllib.request


def measure_url_latency(url: str, samples: int = 50) -> dict:
    latencies = []
    # Warm-up 2 requests
    for _ in range(2):
        try:
            with urllib.request.urlopen(url, timeout=10) as _:
                pass
        except Exception:
            pass

    for _ in range(samples):
        start = time.perf_counter()
        try:
            with urllib.request.urlopen(url, timeout=10) as resp:
                resp.read(64)
        except Exception:
            pass
        elapsed_ms = (time.perf_counter() - start) * 1000
        latencies.append(elapsed_ms)

    ordered = sorted(latencies)
    p50 = ordered[min(len(ordered) - 1, round((len(ordered) - 1) * 0.50))]
    p95 = ordered[min(len(ordered) - 1, round((len(ordered) - 1) * 0.95))]
    return {
        "url": url,
        "samples": samples,
        "min_ms": round(min(ordered), 2),
        "avg_ms": round(statistics.mean(ordered), 2),
        "p50_ms": round(p50, 2),
        "p95_ms": round(p95, 2),
        "max_ms": round(max(ordered), 2),
    }


def main() -> None:
    parser = argparse.ArgumentParser(description="Đo độ trễ Same-Region (Japan East) vs Cross-Region (East US)")
    parser.add_argument(
        "--same-region-url",
        default="https://stis402lakehouse.blob.core.windows.net/?comp=list",
        help="Endpoint tại Japan East (Same-Region)",
    )
    parser.add_argument(
        "--cross-region-url",
        default="https://stis402eastus.blob.core.windows.net/?comp=list",
        help="Endpoint tại East US (Cross-Region)",
    )
    parser.add_argument("-n", "--samples", type=int, default=50)
    args = parser.parse_args()

    same = measure_url_latency(args.same_region_url, args.samples)
    same["region_type"] = "Same-Region (Japan East)"
    print(json.dumps(same, ensure_ascii=False))

    cross = measure_url_latency(args.cross_region_url, args.samples)
    cross["region_type"] = "Cross-Region (East US)"
    print(json.dumps(cross, ensure_ascii=False))


if __name__ == "__main__":
    main()
